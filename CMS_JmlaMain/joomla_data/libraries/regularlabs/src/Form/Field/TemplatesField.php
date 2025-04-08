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
use RegularLabs\Library\DB as RL_DB;
use RegularLabs\Library\Form\FormField as RL_FormField;
class TemplatesField extends RL_FormField
{
    public bool $collapse_children = \true;
    public bool $is_select_list = \true;
    public bool $use_ajax = \true;
    public bool $use_tree_select = \true;
    public function getNamesByIds(array $values, array $attributes): array
    {
        if (empty($values)) {
            return [];
        }
        $query = $this->db->getQuery(\true)->select('e.name, e.element as template')->from('#__extensions as e')->where('e.enabled=1')->where($this->db->quoteName('e.type') . '=' . $this->db->quote('template'))->where(RL_DB::is('e.name', $values))->order('e.name');
        $this->db->setQuery($query);
        $templates = $this->db->loadObjectList();
        $query = $this->db->getQuery(\true)->select('s.title, e.name as template_name, s.template')->from('#__template_styles as s')->join('LEFT', '#__extensions as e on e.element = s.template')->where(RL_DB::is('s.client_id', 0))->where(RL_DB::is('e.enabled', 1))->where(RL_DB::is('e.type', 'template'))->where(RL_DB::in('CONCAT(e.name, "--", s.id)', $values, [], \false))->order('s.template')->order('s.title');
        $this->db->setQuery($query);
        $styles = $this->db->loadObjectList();
        $lang = $this->app->getLanguage();
        $names = [];
        foreach ($templates as $template) {
            $lang->load('tpl_' . $template->template . '.sys', JPATH_SITE) || $lang->load('tpl_' . $template->template . '.sys', JPATH_SITE . '/templates/' . $template->template);
            $names[] = JText::_($template->name);
        }
        foreach ($styles as $style) {
            $lang->load('tpl_' . $style->template . '.sys', JPATH_SITE) || $lang->load('tpl_' . $style->template . '.sys', JPATH_SITE . '/templates/' . $style->template);
            $names[] = '[' . JText::_($style->template_name) . '] ' . JText::_($style->title);
        }
        return $names;
    }
    protected function getOptions()
    {
        $options = [];
        $templates = $this->getTemplates();
        foreach ($templates as $styles) {
            $level = 0;
            foreach ($styles as $style) {
                $style->level = $level;
                $options[] = $style;
                if (count($styles) <= 2) {
                    break;
                }
                $level = 1;
            }
        }
        return $options;
    }
    protected function getTemplates()
    {
        $query = $this->db->getQuery(\true)->select('s.id, s.title, e.name as name, s.template')->from('#__template_styles as s')->where('s.client_id = 0')->join('LEFT', '#__extensions as e on e.element=s.template')->where('e.enabled=1')->where($this->db->quoteName('e.type') . '=' . $this->db->quote('template'))->order('s.template')->order('s.title');
        $this->db->setQuery($query);
        $styles = $this->db->loadObjectList();
        if (empty($styles)) {
            return [];
        }
        $lang = $this->app->getLanguage();
        $groups = [];
        foreach ($styles as $style) {
            $template = $style->template;
            $lang->load('tpl_' . $template . '.sys', JPATH_SITE) || $lang->load('tpl_' . $template . '.sys', JPATH_SITE . '/templates/' . $template);
            $name = JText::_($style->name);
            if (!isset($groups[$template])) {
                $groups[$template] = [];
                $groups[$template][] = JHtml::_('select.option', $template, $name);
            }
            $groups[$template][] = JHtml::_('select.option', $template . '--' . $style->id, $style->title);
        }
        return $groups;
    }
}
