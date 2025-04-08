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

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var   JForm  $form      The form instance for render the section
 * @var   JForm  $field     The field
 * @var   string $basegroup The base group name
 * @var   string $group     Current group name
 * @var   array  $buttons   Array of the buttons that will be rendered
 */

$add_button_text = $field->getAttribute('add_button_text');
?>

<div class="subform-repeatable-group" data-base-name="<?php echo $basegroup; ?>" data-group="<?php echo $group; ?>">
    <?php if ( ! empty($buttons)) : ?>
        <div class="btn-toolbar text-end">
            <div class="btn-group">
                <?php if ( ! empty($buttons['add'])): ?>
                    <button type="button" class="group-add btn btn-sm btn-success" aria-label="<?php echo Text::_($add_button_text ?: 'JGLOBAL_FIELD_ADD'); ?>">
                        <span class="icon-plus icon-white" aria-hidden="true"></span>
                        <?php echo Text::_($add_button_text ?: ''); ?>
                    </button>
                <?php endif; ?>
                <?php if ( ! empty($buttons['remove'])): ?>
                    <button type="button" class="group-remove btn btn-sm btn-danger" aria-label="<?php echo Text::_('JGLOBAL_FIELD_REMOVE'); ?>">
                        <span class="icon-minus icon-white" aria-hidden="true"></span>
                    </button>
                <?php endif; ?>
                <?php if ( ! empty($buttons['move'])): ?>
                    <button type="button" class="group-move btn btn-sm btn-primary" aria-label="<?php echo Text::_('JGLOBAL_FIELD_MOVE'); ?>">
                        <span class="icon-arrows-alt icon-white" aria-hidden="true"></span>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    <div class="row">
        <?php foreach ($form->getFieldsets() as $fieldset) : ?>
            <fieldset class="<?php if ( ! empty($fieldset->class))
            {
                echo $fieldset->class;
            } ?>">
                <?php if ( ! empty($fieldset->label)) : ?>
                    <legend><?php echo Text::_($fieldset->label); ?></legend>
                <?php endif; ?>
                <?php foreach ($form->getFieldset($fieldset->name) as $field) : ?>
                    <?php echo $field->renderField(); ?>
                <?php endforeach; ?>
            </fieldset>
        <?php endforeach; ?>
    </div>
</div>
