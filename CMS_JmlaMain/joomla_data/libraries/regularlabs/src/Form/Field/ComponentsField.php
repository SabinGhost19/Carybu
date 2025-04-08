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
use RegularLabs\Library\RegEx as RL_RegEx;
class ComponentsField extends RL_FormField
{
    static $components;
    public $attributes = ['frontend' => \true, 'admin' => \true];
    public bool $is_select_list = \true;
    public bool $use_ajax = \true;
    public function getNamesByIds(array $values, array $attributes): array
    {
        $query = $this->db->getQuery(\true)->select('e.name, e.element')->from('#__extensions AS e')->where('e.type = ' . $this->db->quote('component'))->where(RL_DB::is('e.element', $values))->order('e.name');
        $this->db->setQuery($query);
        $components = $this->db->loadObjectList();
        $lang = $this->app->getLanguage();
        $names = [];
        foreach ($components as $component) {
            $name = $component->name;
            if (!str_contains($component->name, ' ')) {
                // Load the core file then
                // Load extension-local file.
                $lang->load($component->element . '.sys', JPATH_BASE, null, \false, \false) || $lang->load($component->element . '.sys', JPATH_ADMINISTRATOR . '/components/' . $component->element, null, \false, \false) || $lang->load($component->element . '.sys', JPATH_BASE, $lang->getDefault(), \false, \false) || $lang->load($component->element . '.sys', JPATH_ADMINISTRATOR . '/components/' . $component->element, $lang->getDefault(), \false, \false);
                $name = JText::_(strtoupper($name));
            }
            $names[] = $name;
        }
        return $names;
    }
    protected function getListOptions(array $attributes): array|int
    {
        $frontend = $attributes['frontend'];
        $admin = $attributes['admin'];
        if (!$frontend && !$admin) {
            return [];
        }
        $components = $this->getComponents();
        $comps = [];
        $lang = $this->app->getLanguage();
        foreach ($components as $component) {
            if (empty($component->element)) {
                continue;
            }
            $component_folder = ($frontend ? JPATH_SITE : JPATH_ADMINISTRATOR) . '/components/' . $component->element;
            if (!is_dir($component_folder) && $admin) {
                $component_folder = JPATH_ADMINISTRATOR . '/components/' . $component->element;
            }
            // return if there is no main component folder
            if (!is_dir($component_folder)) {
                continue;
            }
            // return if there is no view(s) folder
            if ($component->element !== 'com_ajax' && !is_dir($component_folder . '/src/View') && !is_dir($component_folder . '/views') && !is_dir($component_folder . '/view')) {
                continue;
            }
            if (!str_contains($component->name, ' ')) {
                // Load the core file then
                // Load extension-local file.
                $lang->load($component->element . '.sys', JPATH_BASE, null, \false, \false) || $lang->load($component->element . '.sys', JPATH_ADMINISTRATOR . '/components/' . $component->element, null, \false, \false) || $lang->load($component->element . '.sys', JPATH_BASE, $lang->getDefault(), \false, \false) || $lang->load($component->element . '.sys', JPATH_ADMINISTRATOR . '/components/' . $component->element, $lang->getDefault(), \false, \false);
                $component->name = JText::_(strtoupper($component->name));
            }
            $comps[RL_RegEx::replace('[^a-z0-9_]', '', $component->name . '_' . $component->element)] = $component;
        }
        ksort($comps);
        $options = [];
        foreach ($comps as $component) {
            $key = $component->element;
            if ($this->get('no_com_prefix')) {
                $key = RL_RegEx::replace('^com_', '', $key);
            }
            $options[] = JHtml::_('select.option', $key, $component->name);
        }
        return $options;
    }
    private function getComponents(): array
    {
        if (!is_null(self::$components)) {
            return self::$components;
        }
        $query = $this->db->getQuery(\true)->select('e.name, e.element')->from('#__extensions AS e')->where('e.type = ' . $this->db->quote('component'))->where('e.name != ""')->where('e.element != ""')->group('e.element')->order('e.element, e.name');
        $this->db->setQuery($query);
        self::$components = $this->db->loadObjectList();
        return self::$components;
    }
}
