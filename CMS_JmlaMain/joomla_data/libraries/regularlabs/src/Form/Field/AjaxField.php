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
class AjaxField extends RL_FormField
{
    protected function getInput()
    {
        $class = $this->get('class', 'btn btn-success');
        if ($this->get('disabled')) {
            return $this->getButton($class . ' disabled', 'disabled');
        }
        RL_Document::script('regularlabs.admin-form');
        RL_Document::script('regularlabs.regular');
        RL_Document::script('regularlabs.script');
        $query = '';
        $url_query = $this->get('url-query');
        if ($url_query) {
            $name_prefix = $this->form->getFormControl() . '\\\\[' . $this->group . '\\\\]';
            $id_prefix = $this->form->getFormControl() . '_' . $this->group . '_';
            $query_parts = [];
            $url_query = explode(',', $url_query);
            foreach ($url_query as $url_query_part) {
                [$key, $id] = explode(':', $url_query_part);
                $el_name = 'document.querySelector(`input[name=' . $name_prefix . '\\\\[' . $id . '\\\\]]:checked`)';
                $el_id = 'document.querySelector(`#' . $id_prefix . $id . '`)';
                $query_parts[] = '`&' . $key . '=`' . ' + encodeURI(' . $el_name . ' ? ' . $el_name . '.value : (' . $el_id . ' ? ' . $el_id . '.value' . ' : ``))';
            }
            $query = '+' . implode('+', $query_parts);
        }
        $url = '`' . addslashes($this->get('url')) . '`' . $query;
        $attributes = 'onclick="RegularLabs.AdminForm.loadAjaxButton(`' . $this->id . '`, ' . $url . ')"';
        return $this->getButton($class, $attributes);
    }
    private function getButton(string $class = 'btn btn-success', string $attributes_string = ''): string
    {
        $icon = $this->get('icon', '') ? 'icon-' . $this->get('icon', '') : '';
        $attributes_string = $attributes_string ? ' ' . $attributes_string : '';
        return '<button type="button" id="' . $this->id . '" class="' . $class . '"' . ' title="' . JText::_($this->get('description')) . '"' . $attributes_string . '>' . '<span class="' . $icon . '"></span> ' . '<span>' . JText::_($this->get('text', $this->get('label'))) . '</span>' . '</button>' . '<div id="message_' . $this->id . '"></div>';
    }
}
