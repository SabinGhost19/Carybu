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
namespace RegularLabs\Library\Form\Field;

defined('_JEXEC') or die;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\Form\FormField as RL_FormField;
use RegularLabs\Library\GeoIp\GeoIp as RL_GeoIP;
class GeoInformationField extends RL_FormField
{
    protected function getInput()
    {
        return '';
    }
    protected function getLabel()
    {
        if (!class_exists('RegularLabs\Library\GeoIp\GeoIp')) {
            return '';
        }
        $ip = '';
        $geo = new RL_GeoIP($ip);
        if (empty($geo)) {
            return \false;
        }
        $geo = $geo->get();
        if (empty($geo)) {
            return \false;
        }
        $details = [JText::_('CON_CONTINENT') . ': <strong>' . $geo->continent . '</strong>', JText::_('CON_COUNTRY') . ':  <strong>' . $geo->country . '</strong>', JText::_('CON_REGION') . ':  <strong>' . implode(', ', $geo->regions) . '</strong>', JText::_('CON_POSTAL_CODE') . ':  <strong>' . $geo->postalCode . '</strong>'];
        $html = '<div class="rl-alert alert alert-info rl-alert-light">' . JText::_('CON_GEO_CURRENT_DETAILS') . '<ul><li>' . implode('</li><li>', $details) . '</li></ul>' . '</div>';
        return '</div><div>' . $html;
    }
}
