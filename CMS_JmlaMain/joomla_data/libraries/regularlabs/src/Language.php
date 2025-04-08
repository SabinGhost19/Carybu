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
use Joomla\CMS\Factory as JFactory;
class Language
{
    /**
     * Load the language of the given extension
     */
    public static function load(string $extension = 'plg_system_regularlabs', string $basePath = '', bool $reload = \false): bool
    {
        if ($basePath && JFactory::getApplication()->getLanguage()->load($extension, $basePath, null, $reload)) {
            return \true;
        }
        $basePath = \RegularLabs\Library\Extension::getPath($extension, $basePath, 'language');
        return JFactory::getApplication()->getLanguage()->load($extension, $basePath, null, $reload);
    }
}
