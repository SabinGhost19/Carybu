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

use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Object\CMSObject as JCMSObject;
use Joomla\CMS\Uri\Uri as JUri;

/**
 * @var   JCMSObject $displayData
 */

if ( ! function_exists('RegularLabsModalRenderButton'))
{
    function RegularLabsModalRenderButton($options, $prefix = 'close', $default_text = 'JLIB_HTML_BEHAVIOR_CLOSE')
    {
        $class   = $options[$prefix . 'Class'] ?? ($prefix == 'close' ? 'btn btn-secondary' : 'btn btn-success');
        $text    = $options[$prefix . 'Text'] ?? JText::_($default_text);
        $onclick = $options[$prefix . 'Callback'] ?? '';
        $dismiss = $prefix == 'close' || ! empty($options[$prefix . 'Close']) ? 'data-bs-dismiss="modal"' : '';
        $icon    = ! empty($options[$prefix . 'Icon']) ? '<span class="icon-' . $options[$prefix . 'Icon'] . '" aria-hidden="true"></span>' : '';

        return '<button type="button" class="' . $class . '" ' . $dismiss . ' onclick="' . $onclick . '">'
            . $icon . $text . ' </button>';
    }
}

$button = $displayData;

if ( ! $button->get('modal'))
{
    return;
}

if ( ! $button->get('name'))
{
    return;
}

$link    = ($button->get('link')) ? JUri::base() . $button->get('link') : null;
$title   = ($button->get('title')) ? $button->get('title') : $button->get('text');
$options = is_array($button->get('options')) ? $button->get('options') : [];

$buttons = [];

if (isset($options['confirmCallback'])
)
{
    $buttons[] = RegularLabsModalRenderButton($options, 'confirm', 'JSAVE');
}

if (isset($options['confirm2Callback'])
)
{
    $buttons[] = RegularLabsModalRenderButton($options, 'confirm2', 'JSAVE');
}

$buttons[] = RegularLabsModalRenderButton($options, 'close', 'JLIB_HTML_BEHAVIOR_CLOSE');

$id = str_replace(' ', '', $button->get('id', strtolower($button->get('name')) . '_modal'));

echo JHtml::_(
    'bootstrap.renderModal',
    $id,
    [
        'url'        => $link,
        'title'      => $title,
        'height'     => array_key_exists('height', $options) ? $options['height'] : '400px',
        'width'      => array_key_exists('width', $options) ? $options['width'] : '800px',
        'bodyHeight' => array_key_exists('bodyHeight', $options) ? $options['bodyHeight'] : '70',
        'modalWidth' => array_key_exists('modalWidth', $options) ? $options['modalWidth'] : '80',
        'keyboard'   => array_key_exists('keyboard', $options) ? $options['keyboard'] : true,
        'backdrop'   => array_key_exists('backdrop', $options) ? $options['backdrop'] : true,
        'footer'     => implode('', $buttons),
    ]
);
