<?php

/**
 * @package         Regular Labs Library
 * @version         25.3.16992
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2025 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */
namespace RegularLabs\Library;

defined('_JEXEC') or die;
class RegEx
{
    /**
     * Perform a regular expression match
     */
    public static function match(string $pattern, string $string, &$match = null, ?string $options = null, int $flags = 0): int
    {
        if ($string == '' || $pattern == '') {
            return \false;
        }
        $pattern = self::preparePattern($pattern, $options, $string);
        $result = preg_match($pattern, $string, $match, $flags);
        // Remove all numeric keys except 0
        $no_numeric_values = array_filter($match, fn($key) => !is_int($key) || $key === 0, \ARRAY_FILTER_USE_KEY);
        // If the leftover array counts more than 2 (so contains named groups), replace $match
        if (count($no_numeric_values) > 1) {
            $match = $no_numeric_values;
        }
        return $result;
    }
    /**
     * Perform a global regular expression match
     */
    public static function matchAll(string $pattern, string $string, &$matches = null, ?string $options = null, int $flags = \PREG_SET_ORDER): int
    {
        if ($string == '' || $pattern == '') {
            $matches = [];
            return \false;
        }
        $pattern = self::preparePattern($pattern, $options, $string);
        $result = preg_match_all($pattern, $string, $matches, $flags);
        if (!$result) {
            return \false;
        }
        if ($flags == \PREG_OFFSET_CAPTURE) {
            // Remove all numeric keys except 0
            $no_numeric_values = array_filter($matches, fn($key) => !is_int($key) || $key === 0, \ARRAY_FILTER_USE_KEY);
            // If the leftover array counts less than 2 (so no named groups), don't continue
            if (count($no_numeric_values) < 2) {
                return $result;
            }
            $matches = $no_numeric_values;
            return $result;
        }
        if ($flags != \PREG_SET_ORDER) {
            return $result;
        }
        foreach ($matches as &$match) {
            // Remove all numeric keys except 0
            $no_numeric_values = array_filter($match, fn($key) => !is_int($key) || $key === 0, \ARRAY_FILTER_USE_KEY);
            // If the leftover array counts less than 2 (so no named groups), don't continue
            if (count($no_numeric_values) < 2) {
                break;
            }
            $match = $no_numeric_values;
        }
        return $result;
    }
    /**
     * preg_quote the given string or array of strings
     */
    public static function nameGroup(string $data, string $name = ''): string
    {
        return '(?<' . $name . '>' . $data . ')';
    }
    /**
     * Make a string a valid regular expression pattern
     */
    public static function preparePattern(string|array $pattern, ?string $options = null, string $string = ''): string|array
    {
        $array = \RegularLabs\Library\ArrayHelper::applyMethodToValues([$pattern, $options, $string]);
        if (!is_null($array)) {
            return $array;
        }
        if (!str_starts_with($pattern, '#')) {
            $options = !is_null($options) ? $options : 'si';
            $pattern = '#' . $pattern . '#' . $options;
        }
        if (\RegularLabs\Library\StringHelper::detectUTF8($string)) {
            // use utf-8
            return $pattern . 'u';
        }
        return $pattern;
    }
    /**
     * preg_quote the given string or array of strings
     */
    public static function quote(string|array $data, string $name = '', string $delimiter = '#'): string
    {
        if (is_array($data)) {
            if (count($data) === 1) {
                return self::quote(array_pop($data), $name, $delimiter);
            }
            $array = self::quoteArray($data, $delimiter);
            $prefix = '?:';
            if ($name != '') {
                $prefix = $name ? '?<' . $name . '>' : '';
            }
            return '(' . $prefix . implode('|', $array) . ')';
        }
        if ($name != '') {
            return '(?<' . $name . '>' . preg_quote($data, $delimiter) . ')';
        }
        return preg_quote($data, $delimiter);
    }
    /**
     * preg_quote the given array of strings
     */
    public static function quoteArray(array $array, string $delimiter = '#'): array
    {
        array_walk($array, function (&$part, $key, $delimiter) {
            $part = self::quote($part, '', $delimiter);
        }, $delimiter);
        return $array;
    }
    /**
     * Perform a regular expression search and replace
     */
    public static function replace(string $pattern, string $replacement, string $string, ?string $options = null, int $limit = -1, ?int &$count = null): string|null
    {
        if ($string == '' || $pattern == '') {
            return $string;
        }
        $pattern = self::preparePattern($pattern, $options, $string);
        return preg_replace($pattern, $replacement, $string, $limit, $count);
    }
    /**
     * Perform a regular expression search and replace once
     */
    public static function replaceOnce(string $pattern, string $replacement, string $string, ?string $options = null): string
    {
        return self::replace($pattern, $replacement, $string, $options, 1);
    }
    /**
     * Perform a regular expression split
     */
    public static function split(string $pattern, string $string, ?string $options = null, int $limit = -1, int $flags = \PREG_SPLIT_DELIM_CAPTURE): array
    {
        if ($string == '' || $pattern == '') {
            return [$string];
        }
        $pattern = self::preparePattern($pattern, $options, $string);
        return preg_split($pattern, $string, $limit, $flags);
    }
    /**
     * reverse preg_quote the given string
     */
    public static function unquote(string $string, string $delimiter = '#'): string
    {
        return strtr($string, ['\\' . $delimiter => $delimiter, '\.' => '.', '\\\\' => '\\', '\+' => '+', '\*' => '*', '\?' => '?', '\[' => '[', '\^' => '^', '\]' => ']', '\$' => '$', '\(' => '(', '\)' => ')', '\{' => '{', '\}' => '}', '\=' => '=', '\!' => '!', '\<' => '<', '\>' => '>', '\|' => '|', '\:' => ':', '\-' => '-']);
    }
}
