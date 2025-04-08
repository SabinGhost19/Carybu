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
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Layout\FileLayout as JFileLayout;
use RegularLabs\Library\Form\FormField as RL_FormField;
class SimpleCategoryField extends RL_FormField
{
    protected function getInput()
    {
        $categories = $this->getOptions();
        $options = parent::getOptions();
        $options = [...$options, ...$categories];
        if ($this->get('show_none', \true)) {
            $empty_option = JHtml::_('select.option', $this->get('none_value', ''), '- ' . JText::_('JNONE') . ' -');
            $empty_option->class = 'hidden';
            array_unshift($options, $empty_option);
        }
        if ($this->get('show_keep_original')) {
            $keep_original_option = JHtml::_('select.option', ' ', '- ' . JText::_('RL_KEEP_ORIGINAL_CATEGORY') . ' -');
            array_unshift($options, $keep_original_option);
        }
        $data = $this->getLayoutData();
        $data['options'] = $options;
        $data['placeholder'] = JText::_($this->get('hint', 'RL_SELECT_OR_CREATE_A_CATEGORY'));
        $data['allowCustom'] = $this->get('allow_custom', \true);
        return (new JFileLayout('regularlabs.form.field.simplecategory', JPATH_SITE . '/libraries/regularlabs/layouts'))->render($data);
    }
    protected function getOptions()
    {
        $table = $this->get('table');
        if (!$table) {
            return [];
        }
        // Get the user groups from the database.
        $query = $this->db->getQuery(\true)->select([$this->db->quoteName('category', 'value'), $this->db->quoteName('category', 'text')])->from($this->db->quoteName('#__' . $table))->where($this->db->quoteName('category') . ' != ' . $this->db->quote(''))->group($this->db->quoteName('category'))->order($this->db->quoteName('category') . ' ASC');
        $this->db->setQuery($query);
        $categories = $this->db->loadObjectList();
        foreach ($categories as &$category) {
            if (!str_contains($category->text, '::')) {
                continue;
            }
            [$text, $icon] = explode('::', $category->text, 2);
            $category->text = $text;
        }
        return $categories;
    }
}
