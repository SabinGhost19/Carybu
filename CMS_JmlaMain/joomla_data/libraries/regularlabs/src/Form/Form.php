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
namespace RegularLabs\Library\Form;

defined('_JEXEC') or die;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Layout\FileLayout as JFileLayout;
use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Input as RL_Input;
use RegularLabs\Library\Parameters as RL_Parameters;
use RegularLabs\Library\RegEx as RL_RegEx;
class Form
{
    public static function getLayout(array $options, bool $treeselect = \false): string
    {
        if ($treeselect) {
            return 'regularlabs.form.field.treeselect';
        }
        if (is_array(reset($options))) {
            return 'joomla.form.field.groupedlist-fancy-select';
        }
        return 'joomla.form.field.list-fancy-select';
    }
    /**
     * Return a name with added extras and formatting
     */
    public static function getNameWithExtras(object $item, array $extras = []): string
    {
        $name = trim($item->name);
        foreach ($extras as $extra) {
            if ($extra == 'language' && $item->{$extra} == '*') {
                continue;
            }
            if (in_array($extra, ['id', 'alias'], \true) && $item->{$extra} == $item->name) {
                continue;
            }
            if ($extra == 'unpublished') {
                $name .= isset($item->published) && !$item->published ? ' (' . JText::_('JUNPUBLISHED') . ')' : '';
                continue;
            }
            if ($extra == 'disabled') {
                $name .= isset($item->disabled) && $item->disabled ? ' (' . JText::_('JDISABLED') . ')' : '';
                continue;
            }
            if (empty($item->{$extra})) {
                continue;
            }
            if (isset($item->{'add_' . $extra}) && !$item->{'add_' . $extra}) {
                continue;
            }
            $name .= ' [' . $item->{$extra} . ']';
        }
        return self::prepareSelectItem($name);
    }
    /**
     * Return an array with names with added extras and formatting
     */
    public static function getNamesWithExtras(array $items, array $extras = []): array
    {
        $names = [];
        foreach ($items as $item) {
            $names[] = self::getNameWithExtras($item, $extras);
        }
        return $names;
    }
    public static function prepareSelectItem(string $string, int $remove_first = 0): string
    {
        if (empty($string)) {
            return '';
        }
        $string = str_replace(['&nbsp;', '&#160;'], ' ', $string);
        $string = RL_RegEx::replace('^(- )+', '  ', $string);
        for ($i = 0; $remove_first > $i; $i++) {
            $string = RL_RegEx::replace('^  ', '', $string, '');
        }
        if (RL_RegEx::match('^( *)(.*)$', $string, $match, '')) {
            [$string, $pre, $name] = $match;
            $pre = str_replace('  ', ' ·  ', $pre);
            $pre = RL_RegEx::replace('(( ·  )*) ·  ', '\1 »  ', $pre);
            $pre = str_replace('  ', ' &nbsp; ', $pre);
            $string = $pre . $name;
        }
        return $string;
    }
    /**
     * Render a full select list
     */
    public static function selectList(int|array $options, string $name, mixed $value, string $id, array $attributes = [], bool $treeselect = \false, bool $collapse_children = \false, null|int $max_list_count = null): string
    {
        if (empty($options)) {
            return '<fieldset class="radio">' . JText::_('RL_NO_ITEMS_FOUND') . '</fieldset>';
        }
        $params = RL_Parameters::getPlugin('regularlabs');
        $max_list_count = $max_list_count ?? $params->max_list_count;
        if (!is_array($value)) {
            $value = explode(',', $value);
        }
        if (count($value) === 1 && str_contains($value[0], ',')) {
            $value = explode(',', $value[0]);
        }
        $count = 0;
        if ($max_list_count && $options != -1) {
            foreach ($options as $option) {
                $count++;
                if (isset($option->links)) {
                    $count += count($option->links);
                }
                if ($count > $params->max_list_count) {
                    break;
                }
            }
        }
        if ($options == -1 || $max_list_count && $count > $max_list_count) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            if (!$value) {
                $input = '<textarea name="' . $name . '" id="' . $id . '" cols="40" rows="5">' . $value . '</textarea>';
            } else {
                $input = '<input type="text" name="' . $name . '" id="' . $id . '" value="' . $value . '" size="60">';
            }
            $plugin = JPluginHelper::getPlugin('system', 'regularlabs');
            $url = !empty($plugin->id) ? 'index.php?option=com_plugins&task=plugin.edit&extension_id=' . $plugin->id : 'index.php?option=com_plugins&&filter[folder]=&filter[search]=Regular%20Labs%20Library';
            $label = JText::_('RL_ITEM_IDS');
            $text = JText::_('RL_MAX_LIST_COUNT_INCREASE');
            $tooltip = JText::_('RL_MAX_LIST_COUNT_INCREASE_DESC,' . $max_list_count . ',RL_MAX_LIST_COUNT');
            $link = '<a href="' . $url . '" target="_blank" id="' . $id . '_msg"' . ' class="hasPopover" title="' . $text . '" data-content="' . htmlentities($tooltip) . '">' . '<span class="icon icon-cog"></span>' . $text . '</a>';
            $script = 'jQuery("#' . $id . '_msg").popover({"html": true,"trigger": "hover focus","container": "body"})';
            return '<fieldset class="radio">' . '<label for="' . $id . '">' . $label . ':</label>' . $input . '<br><small>' . $link . '</small>' . '</fieldset>' . '<script>' . $script . '</script>';
        }
        $layout = self::getLayout($options, $treeselect);
        $path = $treeselect ? JPATH_SITE . '/libraries/regularlabs/layouts' : null;
        $data = [...compact('id', 'name', 'value', 'options'), 'multiple' => \false, 'autofocus' => \false, 'onchange' => '', 'dataAttribute' => '', 'readonly' => \false, 'disabled' => '', 'hint' => \false, 'required' => \false, 'collapse_children' => $collapse_children, 'groups' => $options, ...$attributes];
        $renderer = new JFileLayout($layout, $path);
        return $renderer->render($data);
    }
    /**
     * Render a select list loaded via Ajax
     */
    public static function selectListAjax(string $field_class, string $name, mixed $value, string $id, array $attributes = [], bool $treeselect = \false, bool $collapse_children = \false): string
    {
        RL_Document::style('regularlabs.admin-form');
        RL_Document::script('regularlabs.admin-form');
        RL_Document::script('regularlabs.regular');
        RL_Document::script('regularlabs.script');
        if ($treeselect) {
            RL_Document::script('regularlabs.treeselect');
            RL_Document::useScript('bootstrap.dropdown');
        } else {
            RL_Document::usePreset('choicesjs');
            RL_Document::useScript('webcomponent.field-fancy-select');
        }
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        $ajax_data = ['parent_request' => ['option' => RL_Input::getCmd('option'), 'view' => RL_Input::getCmd('view'), 'id' => RL_Input::getInt('id')], 'field_class' => $field_class, 'value' => $value, 'attributes' => $attributes, 'treeselect' => $treeselect, 'collapse_children' => $collapse_children];
        return '<div class="rl-ajax-wrapper">' . '<textarea name="' . $name . '" id="' . $id . '" cols="40" rows="1" class="form-control rl-ajax-field"' . ' data-rl-ajax="' . htmlspecialchars(json_encode($ajax_data)) . '">' . $value . '</textarea>' . '<div class="rl-spinner"></div>' . '</div>';
    }
}
