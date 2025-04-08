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
use RegularLabs\Library\Extension as RL_Extension;
use RegularLabs\Library\Form\FormField as RL_FormField;
class IsInstalledField extends RL_FormField
{
    protected $layout = 'joomla.form.field.hidden';
    protected function getLabel()
    {
        $this->value = (int) RL_Extension::isInstalled($this->get('extension'), $this->get('extension_type', 'component'), $this->get('folder', 'system'));
        return $this->getControlGroupEnd() . rtrim($this->getRenderer($this->layout)->render($this->getLayoutData()), \PHP_EOL) . $this->getControlGroupStart();
    }
}
