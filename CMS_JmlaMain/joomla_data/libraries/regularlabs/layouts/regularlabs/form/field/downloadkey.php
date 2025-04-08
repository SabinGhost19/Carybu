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

use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Layout\FileLayout as JFileLayout;

defined('_JEXEC') or die;

/**
 * @var   array  $displayData
 * @var   int    $id
 * @var   string $extension
 * @var   int    $cloak_length
 * @var   bool   $use_modal
 * @var   bool   $show_label
 * @var   bool   $hidden
 * @var   string $callback
 */

extract($displayData);

$extension    ??= 'all';
$cloak_length ??= 4;
$use_modal    ??= false;
$hidden       ??= false;
$show_label   ??= false;
$callback     = htmlspecialchars($callback ?? '', ENT_QUOTES, 'UTF-8');
?>
<div id="downloadKeyWrapper_<?php echo $id; ?>" class="rl-download-key" data-callback="<?php echo $callback; ?>">
    <div class="rl-download-key-wrapper mb-4<?php echo $hidden ? ' hidden' : ''; ?>">
        <div class="<?php echo ! $show_label ? ' hidden' : ''; ?>">
            <label for="<?php echo $id; ?>">
                <span class="initialism text-muted">
                    <?php echo JText::_('RL_DOWNLOAD_KEY'); ?>
                </span>
                <span class="rl-popover rl-popover-full">
                    <small class="form-text">
                        <?php echo JText::_('RL_DOWNLOAD_KEY_DESC'); ?>
                    </small>
                </span>
            </label>
        </div>
        <span class="rl-spinner"></span>
        <div class="input-group">
            <input type="text" id="<?php echo $id; ?>" data-key-extension="<?php echo $extension; ?>" data-key-cloak-length="<?php echo $cloak_length; ?>"
                   class="form-control rl-download-key-field form-control inactive rl-code-field hidden">
            <button type="button" class="btn btn-primary button-edit hidden">
                <span class="icon-edit" aria-hidden="true"></span><span class="visually-hidden"><?php echo JText::_('JEDIT'); ?></span>
            </button>
            <button type="button" class="btn btn-success button-apply hidden">
                <span class="icon-checkmark" aria-hidden="true"></span><span class="visually-hidden"><?php echo JText::_('JAPPLY'); ?></span>
            </button>
            <button type="button" class="btn btn-danger button-cancel hidden">
                <span class="icon-times" aria-hidden="true"></span><span class="visually-hidden"><?php echo JText::_('JCANCEL'); ?></span>
            </button>
        </div>

        <?php
        echo (new JFileLayout(
            'regularlabs.form.field.downloadkey_errors',
            JPATH_SITE . '/libraries/regularlabs/layouts'
        ))->render([
            'id'        => $id,
            'extension' => $extension,
        ]);
        ?>
    </div>

    <?php
    if ($use_modal)
    {
        echo (new JFileLayout(
            'regularlabs.form.field.downloadkey_modal',
            JPATH_SITE . '/libraries/regularlabs/layouts'
        ))->render([
            'id'        => $id,
            'extension' => $extension,
        ]);
    }
    ?>
</div>
