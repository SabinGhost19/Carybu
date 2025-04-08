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
use DateTimeZone;
use Joomla\CMS\Application\CMSApplicationInterface as JCMSApplicationInterface;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Form\Form as JForm;
use Joomla\CMS\Form\FormField as JFormField;
use Joomla\CMS\Form\FormHelper as JFormHelper;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use Joomla\Database\DatabaseDriver as JDatabaseDriver;
use Joomla\Registry\Registry;
use Joomla\Registry\Registry as JRegistry;
use ReflectionClass;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Parameters as RL_Parameters;
use RegularLabs\Library\RegEx as RL_RegEx;
use RegularLabs\Library\StringHelper as RL_String;
use SimpleXMLElement;
use function explode;
use function is_array;
class FormField extends JFormField
{
    public JCMSApplicationInterface $app;
    /* @var object|JRegistry $attributes */
    public $attributes;
    public bool $collapse_children = \false;
    public JDatabaseDriver $db;
    public bool $is_select_list = \false;
    public null|int $max_list_count = null;
    public $params;
    public array $parent_request = [];
    public bool $use_ajax = \false;
    public bool $use_tree_select = \false;
    /**
     * @param JForm $form
     */
    public function __construct($form = null)
    {
        $this->type ??= $this->getShortFieldName();
        parent::__construct($form);
        $this->db = JFactory::getDbo();
        $this->app = JFactory::getApplication();
        $params = RL_Parameters::getPlugin('regularlabs');
        $this->max_list_count = $this->max_list_count ?? $params->max_list_count;
        RL_Document::style('regularlabs.admin-form');
    }
    /**
     * Get a value from the field params
     */
    public function get(string $key, mixed $default = ''): mixed
    {
        $value = $default;
        if (isset($this->params[$key]) && (string) $this->params[$key] != '') {
            $value = (string) $this->params[$key];
        }
        return $this->sanitizeValue($value);
    }
    public function getAjaxRaw(JRegistry|\RegularLabs\Library\Form\FormField $attributes): string
    {
        return $this->selectListForAjax($attributes);
    }
    public function getControlGroupEnd(): string
    {
        return '</div></div>';
    }
    public function getControlGroupStart(): string
    {
        return '<div class="control-group"><div class="control-label">';
    }
    /**
     * Return a list option using the custom prepare methods
     */
    public function getOptionByListItem(object $item, array $extras = [], int $levelOffset = 0, string $key = 'id'): object
    {
        $name = \RegularLabs\Library\Form\Form::getNameWithExtras($item, $extras);
        $option = JHtml::_('select.option', $item->{$key}, $name, 'value', 'text', 0);
        if (isset($item->level)) {
            $option->level = $item->level + $levelOffset;
        }
        return $option;
    }
    /**
     * Return an array of options using the custom prepare methods
     */
    public function getOptionsByList(array $list, array $extras = [], int $levelOffset = 0, string $key = 'id'): array
    {
        $options = [];
        foreach ($list as $id => $item) {
            $options[$id] = $this->getOptionByListItem($item, $extras, $levelOffset, $key);
        }
        return $options;
    }
    /**
     * Method to post-process a field value.
     *
     * @param mixed       $value   The optional value to use as the default for the field.
     * @param string      $group   The optional dot-separated form group path on which to find the field.
     * @param   ?Registry $input   An optional Registry object with the entire data set to filter
     *                             against the entire form.
     *
     * @return  mixed   The processed value.
     */
    public function postProcess($value, $group = null, Registry $input = null)
    {
        if (!$this->multiple) {
            return $value;
        }
        if (!is_array($value)) {
            $value = [$value];
        }
        if (count($value) == 1) {
            $value = explode(',', $value[0]);
        }
        return $value;
    }
    /**
     * Prepare the option string, handling language strings
     */
    public function prepareText(?string $string = ''): string
    {
        $string = trim((string) $string);
        if ($string == '') {
            return '';
        }
        $string = JText::_($string);
        $string = $this->replaceDateTags($string);
        $string = $this->fixLanguageStringSyntax($string);
        return $string;
    }
    public function replaceDateTags(string $string): string
    {
        if (!RL_RegEx::matchAll('\[date:(?<format>.*?)\]', $string, $matches)) {
            return $string;
        }
        $date = JFactory::getDate();
        $tz = new DateTimeZone(JFactory::getApplication()->getCfg('offset'));
        $date->setTimeZone($tz);
        foreach ($matches as $match) {
            $replace = $date->format($match['format'], \true);
            $string = str_replace($match[0], $replace, $string);
        }
        return $string;
    }
    public function sanitizeValue(mixed $value): mixed
    {
        if (is_bool($value) || is_array($value) || is_object($value)) {
            return $value;
        }
        if ($value === 'true') {
            return \true;
        }
        if ($value === 'false') {
            return \false;
        }
        return (string) $value;
    }
    public function selectList(): string
    {
        return $this->selectListFromData($this);
    }
    public function selectListAjax(): string
    {
        $class = $this->get('class', '');
        $multiple = $this->get('multiple', \false);
        $attributes = compact('class', 'multiple');
        if (!empty($this->attributes)) {
            foreach ($this->attributes as $key => $default) {
                $attributes[$key] = $this->get($key, $default);
            }
        }
        if (!empty($this->params)) {
            foreach ($this->params as $key => $value) {
                $attributes[$key] = (string) $value;
            }
        }
        $tree_select = $this->use_tree_select && $multiple;
        return \RegularLabs\Library\Form\Form::selectListAjax($this::class, $this->name, $this->value, $this->id, $attributes, $tree_select, $tree_select && $this->collapse_children);
    }
    public function selectListForAjax(JRegistry|\RegularLabs\Library\Form\FormField $data): string
    {
        return $this->selectListFromData($data);
    }
    public function selectListFromData(JRegistry|\RegularLabs\Library\Form\FormField $data): string
    {
        $data_attributes = $data->get('attributes', []);
        $this->parent_request = (array) $data->get('parent_request', []);
        $name = $this->name ?: $data->get('name', $this->type);
        $id = $this->id ?: $data->get('id', strtolower($name));
        $value = $this->value ?: $data->get('value', []);
        $class = $data->get('class', $data_attributes->class ?? '');
        $multiple = $data->get('multiple', $data_attributes->multiple ?? 0);
        $tree_select = $data->get('treeselect', $this->use_tree_select);
        $collapse_children = $data->get('collapse_children', $this->collapse_children);
        if (!is_array($value)) {
            $value = explode(',', $value);
        }
        $this->value = $value;
        $attributes = compact('class', 'multiple');
        if (!empty($this->attributes)) {
            foreach ($this->attributes as $key => $default) {
                $attributes[$key] = $data->get($key, $this->get($key, $default));
            }
        }
        foreach ($data_attributes as $key => $val) {
            $this->params[$key] = $this->sanitizeValue($val);
            $attributes[$key] = $this->sanitizeValue($val);
        }
        $attributes = array_diff_key($attributes, ['name' => '', 'type' => '']);
        $options = $this->getListOptions($attributes);
        if ($this->get('text_as_value')) {
            $this->setTextAsValue($options);
        }
        return \RegularLabs\Library\Form\Form::selectList($options, $name, $value, $id, $attributes, $tree_select && $multiple, $tree_select && $multiple && $collapse_children, $this->max_list_count);
    }
    /**
     * Method declaration must be compatible with JFormField::setup()
     */
    public function setup(SimpleXMLElement $element, $value, $group = null)
    {
        $this->params = $element->attributes();
        return parent::setup($element, $value, $group);
    }
    /**
     * Method declaration must be compatible with JFormField::getInput()
     */
    protected function getInput()
    {
        if (!$this->is_select_list) {
            return '';
        }
        if (!$this->use_ajax && !$this->use_tree_select) {
            return $this->selectList();
        }
        return $this->selectListAjax();
    }
    /**
     * Method declaration must be compatible with JFormField::getLabel()
     */
    protected function getLabel()
    {
        $this->element['label'] = $this->prepareText($this->element['label']);
        return $this->element['label'] == '---' ? '&nbsp;' : parent::getLabel();
    }
    protected function getListOptions(array $attributes): array|int
    {
        return $this->getOptions();
    }
    /**
     * Return the field options (array)
     * Overrules the Joomla core functionality
     * Method declaration must be compatible with JFormField::getOptions()
     */
    protected function getOptions()
    {
        if (empty($this->element->option)) {
            return [];
        }
        $fieldname = RL_RegEx::replace('[^a-z0-9_\-]', '_', $this->fieldname);
        $options = [];
        foreach ($this->element->option as $option) {
            $value = (string) $option['value'];
            $text = trim((string) $option) != '' ? trim((string) $option) : $value;
            $disabled = (string) $option['disabled'];
            $disabled = $disabled === 'true' || $disabled === 'disabled' || $disabled === '1';
            $disabled = $disabled || $this->readonly && $value != $this->value;
            $checked = (string) $option['checked'];
            $checked = $checked === 'true' || $checked === 'checked' || $checked === '1';
            $selected = (string) $option['selected'];
            $selected = $selected === 'true' || $selected === 'selected' || $selected === '1';
            $attributes = '';
            if ((string) $option['showon']) {
                $encodedConditions = json_encode(JFormHelper::parseShowOnConditions((string) $option['showon'], $this->formControl, $this->group));
                $attributes .= ' data-showon="' . $encodedConditions . '"';
            }
            // Add the option object to the result set.
            $options[] = ['value' => $value, 'text' => '- ' . JText::alt($text, $fieldname) . ' -', 'disable' => $disabled, 'class' => (string) $option['class'], 'selected' => $checked || $selected, 'checked' => $checked || $selected, 'onclick' => (string) $option['onclick'], 'onchange' => (string) $option['onchange'], 'optionattr' => $attributes];
        }
        return $options;
    }
    /**
     * Fix some syntax/encoding issues in option text strings
     */
    private function fixLanguageStringSyntax(string $string = ''): string
    {
        $string = str_replace('[:COMMA:]', ',', $string);
        $string = trim(RL_String::html_entity_decoder($string));
        $string = str_replace('&quot;', '"', $string);
        $string = str_replace('span style="font-family:monospace;"', 'span class="rl_code"', $string);
        return $string;
    }
    /**
     * Get the short name of the field class
     * FoobarField => Foobar
     */
    private function getShortFieldName(): string
    {
        return substr((new ReflectionClass($this))->getShortName(), 0, -strlen('Field'));
    }
    private function setTextAsValue(array &$options): void
    {
        if (empty($options)) {
            return;
        }
        foreach ($options as &$option) {
            $option->value = $option->text;
        }
    }
}
