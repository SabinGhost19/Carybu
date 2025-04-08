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
use Joomla\CMS\Form\FormHelper as JFormHelper;
use RegularLabs\Library\Form\FormField as RL_FormField;
class BlockField extends RL_FormField
{
    protected $hiddenDescription = \true;
    protected function getInput()
    {
        if ($this->get('end', 0)) {
            return $this->getControlGroupEnd() . '</fieldset>' . $this->getControlGroupStart();
        }
        $title = $this->get('label');
        $description = $this->get('description');
        $class = $this->get('class');
        $no_default_class = $this->get('no_default_class');
        $html = [];
        $attributes = 'class="' . ($no_default_class ? '' : 'options-form ') . $class . '"';
        if ($this->get('showon')) {
            $encodedConditions = json_encode(JFormHelper::parseShowOnConditions($this->get('showon'), $this->formControl, $this->group));
            $attributes .= " data-showon='" . $encodedConditions . "'";
        }
        $html[] = '<fieldset ' . $attributes . '>';
        if ($title) {
            $html[] = '<legend>' . $this->prepareText($title) . '</legend>';
        }
        if ($description) {
            $html[] = '<div class="form-text mb-3">' . $this->prepareText($description) . '</div>';
        }
        return $this->getControlGroupEnd() . implode('', $html) . $this->getControlGroupStart();
    }
    protected function getLabel()
    {
        return '';
    }
}
