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

use Joomla\CMS\Language\Text;

/**
 * @var   array   $displayData
 * @var   string  $autocomplete   Autocomplete attribute for the field.
 * @var   boolean $autofocus      Is autofocus enabled?
 * @var   string  $class          Classes for the input.
 * @var   string  $description    Description of the field.
 * @var   boolean $disabled       Is this field disabled?
 * @var   string  $group          Group the field belongs to. <fields> section in form XML.
 * @var   boolean $hidden         Is this field hidden in the form?
 * @var   string  $hint           Placeholder for the field.
 * @var   string  $id             DOM id of the field.
 * @var   string  $label          Label of the field.
 * @var   string  $labelclass     Classes to apply to the label.
 * @var   boolean $multiple       Does this field support multiple values?
 * @var   string  $name           Name of the input field.
 * @var   string  $onchange       Onchange attribute for the field.
 * @var   string  $onclick        Onclick attribute for the field.
 * @var   string  $pattern        Pattern (Reg Ex) of value of the form field.
 * @var   boolean $readonly       Is this field read only?
 * @var   boolean $repeat         Allows extensions to duplicate elements.
 * @var   boolean $required       Is this field required?
 * @var   integer $size           Size attribute of the input.
 * @var   boolean $spellcheck     Spellcheck state for the form field.
 * @var   string  $validate       Validation rules to apply.
 * @var   string  $value          Value attribute of the field.
 * @var   array   $checkedOptions Options that will be set as checked.
 * @var   boolean $hasValue       Has this field a value assigned?
 * @var   array   $options        Options available for this field.
 * @var   array   $inputType      Options available for this field.
 * @var   string  $accept         File types that are accepted.
 * @var   string  $dataAttribute  Miscellaneous data attributes preprocessed for HTML output
 * @var   array   $dataAttributes Miscellaneous data attribute for eg, data-*.
 * @var   int     $min
 * @var   int     $max
 * @var   string  $prepend
 * @var   string  $append
 * @var   string  $class_range
 */

extract($displayData);

$value = is_numeric($value) ? (float) $value : $min;

$class       = $class ?: 'rl-w-6em font-monospace text-right';
$class_range = $class_range ?: 'rl-w-16em';

// Initialize some field attributes.
$attributes_range = [
    $class ? 'class="' . $class_range . '"' : '',
    $disabled ? 'disabled' : '',
    $readonly ? 'readonly' : '',
    ! empty($onchange) ? 'onchange="' . $onchange . '"' : '',
    ! empty($max) ? 'max="' . $max . '"' : '',
    ! empty($step) ? 'step="' . $step . '"' : '',
    ! empty($min) ? 'min="' . $min . '"' : '',
    $autofocus ? 'autofocus' : '',
    'oninput="document.querySelector(\'input[name=\\\'' . $name . '\\\']\').value=this.value;document.querySelector(\'input[name=\\\'' . $name . '\\\']\').dispatchEvent(new Event(\'change\'));"',
];

$attributes_number = [
    $class ? 'class="form-control ' . $class . '"' : 'class="form-control"',
    ! empty($description) ? 'aria-describedby="' . $name . '-desc"' : '',
    $disabled ? 'disabled' : '',
    $readonly ? 'readonly' : '',
    strlen($hint) ? 'placeholder="' . htmlspecialchars($hint, ENT_COMPAT, 'UTF-8') . '"' : '',
    ! empty($onchange) ? 'onchange="' . $onchange . '"' : '',
    isset($max) ? 'max="' . $max . '"' : '',
    ! empty($step) ? 'step="' . $step . '"' : '',
    isset($min) ? 'min="' . $min . '"' : '',
    $required ? 'required aria-required="true"' : '',
    $autocomplete,
    $autofocus ? 'autofocus' : '',
    $dataAttribute,
    'oninput="document.querySelector(\'input[data-for=\\\'' . $name . '\\\']\').value=this.value;"',
];

$chars = (strlen($max) ?: $size) ?: 4;
$width = $chars * 8;

$classes = [];

if ($prepend)
{
    $classes[] = 'input-prepend';
}

if ($append)
{
    $classes[] = 'input-append';
}

if (str_starts_with($prepend, 'icon-'))
{
    $prepend = '<span class="' . $prepend . '"></span>';
}

if (str_starts_with($append, 'icon-'))
{
    $append = '<span class="' . $append . '"></span>';
}

if ($prepend && preg_match('#^[A-Z][A-Z0-9_]+$#', $prepend))
{
    $prepend = Text::_($prepend);
}

if ($append && preg_match('#^[A-Z][A-Z0-9_]+$#', $append))
{
    $append = Text::_($append);
}
?>
<div class="rl-flex">

    <span class="rl-mr-1 <?php echo implode(' ', $classes); ?>">
        <?php if ($prepend): ?>
            <span class="add-on"><?php echo $prepend; ?></span>
        <?php endif; ?>

        <input type="number" inputmode="numeric" name="<?php echo $name; ?>" id="<?php echo $id; ?>"
               value="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>"
            <?php echo implode(' ', $attributes_number); ?>>

        <?php if ($append): ?>
            <span class="add-on"><?php echo $append; ?></span>
        <?php endif; ?>
    </span>

    <input type="range" data-for="<?php echo $name; ?>" value="<?php echo $value; ?>"
        <?php echo implode(' ', $attributes_range); ?> />

</div>
