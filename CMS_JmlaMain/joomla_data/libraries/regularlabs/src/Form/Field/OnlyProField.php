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
use RegularLabs\Library\Extension as RL_Extension;
use RegularLabs\Library\Form\FormField as RL_FormField;
class OnlyProField extends RL_FormField
{
    protected function getExtensionName()
    {
        $element = $this->form->getValue('element');
        if ($element) {
            return $element;
        }
        $component = $this->app->input->get('component', '');
        if ($component) {
            return str_replace('com_', '', $component);
        }
        $folder = $this->app->input->get('folder', '');
        if ($folder) {
            $extension = explode('.', $folder);
            return array_pop($extension);
        }
        $option = $this->app->input->get('option', '');
        if ($option) {
            return str_replace('com_', '', $option);
        }
        return \false;
    }
    protected function getInput()
    {
        $label = $this->prepareText($this->get('label'));
        $description = $this->prepareText($this->get('description'));
        if (!$label && !$description) {
            return '';
        }
        return $this->getText();
    }
    protected function getLabel()
    {
        $label = $this->prepareText($this->get('label'));
        $description = $this->prepareText($this->get('description'));
        if (!$label && !$description) {
            return '</div><div>' . $this->getText();
        }
        return parent::getLabel();
    }
    protected function getText()
    {
        $text = JText::_('RL_ONLY_AVAILABLE_IN_PRO');
        $text = '<em>' . $text . '</em>';
        $extension = $this->getExtensionName();
        $alias = RL_Extension::getAliasByName($extension);
        if (!$alias) {
            return $text;
        }
        return '<a href="https://regularlabs.com/' . $extension . '/features" target="_blank">' . $text . '</a>';
    }
}
