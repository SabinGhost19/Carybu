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
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Form\FormField as RL_FormField;
use RegularLabs\Library\Version;
class JCompatibilityField extends RL_FormField
{
    protected function getInput()
    {
        $extension = $this->get('extension');
        if (empty($extension)) {
            return '';
        }
        $jversion = Version::getMajorJoomlaVersion();
        if ($jversion == 4) {
            return '';
        }
        RL_Document::useStyle('webcomponent.joomla-alert');
        RL_Document::useScript('webcomponent.joomla-alert');
        return '<joomla-alert type="danger" dismiss="true" class="joomla-alert--show" role="alert">' . JText::sprintf('RL_NOT_COMPATIBLE_WITH_JOOMLA_VERSION', JText::_($extension), $jversion) . '</joomla-alert>';
    }
    protected function getLabel()
    {
        return '';
    }
}
