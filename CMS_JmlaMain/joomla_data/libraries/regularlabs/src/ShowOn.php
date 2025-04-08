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
use Joomla\CMS\Form\FormHelper as JFormHelper;
class ShowOn
{
    public static function close()
    {
        return '</div>';
    }
    public static function open(string $condition = '', string $formControl = '', string $group = '', string $class = ''): string
    {
        if (!$condition) {
            return self::close();
        }
        \RegularLabs\Library\Document::useScript('showon');
        $json = json_encode(JFormHelper::parseShowOnConditions($condition, $formControl, $group));
        return '<div data-showon=\'' . $json . '\' class="hidden ' . $class . '"">';
    }
    public static function show(string $string = '', string $condition = '', string $formControl = '', string $group = '', bool $animate = \true, string $class = ''): string
    {
        if (!$condition || !$string) {
            return $string;
        }
        return self::open($condition, $formControl, $group, $animate, $class) . $string . self::close();
    }
}
