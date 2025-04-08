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
class Title
{
    /**
     * Cleans the string to make it usable as a title
     */
    public static function clean(string $string = '', bool $strip_tags = \false, bool $strip_spaces = \true): string
    {
        if ($string == '') {
            return '';
        }
        // remove comment tags
        $string = \RegularLabs\Library\RegEx::replace('<\!--.*?-->', '', $string);
        // replace weird whitespace
        $string = str_replace(chr(194) . chr(160), ' ', $string);
        if ($strip_tags) {
            // remove svgs
            $string = \RegularLabs\Library\RegEx::replace('<svg.*?</svg>', '', $string);
            // remove html tags
            $string = \RegularLabs\Library\RegEx::replace('</?[a-z][^>]*>', '', $string);
            // remove comments tags
            $string = \RegularLabs\Library\RegEx::replace('<\!--.*?-->', '', $string);
        }
        if ($strip_spaces) {
            // Replace html spaces
            $string = str_replace(['&nbsp;', '&#160;'], ' ', $string);
            // Remove duplicate whitespace
            $string = \RegularLabs\Library\RegEx::replace('[ \n\r\t]+', ' ', $string);
        }
        return trim($string);
    }
    /**
     * Creates an array of different syntaxes of titles to match against a url variable
     */
    public static function getUrlMatches(array $titles = []): array
    {
        $matches = [];
        foreach ($titles as $title) {
            $matches[] = $title;
            $matches[] = \RegularLabs\Library\StringHelper::strtolower($title);
        }
        $matches = array_unique($matches);
        foreach ($matches as $title) {
            $matches[] = htmlspecialchars(\RegularLabs\Library\StringHelper::html_entity_decoder($title));
        }
        $matches = array_unique($matches);
        foreach ($matches as $title) {
            $matches[] = urlencode($title);
            if (function_exists('mb_convert_encoding')) {
                $matches[] = mb_convert_encoding($title, 'ISO-8859-1', 'UTF-8');
            }
            $matches[] = str_replace(' ', '', $title);
            $matches[] = trim(\RegularLabs\Library\RegEx::replace('[^a-z0-9]', '', $title));
            $matches[] = trim(\RegularLabs\Library\RegEx::replace('[^a-z]', '', $title));
        }
        $matches = array_unique($matches);
        foreach ($matches as $i => $title) {
            $matches[$i] = trim(str_replace('?', '', $title));
        }
        $matches = array_diff(array_unique($matches), ['', '-']);
        return $matches;
    }
}
