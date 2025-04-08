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
use DOMDocument;
class Html
{
    /**
     * Removes complete html tag pairs from the concatenated parts
     */
    public static function cleanSurroundingTags(array $parts, array $elements = ['p', 'span', 'strong', 'b', 'em', 'i']): array
    {
        $breaks = '(?:(?:<br ?/?>|<\!--[^>]*-->|:\|:)\s*)*';
        $keys = array_keys($parts);
        $string = implode(':|:', $parts);
        \RegularLabs\Library\Protect::protectHtmlCommentTags($string);
        // Remove empty tags
        $regex = '<(?<tag>' . implode('|', $elements) . ')(?: [^>]*)?>\s*(?<breaks>' . $breaks . ')<\/\1>\s*';
        while (\RegularLabs\Library\RegEx::match($regex, $string, $match)) {
            $string = str_replace($match[0], $match['breaks'], $string);
        }
        // Remove paragraphs around block elements
        $block_elements = ['p', 'div', 'table', 'tr', 'td', 'thead', 'tfoot', 'h[1-6]'];
        $block_elements = '(?<element>' . implode('|', $block_elements) . ')';
        $regex = '(?<p_tag><p(?: [^>]*)?>)(?<breaks>\s*' . $breaks . ')(?<block_tag><' . $block_elements . '(?: [^>]*)?>)';
        while (\RegularLabs\Library\RegEx::match($regex, $string, $match)) {
            $tags = $match['block_tag'];
            if ($match['element'] == 'p') {
                $tags = $match['p_tag'] . $tags;
                self::combinePTags($tags);
            }
            $string = str_replace($match[0], $match['breaks'] . $tags, $string);
        }
        $regex = '(</' . $block_elements . '>\s*' . $breaks . ')</p>';
        while (\RegularLabs\Library\RegEx::match($regex, $string, $match)) {
            $string = str_replace($match[0], $match[1], $string);
        }
        \RegularLabs\Library\Protect::unprotect($string);
        $parts = explode(':|:', $string);
        $new_tags = [];
        foreach ($parts as $key => $val) {
            $key = $keys[$key] ?? $key;
            $new_tags[$key] = $val;
        }
        return $new_tags;
    }
    /**
     * Combine duplicate <p> tags
     * input: <p class="aaa" a="1"><!-- ... --><p class="bbb" b="2">
     * output: <p class="aaa bbb" a="1" b="2"><!-- ... -->
     */
    public static function combinePTags(string &$string): void
    {
        if ($string == '') {
            return;
        }
        $p_start_tag = '<p(?: [^>]*)?>';
        $optional_tags = '\s*(?:<\!--[^>]*-->|&nbsp;|&\#160;)*\s*';
        \RegularLabs\Library\Protect::protectHtmlCommentTags($string);
        \RegularLabs\Library\RegEx::matchAll('(' . $p_start_tag . ')(' . $optional_tags . ')(' . $p_start_tag . ')', $string, $tags);
        if (empty($tags)) {
            \RegularLabs\Library\Protect::unprotect($string);
            return;
        }
        foreach ($tags as $tag) {
            $string = str_replace($tag[0], $tag[2] . \RegularLabs\Library\HtmlTag::combine($tag[1], $tag[3]), $string);
        }
        \RegularLabs\Library\Protect::unprotect($string);
    }
    /**
     * Check if string contains block elements
     */
    public static function containsBlockElements(string $string): string
    {
        return \RegularLabs\Library\RegEx::match('</?(' . implode('|', self::getBlockElements()) . ')(?: [^>]*)?>', $string);
    }
    /**
     * Convert content saved in a WYSIWYG editor to plain text (like removing html tags)
     */
    public static function convertWysiwygToPlainText(string $string): string
    {
        // replace chr style enters with normal enters
        $string = str_replace([chr(194) . chr(160), '&#160;', '&nbsp;'], ' ', $string);
        // replace linebreak tags with normal linebreaks (paragraphs, enters, etc).
        $enter_tags = ['p', 'br'];
        $regex = '</?((' . implode(')|(', $enter_tags) . '))+[^>]*?>\n?';
        $string = \RegularLabs\Library\RegEx::replace($regex, " \n", $string);
        // replace indent characters with spaces
        $string = \RegularLabs\Library\RegEx::replace('<img [^>]*/sourcerer/images/tab\.png[^>]*>', '    ', $string);
        // strip all other tags
        $regex = '<(/?\w+((\s+\w+(\s*=\s*(?:".*?"|\'.*?\'|[^\'">\s]+))?)+\s*|\s*)/?)>';
        $string = \RegularLabs\Library\RegEx::replace($regex, '', $string);
        // reset htmlentities
        $string = \RegularLabs\Library\StringHelper::html_entity_decoder($string);
        // convert protected html entities &_...; -> &...;
        $string = \RegularLabs\Library\RegEx::replace('&_([a-z0-9\#]+?);', '&\1;', $string);
        return $string;
    }
    /**
     * Fix broken/invalid html syntax in a string
     */
    public static function fix(string $string): string
    {
        if (!self::containsBlockElements($string)) {
            return $string;
        }
        // Convert utf8 characters to html entities
        if (function_exists('mb_decode_numericentity')) {
            $string = mb_encode_numericentity($string, [0x80, 0xffff, 0, ~0], 'UTF-8');
        }
        $string = self::protectSpecialCode($string);
        $string = self::convertDivsInsideInlineElementsToSpans($string);
        $string = self::removeParagraphsAroundBlockElements($string);
        $string = self::removeInlineElementsAroundBlockElements($string);
        $string = self::fixParagraphsAroundParagraphElements($string);
        $string = class_exists('DOMDocument') ? self::fixUsingDOMDocument($string) : self::fixUsingCustomFixer($string);
        $string = self::unprotectSpecialCode($string);
        // Convert html entities back to utf8 characters
        if (function_exists('mb_decode_numericentity')) {
            $string = mb_decode_numericentity($string, [0x80, 0xffff, 0, ~0], 'UTF-8');
        }
        $string = self::removeParagraphsAroundComments($string);
        return $string;
    }
    /**
     * Fix broken/invalid html syntax in an array of strings
     */
    public static function fixArray(array $array): array
    {
        $splitter = ':|:';
        $string = self::fix(implode($splitter, $array));
        $parts = self::removeEmptyTags(explode($splitter, $string));
        // use original keys but new values
        return array_combine(array_keys($array), $parts);
    }
    /**
     * Return an array of block element names, optionally without any of the names given $exclude
     */
    public static function getBlockElements(array $exclude = []): array
    {
        if (!is_array($exclude)) {
            $exclude = [$exclude];
        }
        $elements = ['div', 'p', 'pre', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
        $elements = array_diff($elements, $exclude);
        $elements = implode(',', $elements);
        $elements = str_replace('h1,h2,h3,h4,h5,h6', 'h[1-6]', $elements);
        $elements = explode(',', $elements);
        return $elements;
    }
    /**
     * Return an array of block element names, without divs and any of the names given $exclude
     */
    public static function getBlockElementsNoDiv(array $exclude = []): array
    {
        return array_diff(self::getBlockElements($exclude), ['div']);
    }
    /**
     * Extract the <body>...</body> part from an entire html output string
     */
    public static function getBody(string $html, bool $include_body_tag = \true): array
    {
        if (!str_contains($html, '<body') || !str_contains($html, '</body>')) {
            return ['', $html, ''];
        }
        // Force string to UTF-8
        $html = \RegularLabs\Library\StringHelper::convertToUtf8($html);
        $split = explode('<body', $html, 2);
        $pre = $split[0];
        $split = explode('>', $split[1], 2);
        $body_start = '<body' . $split[0] . '>';
        $body_end = '</body>';
        $split = explode('</body>', $split[1]);
        $post = array_pop($split);
        $body = implode('</body>', $split);
        if (!$include_body_tag) {
            return [$pre . $body_start, $body, $body_end . $post];
        }
        return [$pre, $body_start . $body . $body_end, $post];
    }
    /**
     * Search the string for the start and end searches and split the string in a pre, body and post part
     * This is used to be able to do replacements on the body part, which will be lighter than doing it on the entire string
     */
    public static function getContentContainingSearches(string $string, array $start_searches = [], array $end_searches = [], int $start_offset = 1000, ?int $end_offset = null): array
    {
        // String is too short to split and search through
        if (strlen($string) < 2000) {
            return ['', $string, ''];
        }
        $end_offset = is_null($end_offset) ? $start_offset : $end_offset;
        $found = \false;
        $start_split = strlen($string);
        foreach ($start_searches as $search) {
            $pos = strpos($string, $search);
            if ($pos === \false) {
                continue;
            }
            $start_split = min($start_split, $pos);
            $found = \true;
        }
        // No searches are found
        if (!$found) {
            return [$string, '', ''];
        }
        // String is too short to split
        if (strlen($string) < $start_offset + $end_offset + 1000) {
            return ['', $string, ''];
        }
        $start_split = max($start_split - $start_offset, 0);
        $pre = substr($string, 0, $start_split);
        $string = substr($string, $start_split);
        self::fixBrokenTagsByPreString($pre, $string);
        if (empty($end_searches)) {
            $end_searches = $start_searches;
        }
        $end_split = 0;
        $found = \false;
        foreach ($end_searches as $search) {
            $pos = strrpos($string, $search);
            if ($pos === \false) {
                continue;
            }
            $end_split = max($end_split, $pos + strlen($search));
            $found = \true;
        }
        // No end split is found, so don't split remainder
        if (!$found) {
            return [$pre, $string, ''];
        }
        $end_split = min($end_split + $end_offset, strlen($string));
        $post = substr($string, $end_split);
        $string = substr($string, 0, $end_split);
        self::fixBrokenTagsByPostString($post, $string);
        return [$pre, $string, $post];
    }
    /**
     * Return an array of inline element names, optionally without any of the names given $exclude
     */
    public static function getInlineElements(array $exclude = []): array
    {
        if (!is_array($exclude)) {
            $exclude = [$exclude];
        }
        $elements = ['span', 'code', 'a', 'strong', 'b', 'em', 'i', 'u', 'big', 'small', 'font', 'sup', 'sub'];
        return array_diff($elements, $exclude);
    }
    /**
     * Return an array of block element names, without anchors (a) and any of the names given $exclude
     */
    public static function getInlineElementsNoAnchor(array $exclude = []): array
    {
        return array_diff(self::getInlineElements($exclude), ['a']);
    }
    /**
     * Remove empty tags
     */
    public static function removeEmptyTagPairs(string $string, array $elements = ['p', 'span']): string
    {
        $breaks = '(?:(?:<br ?/?>|<\!--[^>]*-->)\s*)*';
        $regex = '<(' . implode('|', $elements) . ')(?: [^>]*)?>\s*(' . $breaks . ')<\/\1>\s*';
        \RegularLabs\Library\Protect::protectHtmlCommentTags($string);
        while (\RegularLabs\Library\RegEx::match($regex, $string, $match)) {
            $string = str_replace($match[0], $match[2], $string);
        }
        \RegularLabs\Library\Protect::unprotect($string);
        return $string;
    }
    /**
     * Removes empty tags which span concatenating parts in the array
     */
    public static function removeEmptyTags(array $array): array
    {
        $splitter = ':|:';
        $comments = '(?:\s*<\!--[^>]*-->\s*)*';
        $string = implode($splitter, $array);
        \RegularLabs\Library\Protect::protectHtmlCommentTags($string);
        $string = \RegularLabs\Library\RegEx::replace('<([a-z][a-z0-9]*)(?: [^>]*)?>\s*(' . $comments . \RegularLabs\Library\RegEx::quote($splitter) . $comments . ')\s*</\1>', '\2', $string);
        \RegularLabs\Library\Protect::unprotect($string);
        return explode($splitter, $string);
    }
    /**
     * Removes html tags from string
     */
    public static function removeHtmlTags(string $string, bool $remove_comments = \false): string
    {
        // remove pagenavcounter
        $string = \RegularLabs\Library\RegEx::replace('<div class="pagenavcounter">.*?</div>', ' ', $string);
        // remove pagenavbar
        $string = \RegularLabs\Library\RegEx::replace('<div class="pagenavbar">(<div>.*?</div>)*</div>', ' ', $string);
        // remove inline scripts
        $string = \RegularLabs\Library\RegEx::replace('<script[^a-z0-9].*?</script>', '', $string);
        $string = \RegularLabs\Library\RegEx::replace('<noscript[^a-z0-9].*?</noscript>', '', $string);
        // remove inline styles
        $string = \RegularLabs\Library\RegEx::replace('<style[^a-z0-9].*?</style>', '', $string);
        // remove inline html tags
        $string = \RegularLabs\Library\RegEx::replace('</?(' . implode('|', self::getInlineElements()) . ')( [^>]*)?>', '', $string);
        if ($remove_comments) {
            // remove html comments
            $string = \RegularLabs\Library\RegEx::replace('<!--.*?-->', ' ', $string);
        }
        // replace other tags with a space
        $string = \RegularLabs\Library\RegEx::replace('</?[a-z].*?>', ' ', $string);
        // remove double whitespace
        $string = trim(\RegularLabs\Library\RegEx::replace('(\s)[ ]+', '\1', $string));
        return $string;
    }
    /**
     * Remove inline elements around block elements
     */
    public static function removeInlineElementsAroundBlockElements(string $string): string
    {
        $string = \RegularLabs\Library\RegEx::replace('(?:<(?:' . implode('|', self::getInlineElementsNoAnchor()) . ')(?: [^>]*)?>\s*)' . '(</?(?:' . implode('|', self::getBlockElements()) . ')(?: [^>]*)?>)', '\1', $string);
        $string = \RegularLabs\Library\RegEx::replace('(</?(?:' . implode('|', self::getBlockElements()) . ')(?: [^>]*)?>)' . '(?:\s*</(?:' . implode('|', self::getInlineElementsNoAnchor()) . ')>)', '\1', $string);
        return $string;
    }
    /**
     * Convert <div> tags inside inline elements to <span> tags
     */
    private static function convertDivsInsideInlineElementsToSpans(string $string): string
    {
        if (!str_contains($string, '</div>')) {
            return $string;
        }
        // Ignore block elements inside anchors
        $regex = '<(' . implode('|', self::getInlineElementsNoAnchor()) . ')(?: [^>]*)?>.*?</\1>';
        \RegularLabs\Library\RegEx::matchAll($regex, $string, $matches, '', \PREG_PATTERN_ORDER);
        if (empty($matches)) {
            return $string;
        }
        $matches = array_unique($matches[0]);
        $searches = [];
        $replacements = [];
        foreach ($matches as $match) {
            if (!str_contains($match, '</div>')) {
                continue;
            }
            $searches[] = $match;
            $replacements[] = str_replace(['<div>', '<div ', '</div>'], ['<span>', '<span ', '</span>'], $match);
        }
        if (empty($searches)) {
            return $string;
        }
        return str_replace($searches, $replacements, $string);
    }
    /**
     * Prevents broken html tags at the beginning of $pre (other half at end of $string)
     * It will move the broken part to the end of $string to complete it
     */
    private static function fixBrokenTagsByPostString(string &$post, string &$string): void
    {
        if (!\RegularLabs\Library\RegEx::match('<(\![^>]*|/?[a-z][^>]*(="[^"]*)?)$', $string, $match)) {
            return;
        }
        if (!\RegularLabs\Library\RegEx::match('^[^>]*>', $post, $match)) {
            return;
        }
        $post = substr($post, strlen($match[0]));
        $string .= $match[0];
    }
    /**
     * Prevents broken html tags at the end of $pre (other half at beginning of $string)
     * It will move the broken part to the beginning of $string to complete it
     */
    private static function fixBrokenTagsByPreString(string &$pre, string &$string): void
    {
        if (!\RegularLabs\Library\RegEx::match('<(\![^>]*|/?[a-z][^>]*(="[^"]*)?)$', $pre, $match)) {
            return;
        }
        $pre = substr($pre, 0, strlen($pre) - strlen($match[0]));
        $string = $match[0] . $string;
    }
    /**
     * Fix <p> tags around other <p> elements
     */
    private static function fixParagraphsAroundParagraphElements(string $string): string
    {
        if (!str_contains($string, '</p>')) {
            return $string;
        }
        $parts = explode('</p>', $string);
        $ending = '</p>' . array_pop($parts);
        foreach ($parts as &$part) {
            if (!str_contains($part, '<p>') && !str_contains($part, '<p ')) {
                $part = '<p>' . $part;
                continue;
            }
            $part = \RegularLabs\Library\RegEx::replace('(<p(?: [^>]*)?>.*?)(<p(?: [^>]*)?>)', '\1</p>\2', $part);
        }
        return implode('</p>', $parts) . $ending;
    }
    /**
     * Fix broken/invalid html syntax in a string using custom code as an alternative to php DOMDocument functionality
     */
    private static function fixUsingCustomFixer(string $string): string
    {
        $block_regex = '<(' . implode('|', self::getBlockElementsNoDiv()) . ')[\s>]';
        $string = \RegularLabs\Library\RegEx::replace('(' . $block_regex . ')', '[:SPLIT-BLOCK:]\1', $string);
        $parts = explode('[:SPLIT-BLOCK:]', $string);
        foreach ($parts as $i => &$part) {
            if (!\RegularLabs\Library\RegEx::match('^' . $block_regex, $part, $type)) {
                continue;
            }
            $type = strtolower($type[1]);
            // remove endings of other block elements
            $part = \RegularLabs\Library\RegEx::replace('</(?:' . implode('|', self::getBlockElementsNoDiv($type)) . ')>', '', $part);
            if (str_contains($part, '</' . $type . '>')) {
                continue;
            }
            // Add ending tag once
            $part = \RegularLabs\Library\RegEx::replaceOnce('(\s*)$', '</' . $type . '>\1', $part);
            // Remove empty block tags
            $part = \RegularLabs\Library\RegEx::replace('^<' . $type . '(?: [^>]*)?>\s*</' . $type . '>', '', $part);
        }
        return implode('', $parts);
    }
    /**
     * Fix broken/invalid html syntax in a string using php DOMDocument functionality
     */
    private static function fixUsingDOMDocument(string $string): string
    {
        $doc = new DOMDocument();
        $doc->substituteEntities = \false;
        [$pre, $body, $post] = \RegularLabs\Library\Html::getBody($string, \false);
        if (function_exists('mb_encode_numericentity')) {
            $body = mb_encode_numericentity($body, [0x10000, 0x10ffff, 0, 0xffffff], 'UTF-8');
        }
        // Add temporary document structures
        $body = '<html><body><div>' . $body . '</div></body></html>';
        // Suppress errors and prevent adding implied HTML/body tags
        @$doc->loadHTML($body, \LIBXML_HTML_NOIMPLIED | \LIBXML_HTML_NODEFDTD);
        $body = $doc->saveHTML();
        if (str_contains($doc->documentElement->textContent, 'Ã')) {
            // Need to do this utf8 workaround to deal with special characters
            // DOMDocument doesn't seem to deal with them very well
            // See: https://stackoverflow.com/questions/8218230/php-domdocument-loadhtml-not-encoding-utf-8-correctly/47396055#47396055
            $body = \RegularLabs\Library\StringHelper::utf8_decode($doc->saveHTML($doc->documentElement));
        }
        // Remove temporary document structures and surrounding div
        $body = \RegularLabs\Library\RegEx::replace('^.*?<html>.*?(?:<head>(.*)</head>.*?)?<body>\s*<div>(.*)</div>\s*</body>.*?$', '\1\2', $body);
        // Remove leading/trailing empty paragraph
        $body = \RegularLabs\Library\RegEx::replace('(^\s*<div>\s*</div>|<div>\s*</div>\s*$)', '', $body);
        // Remove leading/trailing empty paragraph
        $body = \RegularLabs\Library\RegEx::replace('(^\s*<div>\s*</div>|<div>\s*</div>\s*$)', '', $body);
        // Remove leading/trailing empty paragraph
        $body = \RegularLabs\Library\RegEx::replace('(^\s*<p(?: [^>]*)?>\s*</p>|<p(?: [^>]*)?>\s*</p>\s*$)', '', $body);
        return $pre . $body . $post;
    }
    /**
     * Protect plugin style tags and php
     */
    private static function protectSpecialCode(string $string): string
    {
        // Protect PHP code
        \RegularLabs\Library\Protect::protectByRegex($string, '(<|&lt;)\?php\s.*?\?(>|&gt;)');
        // Protect {...} tags
        \RegularLabs\Library\Protect::protectByRegex($string, '\{[a-z0-9].*?\}');
        // Protect [...] tags
        \RegularLabs\Library\Protect::protectByRegex($string, '\[[a-z0-9].*?\]');
        // Protect scripts
        \RegularLabs\Library\Protect::protectByRegex($string, '<script[^>]*>.*?</script>');
        // Protect css
        \RegularLabs\Library\Protect::protectByRegex($string, '<style[^>]*>.*?</style>');
        \RegularLabs\Library\Protect::convertProtectionToHtmlSafe($string);
        return $string;
    }
    /**
     * Remove <p> tags around block elements
     */
    private static function removeParagraphsAroundBlockElements(string $string): string
    {
        if (!str_contains($string, '</p>')) {
            return $string;
        }
        \RegularLabs\Library\Protect::protectHtmlCommentTags($string);
        $string = \RegularLabs\Library\RegEx::replace('<p(?: [^>]*)?>\s*' . '((?:<\!--[^>]*-->\s*)*</?(?:' . implode('|', self::getBlockElements()) . ')' . '(?: [^>]*)?>)', '\1', $string);
        $string = \RegularLabs\Library\RegEx::replace('(</?(?:' . implode('|', self::getBlockElements()) . ')' . '(?: [^>]*)?>(?:\s*<\!--[^>]*-->)*)' . '(?:\s*</p>)', '\1', $string);
        \RegularLabs\Library\Protect::unprotect($string);
        return $string;
    }
    /**
     * Remove <p> tags around comments
     */
    private static function removeParagraphsAroundComments(string $string): string
    {
        if (!str_contains($string, '</p>')) {
            return $string;
        }
        \RegularLabs\Library\Protect::protectHtmlCommentTags($string);
        $string = \RegularLabs\Library\RegEx::replace('(?:<p(?: [^>]*)?>\s*)' . '(<\!--[^>]*-->)' . '(?:\s*</p>)', '\1', $string);
        \RegularLabs\Library\Protect::unprotect($string);
        return $string;
    }
    /**
     * Unprotect protected tags
     */
    private static function unprotectSpecialCode(string $string): string
    {
        \RegularLabs\Library\Protect::unprotectHtmlSafe($string);
        return $string;
    }
}
