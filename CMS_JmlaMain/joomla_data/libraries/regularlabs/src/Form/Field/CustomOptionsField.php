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
use Joomla\CMS\Layout\FileLayout as JFileLayout;
use RegularLabs\Library\ArrayHelper as RL_Array;
use RegularLabs\Library\Form\FormField as RL_FormField;
class CustomOptionsField extends RL_FormField
{
    protected function getInput()
    {
        $data = $this->getLayoutData();
        $data['options'] = $this->getOptions();
        $data['value'] = RL_Array::toArray($this->value);
        $data['placeholder'] = JText::_('RL_ENTER_NEW_VALUES');
        return (new JFileLayout('regularlabs.form.field.customoptions', JPATH_SITE . '/libraries/regularlabs/layouts'))->render($data);
    }
    protected function getOptions()
    {
        $values = RL_Array::toArray($this->value);
        $options = [];
        foreach ($values as $value) {
            $options[] = (object) ['value' => $value, 'text' => $value];
        }
        return $options;
    }
}
