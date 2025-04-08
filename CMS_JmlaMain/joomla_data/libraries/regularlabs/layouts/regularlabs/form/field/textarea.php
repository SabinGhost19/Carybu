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

use Joomla\CMS\Date\Date as JDate;
use Joomla\CMS\Factory;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Language\Text as JText;

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var   object  $field
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
 * @var   boolean $charcounter    Does this field support a character counter?
 * @var   string  $dataAttribute  Miscellaneous data attributes preprocessed for HTML output
 * @var   array   $dataAttributes Miscellaneous data attribute for eg, data-*.
 * @var   string  $columns
 * @var   string  $rows
 * @var   string  $maxlength
 * @var   boolean $show_insert_date_name
 * @var   boolean $add_separator
 */

// Initialize some field attributes.
if ($charcounter)
{
    // Load the js file
    /** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
    $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
    $wa->useScript('short-and-sweet');

    // Set the css class to be used as the trigger
    $charcounter = ' charcount';
    // Set the text
    $counterlabel = 'data-counter-label="' . $this->escape(Text::_('JFIELD_META_DESCRIPTION_COUNTER')) . '"';
}

$attributes = [
    $columns ?: '',
    $rows ?: '',
    ! empty($class) ? 'class="form-control ' . $class . $charcounter . '"' : 'class="form-control' . $charcounter . '"',
    ! empty($description) ? 'aria-describedby="' . $name . '-desc"' : '',
    strlen($hint) ? 'placeholder="' . htmlspecialchars($hint, ENT_COMPAT, 'UTF-8') . '"' : '',
    $disabled ? 'disabled' : '',
    $readonly ? 'readonly' : '',
    $onchange ? 'onchange="' . $onchange . '"' : '',
    $onclick ? 'onclick="' . $onclick . '"' : '',
    $required ? 'required' : '',
    ! empty($autocomplete) ? 'autocomplete="' . $autocomplete . '"' : '',
    $autofocus ? 'autofocus' : '',
    $spellcheck ? '' : 'spellcheck="false"',
    $maxlength ?: '',
    ! empty($counterlabel) ? $counterlabel : '',
    $dataAttribute,
];

if ($show_insert_date_name)
{
    $user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();

    $date_name = JDate::getInstance()->format('[Y-m-d]') . ' ' . $user->name . ' : ';
    $separator = $add_separator ? '---' : 'none';
    $onclick   = "RegularLabs.TextArea.prependTextarea('" . $id . "', '" . addslashes($date_name) . "', '" . $separator . "');";
}
?>
<?php if ($show_insert_date_name) : ?>
    <span role="button" class="btn btn-sm btn-primary text-bg-primary" onclick="<?php echo $onclick; ?>">
        <span class="icon-pencil" aria-hidden="true"></span>
        <?php echo JText::_('RL_INSERT_DATE_NAME'); ?>
    </span>
<?php endif; ?>

<textarea name="<?php
echo $name; ?>" id="<?php
echo $id; ?>" <?php
echo implode(' ', $attributes); ?> ><?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?></textarea>
