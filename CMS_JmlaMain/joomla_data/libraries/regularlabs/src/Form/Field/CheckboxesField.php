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
use Joomla\CMS\Form\Field\CheckboxesField as JCheckboxesField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text as JText;
use SimpleXMLElement;
use UnexpectedValueException;
use function count;
class CheckboxesField extends JCheckboxesField
{
    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     */
    protected $layout = 'regularlabs.form.field.checkboxes';
    protected function getLayoutPaths()
    {
        $paths = parent::getLayoutPaths();
        $paths[] = JPATH_LIBRARIES . '/regularlabs/layouts';
        return $paths;
    }
    protected function getOptions()
    {
        $groups = $this->getGroups();
        return self::flattenGroups($groups);
    }
    private static function flattenGroups(array $groups): array
    {
        $options = [];
        foreach ($groups as $group_name => $group) {
            if ($group_name !== 0) {
                $options[] = $group_name;
            }
            foreach ($group as $option) {
                $options[] = $option;
            }
        }
        return $options;
    }
    private function getGroups(): array
    {
        $fieldname = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname);
        $groups = [];
        $label = 0;
        foreach ($this->element->children() as $element) {
            switch ($element->getName()) {
                // The element is an <option />
                case 'option':
                    if (!isset($groups[$label])) {
                        $groups[$label] = [];
                    }
                    $groups[$label][] = $this->getOption($element, $fieldname);
                    break;
                // The element is a <group />
                case 'group':
                    // Get the group label.
                    $groupLabel = (string) $element['label'];
                    if ($groupLabel) {
                        $label = JText::_($groupLabel);
                    }
                    // Initialize the group if necessary.
                    if (!isset($groups[$label])) {
                        $groups[$label] = [];
                    }
                    // Iterate through the children and build an array of options.
                    foreach ($element->children() as $option) {
                        // Only add <option /> elements.
                        if ($option->getName() !== 'option') {
                            continue;
                        }
                        $groups[$label][] = $this->getOption($option, $fieldname);
                    }
                    if ($groupLabel) {
                        $label = count($groups);
                    }
                    break;
                // Unknown element type.
                default:
                    throw new UnexpectedValueException(sprintf('Unsupported element %s in GroupedlistField', $element->getName()), 500);
            }
        }
        reset($groups);
        return $groups;
    }
    private function getOption(SimpleXMLElement $option, string $fieldname): object
    {
        $value = (string) $option['value'];
        $text = trim((string) $option) != '' ? trim((string) $option) : $value;
        $disabled = (string) $option['disabled'];
        $disabled = $disabled === 'true' || $disabled === 'disabled' || $disabled === '1';
        $disabled = $disabled || $this->readonly && $value != $this->value;
        $checked = (string) $option['checked'];
        $checked = $checked === 'true' || $checked === 'checked' || $checked === '1';
        $selected = (string) $option['selected'];
        $selected = $selected === 'true' || $selected === 'selected' || $selected === '1';
        $tmp = ['value' => $value, 'text' => JText::alt($text, $fieldname), 'disable' => $disabled, 'class' => (string) $option['class'], 'selected' => $checked || $selected, 'checked' => $checked || $selected];
        // Set some event handler attributes. But really, should be using unobtrusive js.
        $tmp['onclick'] = (string) $option['onclick'];
        $tmp['onchange'] = (string) $option['onchange'];
        if ((string) $option['showon']) {
            $encodedConditions = json_encode(FormHelper::parseShowOnConditions((string) $option['showon'], $this->formControl, $this->group));
            $tmp['optionattr'] = " data-showon='" . $encodedConditions . "'";
        }
        return (object) $tmp;
    }
}
