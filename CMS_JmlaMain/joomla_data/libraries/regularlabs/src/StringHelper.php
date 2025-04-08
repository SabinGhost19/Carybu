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
use Joomla\String\Normalise as JNormalise;
use Normalizer;
class StringHelper extends \Joomla\String\StringHelper
{
    /**
     * Adds postfix to a string
     */
    public static function addPostfix(string $string, string $postfix): string
    {
        $array = \RegularLabs\Library\ArrayHelper::applyMethodToValues([$string, $postfix]);
        if (!is_null($array)) {
            return $array;
        }
        if ($postfix == '') {
            return $string;
        }
        if (!is_string($string) && !is_numeric($string)) {
            return $string;
        }
        return $string . $postfix;
    }
    /**
     * Adds prefix to a string
     */
    public static function addPrefix(string $string, string $prefix, bool $keep_leading_slash = \true): string
    {
        $array = \RegularLabs\Library\ArrayHelper::applyMethodToValues([$string, $prefix, $keep_leading_slash]);
        if (!is_null($array)) {
            return $array;
        }
        if ($prefix == '') {
            return $string;
        }
        if (!is_string($string) && !is_numeric($string)) {
            return $string;
        }
        if ($keep_leading_slash && !empty($string) && $string[0] == '/') {
            return $string[0] . $prefix . substr($string, 1);
        }
        return $prefix . $string;
    }
    public static function applyConversion(string $type, string $string, ?object $attributes): string
    {
        switch ($type) {
            case 'escape':
                return addslashes($string);
            case 'lowercase':
                return self::toLowerCase($string);
            case 'uppercase':
                return self::toUpperCase($string);
            case 'notags':
                return strip_tags($string);
            case 'nowhitespace':
                return str_replace(' ', '', strip_tags($string));
            case 'toalias':
                return \RegularLabs\Library\Alias::get($string);
            case 'replace':
                if (!isset($attributes->from)) {
                    return $string;
                }
                $case_insensitive = isset($attributes->{'case-insensitive'}) && $attributes->{'case-insensitive'} == 'true';
                return \RegularLabs\Library\RegEx::replace($attributes->from, $attributes->to ?? '', $string, $case_insensitive ? 'is' : 's');
            default:
                return $string;
        }
    }
    /**
     * Check if any of the needles are found in any of the haystacks
     */
    public static function contains(string|array $haystacks, string|array $needles): bool
    {
        $haystacks = \RegularLabs\Library\ArrayHelper::toArray($haystacks);
        $needles = \RegularLabs\Library\ArrayHelper::toArray($needles);
        if (empty($haystacks) || empty($needles)) {
            return \false;
        }
        foreach ($haystacks as $haystack) {
            foreach ($needles as $needle) {
                if (!str_contains($haystack, $needle)) {
                    continue;
                }
                return \true;
            }
        }
        return \false;
    }
    /**
     * Converts a string to a UTF-8 encoded string
     */
    public static function convertToUtf8(string $string = ''): string
    {
        $array = \RegularLabs\Library\ArrayHelper::applyMethodToValues([$string]);
        if (!is_null($array)) {
            return $array;
        }
        if (self::detectUTF8($string)) {
            // Already UTF-8, so skip
            return $string;
        }
        if (!function_exists('iconv')) {
            // Still need to find a stable fallback
            return $string;
        }
        $utf8_string = @iconv('UTF8', 'UTF-8//IGNORE', $string);
        if (empty($utf8_string)) {
            return $string;
        }
        return $utf8_string;
    }
    public static function countWords(string $string, int|string $format = 0): array|int
    {
        $format = match ($format) {
            'array', 1 => 'array',
            'numbered', 2 => 'numbered',
            default => 'number',
        };
        $words = preg_split('#[^\p{L}\p{N}\']+#u', $string, -1, $format == 'numbered' ? \PREG_SPLIT_OFFSET_CAPTURE : null);
        switch ($format) {
            case 'array':
                return $words;
            case 'numbered':
                $numbered = [];
                foreach ($words as $word) {
                    $numbered[$word[1]] = $word[0];
                }
                return $numbered;
            case 'number':
            default:
                return count($words);
        }
    }
    /**
     * Check whether string is a UTF-8 encoded string
     */
    public static function detectUTF8(string $string = ''): bool
    {
        // Try to check the string via the mb_check_encoding function
        if (function_exists('mb_check_encoding')) {
            return mb_check_encoding($string, 'UTF-8');
        }
        // Otherwise: Try to check the string via the iconv function
        if (function_exists('iconv')) {
            $converted = iconv('UTF-8', 'UTF-8//IGNORE', $string);
            return md5($converted) == md5($string);
        }
        // As last fallback, check if the preg_match finds anything using the unicode flag
        return preg_match('#.#u', $string);
    }
    public static function escape(string $string): string
    {
        return htmlspecialchars($string, \ENT_QUOTES, 'UTF-8');
    }
    /**
     * Converts a camelcased string to a space separated string
     * eg: FooBar => Foo Bar
     */
    public static function fromCamelCase(string $string): string|array|object
    {
        $array = \RegularLabs\Library\ArrayHelper::applyMethodToValues([$string]);
        if (!is_null($array)) {
            return $array;
        }
        $parts = JNormalise::fromCamelCase($string, \true);
        $parts = \RegularLabs\Library\ArrayHelper::trim($parts);
        return implode(' ', $parts);
    }
    /**
     * Decode html entities in string (or array of strings)
     */
    public static function html_entity_decoder(string $string, int $quote_style = \ENT_QUOTES, string $encoding = 'UTF-8'): string|array|object
    {
        $array = \RegularLabs\Library\ArrayHelper::applyMethodToValues([$string, $quote_style, $encoding]);
        if (!is_null($array)) {
            return $array;
        }
        if (!is_string($string)) {
            return $string;
        }
        $string = html_entity_decode($string, $quote_style | \ENT_HTML5, $encoding);
        $string = str_replace(chr(194) . chr(160), ' ', $string);
        return $string;
    }
    /**
     * Check if string is alphanumerical
     */
    public static function is_alphanumeric(string $string): bool
    {
        if (function_exists('ctype_alnum')) {
            return (bool) ctype_alnum($string);
        }
        return (bool) \RegularLabs\Library\RegEx::match('^[a-z0-9]+$', $string);
    }
    /**
     * Check if string is a valid key / alias (alphanumeric with optional _ or - chars)
     */
    public static function is_key(string $string): bool
    {
        return \RegularLabs\Library\RegEx::match('^[a-z][a-z0-9-_]*$', trim($string));
    }
    /**
     * UTF-8 aware alternative to lcfirst
     */
    public static function lcfirst(string $string): string
    {
        switch (utf8_strlen($string)) {
            case 0:
                return '';
            case 1:
                return utf8_strtolower($string);
            default:
                preg_match('/^(.{1})(.*)$/us', $string, $matches);
                return utf8_strtolower($matches[1]) . $matches[2];
        }
    }
    /**
     * Converts the first letter to lowercase
     * eg: FooBar => fooBar
     * eg: Foo bar => foo bar
     * eg: FOO_BAR => fOO_BAR
     */
    public static function lowerCaseFirst(string|array|object $string): string|array|null
    {
        $array = \RegularLabs\Library\ArrayHelper::applyMethodToValues([$string]);
        if (!is_string($string)) {
            return $array;
        }
        return self::lcfirst($string);
    }
    public static function minify(string $string): string
    {
        // place new lines around string to make regex searching easier
        $string = "\n" . $string . "\n";
        // Remove comment lines
        $string = \RegularLabs\Library\RegEx::replace('\n\s*//.*?\n', '', $string);
        // Remove comment blocks
        $string = \RegularLabs\Library\RegEx::replace('/\*.*?\*/', '', $string);
        // Remove enters
        $string = \RegularLabs\Library\RegEx::replace('\n\s*', ' ', $string);
        // Remove surrounding whitespace
        $string = trim($string);
        return $string;
    }
    /**
     * Normalizes the input provided and returns the normalized string
     */
    public static function normalize(string $string, bool $to_lowercase = \false): string|array|object
    {
        $array = \RegularLabs\Library\ArrayHelper::applyMethodToValues([$string, $to_lowercase]);
        if (!is_null($array)) {
            return $array;
        }
        // Normalizer-class missing!
        if (class_exists('Normalizer', \false)) {
            $string = Normalizer::normalize($string);
        }
        return $to_lowercase ? self::toLowerCase($string) : $string;
    }
    /**
     * Removes html tags from string
     */
    public static function removeHtml(string $string, bool $remove_comments = \false): string|array|object
    {
        $array = \RegularLabs\Library\ArrayHelper::applyMethodToValues([$string, $remove_comments]);
        if (!is_null($array)) {
            return $array;
        }
        return \RegularLabs\Library\Html::removeHtmlTags($string, $remove_comments);
    }
    /**
     * Removes the trailing part of a string if it matches the given $postfix
     */
    public static function removePostfix(string $string, string $postfix): string|array|object
    {
        $array = \RegularLabs\Library\ArrayHelper::applyMethodToValues([$string, $postfix]);
        if (!is_null($array)) {
            return $array;
        }
        if (empty($string) || empty($postfix)) {
            return $string;
        }
        if (!is_string($string) && !is_numeric($string)) {
            return $string;
        }
        $string_length = strlen($string);
        $postfix_length = strlen($postfix);
        $start = $string_length - $postfix_length;
        if (substr($string, $start) !== $postfix) {
            return $string;
        }
        return substr($string, 0, $start);
    }
    /**
     * Removes the first part of a string if it matches the given $prefix
     */
    public static function removePrefix(string $string, string $prefix, bool $keep_leading_slash = \true): string|array|object
    {
        $array = \RegularLabs\Library\ArrayHelper::applyMethodToValues([$string, $prefix, $keep_leading_slash]);
        if (!is_null($array)) {
            return $array;
        }
        if (empty($string) || empty($prefix)) {
            return $string;
        }
        if (!is_string($string) && !is_numeric($string)) {
            return $string;
        }
        $prefix_length = strlen($prefix);
        $start = 0;
        if ($keep_leading_slash && $prefix[0] !== '/' && $string[0] == '/') {
            $start = 1;
        }
        if (substr($string, $start, $prefix_length) !== $prefix) {
            return $string;
        }
        return substr($string, 0, $start) . substr($string, $start + $prefix_length);
    }
    /**
     * Replace the given replace string once in the main string
     */
    public static function replaceOnce(?string $search, ?string $replace, string $string): string
    {
        if (($search ?? '') == '' || ($string ?? '') == '') {
            return $string;
        }
        if (!str_contains($string, $search)) {
            return $string;
        }
        if (($replace ?? '') == '') {
            $replace = '';
        }
        return substr_replace($string, $replace, strpos($string, $search), strlen($search));
    }
    /**
     * Split a long string into parts (array)
     *
     * @param array $delimiters     Array of strings to split the string on
     * @param int   $max_length     Maximum length of each part
     * @param bool  $maximize_parts If true, the different parts will be made as large as possible (combining consecutive short string elements)
     */
    public static function split(string $string, array $delimiters = [], int $max_length = 10000, bool $maximize_parts = \true): array
    {
        // String is too short to split
        if (strlen($string) < $max_length) {
            return [$string];
        }
        // No delimiters given or found
        if (empty($delimiters) || !self::contains($string, $delimiters)) {
            return [$string];
        }
        // preg_quote all delimiters
        $array = preg_split('#(' . \RegularLabs\Library\RegEx::quote($delimiters) . ')#s', $string, -1, \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY);
        if (!$maximize_parts) {
            return $array;
        }
        $new_array = [];
        foreach ($array as $i => $part) {
            // First element, add to new array
            if (!count($new_array)) {
                $new_array[] = $part;
                continue;
            }
            $last_part = end($new_array);
            $last_key = key($new_array);
            // This is the delimiter so add to previous part
            if ($i % 2) {
                // Concatenate part to previous part
                $new_array[$last_key] .= $part;
                continue;
            }
            // If last and current parts are shorter than or same as  max_length, then add to previous part
            if (strlen($last_part) + strlen($part) <= $max_length) {
                $new_array[$last_key] .= $part;
                continue;
            }
            $new_array[] = $part;
        }
        return $new_array;
    }
    /**
     * Converts a string to a camel case
     * eg: foo bar => fooBar
     * eg: foo_bar => fooBar
     * eg: foo-bar => fooBar
     */
    public static function toCamelCase(string $string, bool $keep_duplicate_separators = \true): string
    {
        $array = \RegularLabs\Library\ArrayHelper::applyMethodToValues([$string]);
        if (!is_null($array)) {
            return $array;
        }
        if (empty($string)) {
            return $string;
        }
        return JNormalise::toVariable(self::toSpaceSeparated($string, $keep_duplicate_separators));
    }
    /**
     * Converts a string to a certain case
     */
    public static function toCase(string $string, string $format, bool $to_lowercase = \true): string
    {
        $format = strtolower(str_replace('case', '', $format));
        return match ($format) {
            'lower' => self::toLowerCase($string),
            'upper' => self::toUpperCase($string),
            'lcfirst', 'lower-first' => self::lowerCaseFirst($string),
            'ucfirst', 'upper-first' => self::upperCaseFirst($string),
            'title' => self::toTitleCase($string),
            'camel' => self::toCamelCase($string),
            'dash' => self::toDashCase($string, $to_lowercase),
            'dot' => self::toDotCase($string, $to_lowercase),
            'pascal' => self::toPascalCase($string),
            'underscore' => self::toUnderscoreCase($string, $to_lowercase),
            default => $to_lowercase ? self::toLowerCase($string) : $string,
        };
    }
    /**
     * Converts a string to a camel case
     * eg: FooBar => foo-bar
     * eg: foo_bar => foo-bar
     */
    public static function toDashCase(string|array|object $string, bool $to_lowercase = \true, bool $keep_duplicate_separators = \true): string|array|object
    {
        $array = \RegularLabs\Library\ArrayHelper::applyMethodToValues([$string, $to_lowercase]);
        if (!is_string($string)) {
            return $array;
        }
        $string = preg_replace(self::getSeparatorRegex($keep_duplicate_separators), '-', self::toSpaceSeparated($string, $keep_duplicate_separators));
        return $to_lowercase ? self::toLowerCase($string) : $string;
    }
    /**
     * Converts a string to a camel case
     * eg: FooBar => foo.bar
     * eg: foo_bar => foo.bar
     */
    public static function toDotCase(string|array|object $string, bool $to_lowercase = \true): string|array|object
    {
        $array = \RegularLabs\Library\ArrayHelper::applyMethodToValues([$string, $to_lowercase]);
        if (!is_string($string)) {
            return $array;
        }
        $string = self::toDashCase($string, $to_lowercase);
        return str_replace('-', '.', $string);
    }
    /**
     * Converts a string to a lower case
     * eg: FooBar => foobar
     * eg: foo_bar => foo_bar
     */
    public static function toLowerCase(string|array|object $string): string|array
    {
        $array = \RegularLabs\Library\ArrayHelper::applyMethodToValues([$string]);
        if (!is_string($string)) {
            return $array;
        }
        return self::strtolower($string);
    }
    /**
     * Converts a string to a camel case
     * eg: foo bar => FooBar
     * eg: foo_bar => FooBar
     * eg: foo-bar => FooBar
     */
    public static function toPascalCase(string $string, bool $keep_duplicate_separators = \true): string|array|object
    {
        $array = \RegularLabs\Library\ArrayHelper::applyMethodToValues([$string]);
        if (!is_null($array)) {
            return $array;
        }
        return JNormalise::toCamelCase(self::toSpaceSeparated($string, $keep_duplicate_separators));
    }
    /**
     * Converts a string into space separated form
     * eg: FooBar => Foo Bar
     * eg: foo-bar => foo bar
     */
    public static function toSpaceSeparated(string $string, bool $keep_duplicate_separators = \true): string|array|object
    {
        $array = \RegularLabs\Library\ArrayHelper::applyMethodToValues([$string]);
        if (!is_null($array)) {
            return $array;
        }
        return preg_replace(self::getSeparatorRegex($keep_duplicate_separators), ' ', self::fromCamelCase($string));
    }
    /**
     * Converts an object or array to a single string
     */
    public static function toString(string|array|object $string): string
    {
        if (is_string($string)) {
            return $string;
        }
        foreach ($string as &$part) {
            $part = self::toString($part);
        }
        return \RegularLabs\Library\ArrayHelper::implode((array) $string);
    }
    /**
     * Converts a string to a camel case
     * eg: foo bar => Foo Bar
     * eg: foo_bar => Foo Bar
     * eg: foo-bar => Foo Bar
     */
    public static function toTitleCase(string $string, bool $keep_duplicate_separators = \true): string|array|object
    {
        $array = \RegularLabs\Library\ArrayHelper::applyMethodToValues([$string]);
        if (!is_null($array)) {
            return $array;
        }
        return self::ucwords(self::toSpaceSeparated($string, $keep_duplicate_separators));
    }
    /**
     * Converts a string to a underscore separated string
     * eg: FooBar => foo_bar
     * eg: foo-bar => foo_bar
     */
    public static function toUnderscoreCase(string $string, bool $to_lowercase = \true, bool $keep_duplicate_separators = \true): string|array|object
    {
        $array = \RegularLabs\Library\ArrayHelper::applyMethodToValues([$string, $to_lowercase]);
        if (!is_null($array)) {
            return $array;
        }
        $string = preg_replace(self::getSeparatorRegex($keep_duplicate_separators), '_', self::toSpaceSeparated($string, $keep_duplicate_separators));
        return $to_lowercase ? self::toLowerCase($string) : $string;
    }
    /**
     * Converts a string to a lower case
     * eg: FooBar => FOOBAR
     * eg: foo_bar => FOO_BAR
     */
    public static function toUpperCase(string|array|object $string): string|array|object
    {
        $array = \RegularLabs\Library\ArrayHelper::applyMethodToValues([$string]);
        if (!is_string($string)) {
            return $array;
        }
        return self::strtoupper($string);
    }
    public static function truncate(string $string, int $maxlen): string
    {
        if (self::strlen($string) <= $maxlen) {
            return $string;
        }
        return self::substr($string, 0, $maxlen - 3) . '…';
    }
    /**
     * Converts the first letter to uppercase
     * eg: fooBar => FooBar
     * eg: foo bar => Foo bar
     * eg: foo_bar => Foo_bar
     */
    public static function upperCaseFirst(string|array|object $string): string|array|object
    {
        $array = \RegularLabs\Library\ArrayHelper::applyMethodToValues([$string]);
        if (!is_string($string)) {
            return $array;
        }
        return self::ucfirst($string);
    }
    /**
     * utf8 decode a string (or array of strings)
     */
    public static function utf8_decode(string $string): string|array|object
    {
        $array = \RegularLabs\Library\ArrayHelper::applyMethodToValues([$string]);
        if (!is_null($array)) {
            return $array;
        }
        if (!is_string($string)) {
            return $string;
        }
        if (!function_exists('mb_decode_numericentity')) {
            return $string;
        }
        return mb_decode_numericentity($string, [0x80, 0xffff, 0, ~0], 'UTF-8');
    }
    /**
     * utf8 encode a string (or array of strings)
     */
    public static function utf8_encode(string $string): string|array|object
    {
        $array = \RegularLabs\Library\ArrayHelper::applyMethodToValues([$string]);
        if (!is_null($array)) {
            return $array;
        }
        if (!is_string($string)) {
            return $string;
        }
        if (!function_exists('mb_decode_numericentity')) {
            return $string;
        }
        return mb_encode_numericentity($string, [0x80, 0xffff, 0, ~0], 'UTF-8');
    }
    private static function getSeparatorRegex(bool $keep_duplicate_separators = \true): string
    {
        $regex = '[ \-_]';
        if (!$keep_duplicate_separators) {
            $regex .= '+';
        }
        return '#' . $regex . '#';
    }
}
