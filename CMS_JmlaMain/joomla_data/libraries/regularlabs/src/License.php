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
use Joomla\CMS\Language\Text as JText;
class License
{
    /**
     * Render the license message for Free versions
     */
    public static function getMessage(string $name, bool $check_pro = \false): string
    {
        if (!$name) {
            return '';
        }
        $alias = \RegularLabs\Library\Extension::getAliasByName($name);
        $name = \RegularLabs\Library\Extension::getNameByAlias($name);
        if ($check_pro && self::isPro($alias)) {
            return '';
        }
        return '<div class="rl-license rl-alert alert alert-warning rl-alert-light">' . '<div>' . JText::sprintf('RL_IS_FREE_VERSION', $name) . '</div>' . '<div>' . JText::_('RL_FOR_MORE_GO_PRO') . '</div>' . '<div>' . '<a href="https://regularlabs.com/purchase/cart/add/' . $alias . '" target="_blank" class="btn btn-sm btn-primary">' . '<span class="icon-basket"></span>&nbsp;&nbsp;' . \RegularLabs\Library\StringHelper::html_entity_decoder(JText::_('RL_GO_PRO')) . '</a>' . '</div>' . '</div>';
    }
    /**
     * Check if the installed version of the extension is a Pro version
     */
    private static function isPro(string $element_name): bool
    {
        $version = \RegularLabs\Library\Extension::getXMLValue('version', $element_name);
        if (!$version) {
            return \false;
        }
        return stripos($version, 'PRO') !== \false;
    }
}
