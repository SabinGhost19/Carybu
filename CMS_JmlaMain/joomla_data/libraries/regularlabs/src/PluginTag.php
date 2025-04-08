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
class PluginTag
{
    /**
     * @var array
     */
    static $protected_characters = ['=' => '[[:EQUAL:]]', '"' => '[[:QUOTE:]]', ',' => '[[:COMMA:]]', '|' => '[[:BAR:]]', ':' => '[[:COLON:]]'];
    /**
     * Cleans the given tag word
     *
     * @param string $string
     *
     * @return string
     */
    public static function clean($string = '')
    {
        return \RegularLabs\Library\RegEx::replace('[^a-z0-9-_]', '', $string);
    }
    /**
     * Get the attributes from  plugin style string
     *
     * @param string $string
     * @param string $main_key
     * @param array  $known_boolean_keys
     * @param string $key_format (empty, 'underscore', 'dash')
     * @param array  $keep_escaped_chars
     *
     * @return object
     */
    public static function getAttributesFromString($string = '', $main_key = 'title', $known_boolean_keys = [], $key_format = '', $keep_escaped_chars = null, $convert_numerals = null)
    {
        if (empty($string)) {
            return (object) [];
        }
        if (is_object($string)) {
            return $string;
        }
        if (is_array($string)) {
            return (object) $string;
        }
        // Replace html entity quotes to normal quotes
        if (!str_contains($string, '"')) {
            $string = str_replace('&quot;', '"', $string);
        }
        self::protectSpecialChars($string);
        // replace weird whitespace
        $string = str_replace(chr(194) . chr(160), ' ', $string);
        // Replace html entity spaces between attributes to normal spaces
        $string = \RegularLabs\Library\RegEx::replace('((?:^|")\s*)&nbsp;(\s*(?:[a-z]|$))', '\1 \2', $string);
        // Only one value, so return simple key/value object
        if (!str_contains($string, '|') && !\RegularLabs\Library\RegEx::match('=\s*["\']', $string)) {
            self::unprotectSpecialChars($string, $keep_escaped_chars);
            return (object) [$main_key => $string];
        }
        // Cannot find right syntax, so return simple key/value object
        if (!\RegularLabs\Library\RegEx::matchAll('(?:^|\s)(?<key>[a-z0-9-_\:]+)\s*(?<not>\!?)=\s*(["\'])(?<value>.*?)\3', $string, $matches)) {
            self::unprotectSpecialChars($string, $keep_escaped_chars);
            return (object) [$main_key => $string];
        }
        $tag = (object) [];
        foreach ($matches as $match) {
            $key = \RegularLabs\Library\StringHelper::toCase($match['key'], $key_format);
            $tag->{$key} = self::getAttributeValueFromMatch($match, $known_boolean_keys, $keep_escaped_chars, $convert_numerals);
        }
        return $tag;
    }
    /**
     * Extract the plugin style div tags with the possible attributes. like:
     * {div width:100|float:left}...{/div}
     *
     * @param string $start_tag
     * @param string $end_tag
     * @param string $tag_start
     * @param string $tag_end
     *
     * @return array
     */
    public static function getDivTags($start_tag = '', $end_tag = '', $tag_start = '{', $tag_end = '}')
    {
        $tag_start = \RegularLabs\Library\RegEx::quote($tag_start);
        $tag_end = \RegularLabs\Library\RegEx::quote($tag_end);
        $start_div = ['pre' => '', 'tag' => '', 'post' => ''];
        $end_div = ['pre' => '', 'tag' => '', 'post' => ''];
        if (!empty($start_tag) && \RegularLabs\Library\RegEx::match('^(?<pre>.*?)(?<tag>' . $tag_start . 'div(?: .*?)?' . $tag_end . ')(?<post>.*)$', $start_tag, $match)) {
            $start_div = $match;
        }
        if (!empty($end_tag) && \RegularLabs\Library\RegEx::match('^(?<pre>.*?)(?<tag>' . $tag_start . '/div' . $tag_end . ')(?<post>.*)$', $end_tag, $match)) {
            $end_div = $match;
        }
        if (empty($start_div['tag']) || empty($end_div['tag'])) {
            return [$start_div, $end_div];
        }
        $attribs = trim(\RegularLabs\Library\RegEx::replace($tag_start . 'div(.*)' . $tag_end, '\1', $start_div['tag']));
        $start_div['tag'] = '<div>';
        $end_div['tag'] = '</div>';
        if (empty($attribs)) {
            return [$start_div, $end_div];
        }
        $attribs = self::getDivAttributes($attribs);
        $style = [];
        if (isset($attribs->width)) {
            if (is_numeric($attribs->width)) {
                $attribs->width .= 'px';
            }
            $style[] = 'width:' . $attribs->width;
        }
        if (isset($attribs->height)) {
            if (is_numeric($attribs->height)) {
                $attribs->height .= 'px';
            }
            $style[] = 'height:' . $attribs->height;
        }
        if (isset($attribs->align)) {
            $style[] = 'float:' . $attribs->align;
        }
        if (!isset($attribs->align) && isset($attribs->float)) {
            $style[] = 'float:' . $attribs->float;
        }
        $attribs = isset($attribs->class) ? 'class="' . $attribs->class . '"' : '';
        if ($style != '') {
            $attribs .= ' style="' . implode(';', $style) . ';"';
        }
        $start_div['tag'] = trim('<div ' . trim($attribs)) . '>';
        return [$start_div, $end_div];
    }
    /**
     * Return the Regular Expressions string to match:
     * Plugin type tags inside others
     *
     * @return string
     */
    public static function getRegexInsideTag($start_character = '{', $end_character = '}')
    {
        $s = \RegularLabs\Library\RegEx::quote($start_character);
        $e = \RegularLabs\Library\RegEx::quote($end_character);
        return '(?:[^' . $s . $e . ']*' . $s . '[^' . $e . ']*' . $e . ')*.*?';
    }
    /**
     * Return the Regular Expressions string to match:
     * html before plugin tag
     *
     * @param string $group_id
     *
     * @return string
     */
    public static function getRegexLeadingHtml($group_id = '')
    {
        $group = 'leading_block_element';
        $html_tag_group = 'html_tag';
        if ($group_id) {
            $group .= '_' . $group_id;
            $html_tag_group .= '_' . $group_id;
        }
        $block_elements = \RegularLabs\Library\Html::getBlockElements(['div']);
        $block_element = '(?<' . $group . '>' . implode('|', $block_elements) . ')';
        $other_html = '[^<]*(<(?<' . $html_tag_group . '>[a-z][a-z0-9_-]*)[\s>]([^<]*</(?P=' . $html_tag_group . ')>)?[^<]*)*';
        // Grab starting block element tag and any html after it (that is not the same block element starting/ending tag).
        return '(?:' . '<' . $block_element . '(?: [^>]*)?>' . $other_html . ')?';
    }
    /**
     * Return the Regular Expressions string to match:
     * Different types of spaces
     *
     * @param string $modifier
     *
     * @return string
     */
    public static function getRegexSpaces($modifier = '+')
    {
        return '(?:\s|&nbsp;|&\#160;)' . $modifier;
    }
    /**
     * Return the Regular Expressions string to match:
     * Trailing html tag
     *
     * @param array $elements
     *
     * @return string
     */
    public static function getRegexSurroundingTagPost($elements = [])
    {
        $elements = $elements ?? null ?: [...\RegularLabs\Library\Html::getBlockElements(), 'span'];
        return '(?:(?:\s*<br ?/?>)*\s*<\/(?:' . implode('|', $elements) . ')>)?';
    }
    /**
     * Return the Regular Expressions string to match:
     * Leading html tag
     *
     * @param array $elements
     *
     * @return string
     */
    public static function getRegexSurroundingTagPre($elements = [])
    {
        $elements = $elements ?? null ?: [...\RegularLabs\Library\Html::getBlockElements(), 'span'];
        return '(?:<(?:' . implode('|', $elements) . ')(?: [^>]*)?>\s*(?:<br ?/?>\s*)*)?';
    }
    /**
     * Return the Regular Expressions string to match:
     * Closing html tags
     *
     * @param array $block_elements
     * @param array $inline_elements
     * @param array $excluded_block_elements
     *
     * @return string
     */
    public static function getRegexSurroundingTagsPost($block_elements = [], $inline_elements = ['span', 'strong', 'b', 'em', 'i'], $excluded_block_elements = [])
    {
        $block_elements = $block_elements ?? null ?: \RegularLabs\Library\Html::getBlockElements($excluded_block_elements);
        $regex = '';
        if (!empty($inline_elements)) {
            $regex .= '(?:(?:\s*<br ?/?>)*\s*<\/(?:' . implode('|', $inline_elements) . ')>){0,3}';
        }
        $regex .= '(?:(?:\s*<br ?/?>)*\s*<\/(?:' . implode('|', $block_elements) . ')>)?';
        return $regex;
    }
    /**
     * Return the Regular Expressions string to match:
     * Opening html tags
     *
     * @param array $block_elements
     * @param array $inline_elements
     * @param array $excluded_block_elements
     *
     * @return string
     */
    public static function getRegexSurroundingTagsPre($block_elements = [], $inline_elements = ['span', 'strong', 'b', 'em', 'i'], $excluded_block_elements = [])
    {
        $block_elements = $block_elements ?? null ?: \RegularLabs\Library\Html::getBlockElements($excluded_block_elements);
        $regex = '(?:<(?:' . implode('|', $block_elements) . ')(?: [^>]*)?>\s*(?:<br ?/?>\s*)*)?';
        if (!empty($inline_elements)) {
            $regex .= '(?:<(?:' . implode('|', $inline_elements) . ')(?: [^>]*)?>\s*(?:<br ?/?>\s*)*){0,3}';
        }
        return $regex;
    }
    /**
     * Return the Regular Expressions string to match:
     * Plugin style tags
     *
     * @param array|string $tags
     * @param bool         $include_no_attributes
     * @param bool         $include_ending
     * @param array        $required_attributes
     *
     * @return string
     */
    public static function getRegexTags($tags, $include_no_attributes = \true, $include_ending = \true, $required_attributes = [])
    {
        $tags = \RegularLabs\Library\ArrayHelper::toArray($tags);
        $tags = count($tags) > 1 ? '(?:' . implode('|', $tags) . ')' : $tags[0];
        $value = '(?:\s*=\s*(?:"[^"]*"|\'[^\']*\'|[a-z0-9-_]+))?';
        $attributes = '(?:\s+[a-z0-9-_]+' . $value . ')+';
        $required_attributes = \RegularLabs\Library\ArrayHelper::toArray($required_attributes);
        if (!empty($required_attributes)) {
            $attributes = '(?:' . $attributes . ')?' . '(?:\s+' . implode('|', $required_attributes) . ')' . $value . '(?:' . $attributes . ')?';
        }
        if ($include_no_attributes) {
            $attributes = '\s*(?:' . $attributes . ')?';
        }
        if (!$include_ending) {
            return '<' . $tags . $attributes . '\s*/?>';
        }
        return '<(?:\/' . $tags . '|' . $tags . $attributes . '\s*/?)\s*/?>';
    }
    /**
     * Return the Regular Expressions string to match:
     * html after plugin tag
     *
     * @param string $group_id
     *
     * @return string
     */
    public static function getRegexTrailingHtml($group_id = '')
    {
        $group = 'leading_block_element';
        if ($group_id) {
            $group .= '_' . $group_id;
        }
        // If the grouped name is found, then grab all content till ending html tag is found. Otherwise grab nothing.
        return '(?(<' . $group . '>)' . '(?:.*?</(?P=' . $group . ')>)?' . ')';
    }
    /**
     * Replace special characters in the string with the protected versions
     *
     * @param string $string
     */
    public static function protectSpecialChars(&$string)
    {
        $unescaped_chars = array_keys(self::$protected_characters);
        array_walk($unescaped_chars, function (&$char) {
            $char = '\\' . $char;
        });
        // replace escaped characters with special markup
        $string = str_replace($unescaped_chars, array_values(self::$protected_characters), $string);
        if (!\RegularLabs\Library\RegEx::matchAll('(<[a-z][a-z0-9-_]*(?: [a-z0-9-_]*=".*?")* ?/?>|{.*?}|\[.*?\])', $string, $tags, null, \PREG_PATTERN_ORDER)) {
            return;
        }
        foreach ($tags[0] as $tag) {
            // replace unescaped characters with special markup
            $protected = str_replace(['=', '"'], [self::$protected_characters['='], self::$protected_characters['"']], $tag);
            $string = str_replace($tag, $protected, $string);
        }
    }
    /**
     * Replace keys aliases with the main key names in an object
     *
     * @param object|string $attributes
     * @param array         $key_aliases
     * @param bool          $handle_plurals
     *
     * @deprecated Use ObjectHelper::replaceKeys()
     */
    public static function replaceKeyAliases(&$attributes, $key_aliases = [], $handle_plurals = \false)
    {
        return \RegularLabs\Library\ObjectHelper::replaceKeys($attributes, $key_aliases);
    }
    /**
     * Replace protected characters in the string with the original special versions
     *
     * @param string $string
     * @param array  $keep_escaped_chars
     */
    public static function unprotectSpecialChars(&$string, $keep_escaped_chars = null)
    {
        $unescaped_chars = array_keys(self::$protected_characters);
        $keep_escaped_chars = !is_null($keep_escaped_chars) ? \RegularLabs\Library\ArrayHelper::toArray($keep_escaped_chars) : [];
        if (!empty($keep_escaped_chars)) {
            array_walk($unescaped_chars, function (&$char, $key, $keep_escaped_chars) {
                if (is_array($keep_escaped_chars) && !in_array($char, $keep_escaped_chars, \true)) {
                    return;
                }
                $char = '\\' . $char;
            }, $keep_escaped_chars);
        }
        // replace special markup with unescaped characters
        $string = str_replace(array_values(self::$protected_characters), $unescaped_chars, $string);
    }
    /**
     * Get the value from a found attribute match
     *
     * @param array $match
     * @param array $known_boolean_keys
     * @param array $keep_escaped_chars
     *
     * @return bool|int|string
     */
    private static function getAttributeValueFromMatch($match, $known_boolean_keys = [], $keep_escaped_chars = [','], $convert_numerals = \true)
    {
        $value = $match['value'];
        self::unprotectSpecialChars($value, $keep_escaped_chars);
        if (is_numeric($value) && (in_array($match['key'], $known_boolean_keys, \true) || in_array(strtolower($match['key']), $known_boolean_keys, \true))) {
            $value = $value ? 'true' : 'false';
        }
        // Convert numeric values to ints/floats
        if ($convert_numerals && is_numeric($value) && \RegularLabs\Library\RegEx::match('^[0-9\.]+$', $value)) {
            $value = $value + 0;
        }
        // Convert boolean values to actual booleans
        if ($value === 'true' || $value === \true) {
            return $match['not'] ? \false : \true;
        }
        if ($value === 'false' || $value === \false) {
            return $match['not'] ? \true : \false;
        }
        return $match['not'] ? '!NOT!' . $value : $value;
    }
    /**
     * Get the attributes from a plugin style div tag
     */
    private static function getDivAttributes(string $string): object
    {
        if (str_contains($string, '="')) {
            return self::getAttributesFromString($string);
        }
        $parts = explode('|', $string);
        $attributes = (object) [];
        foreach ($parts as $e) {
            if (!str_contains($e, ':')) {
                continue;
            }
            [$key, $val] = explode(':', $e, 2);
            $attributes->{$key} = $val;
        }
        return $attributes;
    }
}
