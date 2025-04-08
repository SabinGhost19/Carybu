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

use Joomla\CMS\Object\CMSObject as JCMSObject;

/**
 * @var   JCMSObject $displayData
 */

$button = $displayData;

if ( ! $button->get('name'))
{
    return;
}

$is_modal = $button->get('modal');

$class   = 'btn';
$class   .= $button->get('class') ? ' ' . $button->get('class') : ' btn-secondary';
$class   .= $is_modal ? ' modal-button' : null;
$onclick = $button->get('onclick') ? ' onclick="' . str_replace('"', '&quot;', $button->get('onclick')) . '"' : '';
$title   = $button->get('title') ? $button->get('title') : $button->get('text');
$icon    = $button->get('icon') ? $button->get('icon') : $button->get('name');

$href = $is_modal
    ? 'data-bs-target="#' . strtolower($button->get('name')) . '_modal"'
    : 'href="' . $button->get('link', '#') . '"';
?>
<button type="button" <?php echo $href; ?> class="<?php echo $class; ?>" <?php echo $button->get('modal') ? 'data-bs-toggle="modal"' : '' ?> title="<?php echo $title; ?>" <?php echo $onclick; ?>>
    <span class="icon-<?php echo $icon; ?>" aria-hidden="true"></span>
    <?php echo $button->get('text'); ?>
</button>
