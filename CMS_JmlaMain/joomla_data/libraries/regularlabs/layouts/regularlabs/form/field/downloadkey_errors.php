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

defined('_JEXEC') or die;

/**
 * @var   array  $displayData
 * @var   int    $id
 * @var   string $extension
 */

extract($displayData);

$extension = $extension ?: 'all';

$extension_name = $extension == 'all'
    ? JText::_('RL_REGULAR_LABS_EXTENSIONS')
    : JText::_(strtoupper($extension));

?>

<div class="key-errors">
    <div class="key-error-empty alert alert-danger hidden">
        <?php echo JText::sprintf('RL_DOWNLOAD_KEY_ERROR_EMPTY', $extension_name, '<a href="https://regularlabs.com/download-keys" target="_blank">', '</a>'); ?>
    </div>
    <div class="key-error-invalid alert alert-danger hidden">
        <?php echo JText::sprintf('RL_DOWNLOAD_KEY_ERROR_INVALID', '<a href="https://regularlabs.com/download-keys" target="_blank">', '</a>'); ?>
    </div>
    <div class="key-error-expired alert alert-danger hidden">
        <?php echo JText::sprintf('RL_DOWNLOAD_KEY_ERROR_EXPIRED', '<a href="https://regularlabs.com/purchase" target="_blank">', '</a>'); ?>
    </div>
    <div class="key-error-local alert alert-danger hidden">
        <?php echo JText::_('RL_DOWNLOAD_KEY_ERROR_LOCAL'); ?>
    </div>
    <div class="key-error-external alert alert-danger hidden">
        <?php echo JText::sprintf('RL_DOWNLOAD_KEY_ERROR_EXTERNAL', '<a href="https://regularlabs.com/forum" target="_blank">', '</a>'); ?>
    </div>
</div>
