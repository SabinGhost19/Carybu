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
class ArrayHelper
{
    /**
     * Add a postfix to all keys in an array
     */
    public static function addPostfixToKeys(array|object $array, string $postfix): array|object
    {
        $pefixed = [];
        foreach ($array as $key => $value) {
            $pefixed[\RegularLabs\Library\StringHelper::addPostfix($key, $postfix)] = $value;
        }
        return $pefixed;
    }
    /**
     * Add a postfix to all string values in an array
     */
    public static function addPostfixToValues(array|object $array, string $postfix): array|object
    {
        foreach ($array as &$value) {
            $value = \RegularLabs\Library\StringHelper::addPostfix($value, $postfix);
        }
        return $array;
    }
    /**
     * Add a prefix and postfix to all string values in an array
     */
    public static function addPreAndPostfixToValues(array|object $array, string $prefix, string $postfix, bool $keep_leading_slash = \true): array|object
    {
        foreach ($array as &$value) {
            $value = \RegularLabs\Library\StringHelper::addPrefix($value, $prefix, $keep_leading_slash);
            $value = \RegularLabs\Library\StringHelper::addPostfix($value, $postfix, $keep_leading_slash);
        }
        return $array;
    }
    /**
     * Add a prefix to all keys in an array
     */
    public static function addPrefixToKeys(array|object $array, string $prefix): array|object
    {
        $pefixed = [];
        foreach ($array as $key => $value) {
            $pefixed[\RegularLabs\Library\StringHelper::addPrefix($key, $prefix)] = $value;
        }
        return $pefixed;
    }
    /**
     * Add a prefix to all string values in an array
     */
    public static function addPrefixToValues(array|object $array, string $prefix, bool $keep_leading_slash = \true): array|object
    {
        foreach ($array as &$value) {
            $value = \RegularLabs\Library\StringHelper::addPrefix($value, $prefix, $keep_leading_slash);
        }
        return $array;
    }
    /**
     * Run a method over all values inside the array or object
     */
    public static function applyMethodToKeys(array $attributes, string $class = '', string $method = ''): array|object|null
    {
        if (!$class || !$method) {
            $caller = debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
            $class = $caller['class'];
            $method = $caller['function'];
        }
        $array = array_shift($attributes);
        if (!is_array($array) && !is_object($array)) {
            return null;
        }
        if (empty($array)) {
            return $array;
        }
        $json = json_encode($array);
        foreach ($array as $key => $value) {
            $value_attributes = [$key, ...$attributes];
            $json = str_replace('"' . $key . '":', '"' . $class::$method(...$value_attributes) . '":', $json);
        }
        return json_decode($json, \true);
    }
    /**
     * Run a method over all values inside the array or object
     */
    public static function applyMethodToValues(array $attributes, string $class = '', string $method = '', int $array_number_in_attributes = 0): array|object|null
    {
        if (!$class || !$method) {
            $caller = debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
            $class = $caller['class'];
            $method = $caller['function'];
        }
        $array = $attributes[$array_number_in_attributes];
        if (!is_array($array) && !is_object($array)) {
            return null;
        }
        $as_object = is_object($array);
        foreach ($array as &$value) {
            if (!is_string($value) && !is_null($value)) {
                continue;
            }
            $value_attributes = array_values($attributes);
            $value_attributes[$array_number_in_attributes] = $value;
            $value = $class::$method(...$value_attributes);
        }
        return $as_object ? (object) $array : $array;
    }
    /**
     * Change the case of array keys
     */
    public static function changeKeyCase(array|object|null $array, string $format, bool $to_lowercase = \true): array|object
    {
        if (is_null($array)) {
            return [];
        }
        return self::applyMethodToKeys([$array, $format, $to_lowercase], '\RegularLabs\Library\StringHelper', 'toCase');
    }
    /**
     * Clean array by trimming values and removing empty/false values
     */
    public static function clean(?array $array): array
    {
        if (!is_array($array)) {
            return $array;
        }
        $array = self::trim($array);
        $array = self::unique($array);
        $array = self::removeEmpty($array);
        return $array;
    }
    /**
     * Create a tree from a flat array based on the parent_id value
     */
    public static function createTreeArray(array $array, int $level = 0, int|string $parent = 0, string $id_name = 'id', string $parent_id_name = 'parent_id'): array
    {
        if (empty($array)) {
            return $array;
        }
        $tree = [];
        foreach ($array as $item) {
            $id = $item->{$id_name} ?? 0;
            $parent_id = $item->{$parent_id_name} ?? '';
            if ($parent_id !== $parent) {
                continue;
            }
            $item->level = $level;
            $tree[$id] = $item;
            $children = self::createTreeArray($array, $level + 1, $id, $id_name, $parent_id_name);
            if (empty($children)) {
                continue;
            }
            $tree[$id]->children = $children;
        }
        return $tree;
    }
    /**
     * Check if any of the given values is found in the array
     */
    public static function find(array|string|null $needles, array|null $haystack, bool $strict = \true): bool
    {
        if (!is_array($haystack) || empty($haystack)) {
            return \false;
        }
        $needles = self::toArray($needles);
        foreach ($needles as $value) {
            if (in_array($value, $haystack, $strict)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * Flatten an array of nested arrays, keeping the order
     */
    public static function flatten(array $array): array
    {
        $flattened = [];
        foreach ($array as $nested) {
            if (!is_array($nested)) {
                $flattened[] = $nested;
                continue;
            }
            $flattened = [...$flattened, ...self::flatten($nested)];
        }
        return $flattened;
    }
    /**
     * Flatten a tree array into a single dimension array
     */
    public static function flattenTreeArray(array $array, string $children_name = 'children'): array
    {
        $flat = [];
        foreach ($array as $key => $item) {
            $flat[$key] = $item;
            if (!isset($item->{$children_name})) {
                continue;
            }
            $children = $item->{$children_name};
            unset($flat[$key]->{$children_name});
            $flat = [...$flat, ...self::flattenTreeArray($children, $children_name)];
        }
        foreach ($flat as $key => $item) {
            $check = (array) $item;
            unset($check['level']);
            if (empty($check)) {
                unset($flat[$key]);
            }
        }
        return $flat;
    }
    /**
     * Join array elements with a string
     */
    public static function implode(array|object|string|null $pieces, string $glue = '', string $last_glue = null): string
    {
        if (!is_array($pieces)) {
            $pieces = self::toArray($pieces, $glue);
        }
        if (is_null($last_glue) || $last_glue == $glue || count($pieces) < 2) {
            return implode($glue ?? '', $pieces);
        }
        $last_item = array_pop($pieces);
        return implode($glue ?? '', $pieces) . $last_glue . $last_item;
    }
    /**
     * Removes empty values from the array
     */
    public static function removeEmpty(array $array): array
    {
        if (!is_array($array)) {
            return $array;
        }
        foreach ($array as $key => &$value) {
            if ($key && !is_numeric($key)) {
                continue;
            }
            if ($value !== '') {
                continue;
            }
            unset($array[$key]);
        }
        return $array;
    }
    /**
     * Removes the trailing part of all keys in an array
     */
    public static function removePostfixFromKeys(array $array, string $postfix): array
    {
        $pefixed = [];
        foreach ($array as $key => $value) {
            $pefixed[\RegularLabs\Library\StringHelper::removePostfix($key, $postfix)] = $value;
        }
        return $pefixed;
    }
    /**
     * Removes the trailing part of all string values in an array
     */
    public static function removePostfixFromValues(array $array, string $postfix): array
    {
        foreach ($array as &$value) {
            $value = \RegularLabs\Library\StringHelper::removePostfix($value, $postfix);
        }
        return $array;
    }
    /**
     * Removes the first part of all keys in an array
     */
    public static function removePrefixFromKeys(array $array, string $prefix): array
    {
        $pefixed = [];
        foreach ($array as $key => $value) {
            $pefixed[\RegularLabs\Library\StringHelper::removePrefix($key, $prefix)] = $value;
        }
        return $pefixed;
    }
    /**
     * Removes the first part of all string values in an array
     */
    public static function removePrefixFromValues(array $array, string $prefix, bool $keep_leading_slash = \true): array
    {
        foreach ($array as &$value) {
            $value = \RegularLabs\Library\StringHelper::removePrefix($value, $prefix, $keep_leading_slash);
        }
        return $array;
    }
    /**
     * Set the level on each object based on the parent_id value
     */
    public static function setLevelsByParentIds(array $array, int $starting_level = 0, int $parent = 0, string $id_name = 'id', string $parent_id_name = 'parent_id'): array
    {
        if (empty($array)) {
            return $array;
        }
        $tree = self::createTreeArray($array, $starting_level, $parent, $id_name, $parent_id_name);
        return self::flattenTreeArray($tree);
    }
    /**
     * Sorts the array by keys based on the values of another array
     */
    public static function sortByOtherArray(array $array, array $order): array
    {
        if (empty($order)) {
            return $array;
        }
        uksort($array, function ($key1, $key2) use ($order) {
            return array_search($key1, $order) > array_search($key2, $order);
        });
        return $array;
    }
    /**
     * Convert data (string or object) to an array
     */
    public static function toArray(mixed $data, string $separator = ',', bool $unique = \false, bool $trim = \true): array
    {
        if (is_array($data)) {
            return $data;
        }
        if (is_object($data)) {
            return (array) $data;
        }
        if ($data === '' || is_null($data)) {
            return [];
        }
        if ($separator === '') {
            return [$data];
        }
        // explode on separator, but keep escaped separators
        $splitter = uniqid('RL_SPLIT');
        $data = str_replace($separator, $splitter, $data);
        $data = str_replace('\\' . $splitter, $separator, $data);
        $array = explode($splitter, $data);
        if ($trim) {
            $array = self::trim($array);
        }
        if ($unique) {
            $array = array_unique($array);
        }
        return $array;
    }
    /**
     * Convert an associative array or object to a html style attribute list
     */
    public static function toAttributeString(array $array, string $key_prefix = ''): string
    {
        $array = self::toArray($array);
        return implode(' ', array_map(fn($key, $value) => $key_prefix . $key . '="' . htmlspecialchars($value) . '"', array_keys($array), $array));
    }
    /**
     * Clean array by trimming values
     */
    public static function trim(array $array): array
    {
        if (!is_array($array)) {
            return $array;
        }
        foreach ($array as &$value) {
            if (!is_string($value)) {
                continue;
            }
            $value = trim($value);
        }
        return $array;
    }
    /**
     * Removes duplicate values from the array
     */
    public static function unique(?array $array): array
    {
        if (!is_array($array)) {
            return $array;
        }
        $values = [];
        foreach ($array as $key => $value) {
            if (!is_numeric($key)) {
                continue;
            }
            if (!in_array($value, $values, \true)) {
                $values[] = $value;
                continue;
            }
            unset($array[$key]);
        }
        return $array;
    }
}
