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

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var   JForm  $tmpl            The Empty form for template
 * @var   array  $forms           Array of JForm instances for render the rows
 * @var   bool   $multiple        The multiple state for the form field
 * @var   int    $min             Count of minimum repeating in multiple mode
 * @var   int    $max             Count of maximum repeating in multiple mode
 * @var   string $name            Name of the input field.
 * @var   string $field           The field
 * @var   string $fieldname       The field name
 * @var   string $fieldId         The field ID
 * @var   string $control         The forms control
 * @var   string $label           The field label
 * @var   string $description     The field description
 * @var   string $class           Classes for the container
 * @var   array  $buttons         Array of the buttons that will be rendered
 * @var   bool   $groupByFieldset Whether group the subform fields by it`s fieldset
 */

// Add script
if ($multiple)
{
    Factory::getDocument()->getWebAssetManager()
        ->useScript('webcomponent.field-subform');
}

$class           = $class ? ' ' . $class : '';
$sublayout       = (empty($groupByFieldset) ? 'section' : 'section-byfieldsets');
$add_button_text = $field->getAttribute('add_button_text');
?>

<div class="subform-repeatable-wrapper subform-layout">
    <joomla-field-subform class="subform-repeatable<?php echo $class; ?>" name="<?php echo $name; ?>"
                          button-add=".group-add" button-remove=".group-remove" button-move="<?php echo empty($buttons['move']) ? '' : '.group-move' ?>"
                          repeatable-element=".subform-repeatable-group" minimum="<?php echo $min; ?>" maximum="<?php echo $max; ?>">
        <?php if ( ! empty($buttons['add'])) : ?>
            <div class="btn-toolbar">
                <div class="btn-group">
                    <button type="button" class="group-add btn btn-sm button btn-success" aria-label="<?php echo Text::_($add_button_text ?: 'JGLOBAL_FIELD_ADD'); ?>">
                        <span class="icon-plus icon-white" aria-hidden="true"></span>
                        <?php echo Text::_($add_button_text ?: ''); ?>
                    </button>
                </div>
            </div>
        <?php endif; ?>
        <?php
        foreach ($forms as $k => $form) :
            echo $this->sublayout($sublayout, [
                'form' => $form, 'basegroup' => $fieldname, 'group' => $fieldname . $k, 'buttons' => $buttons, 'field' => $field,
            ]);
        endforeach;
        ?>
        <?php if ($multiple) : ?>
            <template class="subform-repeatable-template-section hidden"><?php
                echo trim($this->sublayout($sublayout, [
                    'form'  => $tmpl, 'basegroup' => $fieldname, 'group' => $fieldname . 'X', 'buttons' => $buttons,
                    'field' => $field,
                ]));
                ?></template>
        <?php endif; ?>
    </joomla-field-subform>
</div>
