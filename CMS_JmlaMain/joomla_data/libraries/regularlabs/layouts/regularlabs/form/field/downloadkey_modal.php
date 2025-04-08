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

use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Layout\FileLayout as JFileLayout;

defined('_JEXEC') or die;

/**
 * @var   array  $displayData
 * @var   int    $id
 * @var   string $extension
 */

extract($displayData);

$body = (new JFileLayout(
    'regularlabs.form.field.downloadkey_modal_body',
    JPATH_SITE . '/libraries/regularlabs/layouts'
))->render([
    'id'        => $id,
    'extension' => $extension,
]);

$onclick = 'RegularLabs.DownloadKey.save(\'' . $extension . '\', document.querySelector(\'#' . $id . '_modal\').value, document.querySelector(\'#downloadKeyModal_' . $id . '\'));';

echo JHtml::_(
    'bootstrap.renderModal',
    'downloadKeyModal_' . $id,
    [
        'title'      => JText::_('RL_REGULAR_LABS_DOWNLOAD_KEY'),
        'modalWidth' => '60',
        'footer'     => '<button type="button" class="btn btn-success" onclick="' . $onclick . '">'
            . JText::_('JAPPLY')
            . '</button>'
            . '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-hidden="true">'
            . JText::_('JLIB_HTML_BEHAVIOR_CLOSE')
            . '</button>',
    ],
    $body
);
