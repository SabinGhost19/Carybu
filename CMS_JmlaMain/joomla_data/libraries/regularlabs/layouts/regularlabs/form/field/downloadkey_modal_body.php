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
 */

extract($displayData);

$extension = $extension ?: 'all';

?>

<div class="content p-3">
    <?php
    echo (new JFileLayout(
        'regularlabs.form.field.downloadkey_errors',
        JPATH_SITE . '/libraries/regularlabs/layouts'
    ))->render([
        'id'        => $id,
        'extension' => $extension,
    ]);
    ?>
    <p>
        <?php echo html_entity_decode(JText::_('RL_DOWNLOAD_KEY_ENTER')); ?>:
    </p>
    <div class="input-group">
        <input type="text" id="<?php echo $id; ?>_modal" placeholder="ABC123..." class="rl-download-key-field form-control rl-code-field">
    </div>
</div>
