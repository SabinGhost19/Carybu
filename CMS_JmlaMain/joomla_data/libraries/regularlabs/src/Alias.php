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
use Joomla\CMS\Application\ApplicationHelper as JApplicationHelper;
use Joomla\CMS\Factory as JFactory;
class Alias
{
    /**
     * Creates an alias from a string
     */
    public static function get(string $string = '', bool $unicode = \false): string
    {
        if ($string == '') {
            return '';
        }
        $string = \RegularLabs\Library\StringHelper::removeHtml($string);
        if ($unicode || JFactory::getApplication()->get('unicodeslugs') == 1) {
            return self::stringURLUnicodeSlug($string);
        }
        // Remove < > html entities
        $string = str_replace(['&lt;', '&gt;'], '', $string);
        // Convert html entities
        $string = \RegularLabs\Library\StringHelper::html_entity_decoder($string);
        return JApplicationHelper::stringURLSafe($string);
    }
    /**
     * Creates a unicode alias from a string
     * Based on stringURLUnicodeSlug method from the unicode slug plugin by infograf768
     */
    private static function stringURLUnicodeSlug(string $string = ''): string
    {
        if ($string == '') {
            return '';
        }
        // Remove < > html entities
        $string = str_replace(['&lt;', '&gt;'], '', $string);
        // Convert html entities
        $string = \RegularLabs\Library\StringHelper::html_entity_decoder($string);
        // Convert to lowercase
        $string = \RegularLabs\Library\StringHelper::strtolower($string);
        // remove html tags
        $string = \RegularLabs\Library\RegEx::replace('</?[a-z][^>]*>', '', $string);
        // remove comments tags
        $string = \RegularLabs\Library\RegEx::replace('<\!--.*?-->', '', $string);
        // Replace weird whitespace characters like (Â) with spaces
        //$string = str_replace(array(chr(160), chr(194)), ' ', $string);
        $string = str_replace(" ", ' ', $string);
        $string = str_replace(" ", ' ', $string);
        // ascii only
        // Replace double byte whitespaces by single byte (East Asian languages)
        $string = str_replace("　", ' ', $string);
        // Remove any '-' from the string as they will be used as concatenator.
        // Would be great to let the spaces in but only Firefox is friendly with this
        $string = str_replace('-', ' ', $string);
        // Replace forbidden characters by whitespaces
        $string = \RegularLabs\Library\RegEx::replace('[' . \RegularLabs\Library\RegEx::quote(',:#$*"@+=;&.%()[]{}/\'\|') . ']', " ", $string);
        // Delete all characters that should not take up any space, like: ?
        $string = \RegularLabs\Library\RegEx::replace('[' . \RegularLabs\Library\RegEx::quote('?!¿¡') . ']', '', $string);
        // Trim white spaces at beginning and end of alias and make lowercase
        $string = trim($string);
        // Remove any duplicate whitespace and replace whitespaces by hyphens
        $string = \RegularLabs\Library\RegEx::replace('\x20+', '-', $string);
        // Remove leading and trailing hyphens
        $string = trim($string, '-');
        return $string;
    }
}
