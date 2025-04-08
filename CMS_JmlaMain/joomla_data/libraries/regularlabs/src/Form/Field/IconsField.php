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
namespace RegularLabs\Library\Form\Field;

defined('_JEXEC') or die;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\Form\FormField as RL_FormField;
class IconsField extends RL_FormField
{
    protected $layout = 'joomla.form.field.radio.buttons';
    protected function getInput()
    {
        $data = $this->getLayoutData();
        return $this->getRenderer($this->layout)->render($data);
    }
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();
        $extraData = ['options' => $this->getOptions(), 'value' => (string) $this->value, 'class' => 'btn-group rl-btn-group rl-btn-group-separate rl-btn-group-min-size'];
        return [...$data, ...$extraData];
    }
    protected function getOptions()
    {
        $classes = ['address-book', 'address-card', 'align-center', 'align-justify', 'align-left', 'align-right', 'angle-double-left', 'angle-double-right', 'angle-down', 'angle-left', 'angle-right', 'angle-up', 'archive', 'arrow-alt-circle-down', 'arrow-alt-circle-left', 'arrow-alt-circle-right', 'arrow-alt-circle-up', 'arrow-down', 'arrow-left', 'arrow-right', 'arrow-up', 'arrows-alt', 'bars', 'bell', 'bolt', 'bookmark', 'briefcase', 'bullhorn', 'calendar-alt', 'calendar-check', 'camera', 'caret-down', 'caret-left', 'caret-right', 'caret-up', 'chart-area', 'chart-bar', 'chart-pie', 'check-square', 'plus-square', 'minus-square', 'check-circle', 'plus-circle', 'minus-circle', 'times-circle', 'play-circle', 'pause-circle', 'stop-circle', 'chevron-circle-left', 'chevron-circle-right', 'backward', 'forward', 'step-backward', 'fast-backward', 'fast-forward', 'square', 'chevron-down', 'chevron-left', 'chevron-right', 'chevron-up', 'circle', 'clipboard', 'clock', 'cloud-download-alt', 'cloud-upload-alt', 'code-branch', 'cogs', 'comment-dots', 'comments', 'compress', 'copy', 'credit-card', 'crop', 'cubes', 'cut', 'database', 'desktop', 'tablet', 'mobile', 'dot-circle', 'download', 'upload', 'edit', 'pen-square', 'pencil-alt', 'ellipsis-h', 'ellipsis-v', 'envelope-open-text', 'exclamation-circle', 'exclamation-triangle', 'info-circle', 'question-circle', 'expand-arrows-alt', 'external-link-alt', 'external-link-square-alt', 'eye-slash', 'fax', 'file-alt', 'filter', 'flag', 'folder-open', 'handshake', 'home', 'image', 'key', 'lock-open', 'unlock-alt', 'language', 'life-ring', 'lightbulb', 'link', 'list-ol', 'list-ul', 'tasks', 'magic', 'compass', 'globe', 'map-marker-alt', 'thumbtack', 'map-signs', 'medkit', 'music', 'paint-brush', 'paperclip', 'phone-square', 'plug', 'power-off', 'print', 'project-diagram', 'puzzle-piece', 'quote-left', 'quote-right', 'random', 'rss-square', 'save', 'search-minus', 'search-plus', 'shield-alt', 'shopping-basket', 'shopping-cart', 'sign-in-alt', 'sign-out-alt', 'sitemap', 'sliders-h', 'smile', 'frown', 'thumbs-down', 'thumbs-up', 'heart', 'star', 'star-half', 'trophy', 'tachometer-alt', 'tags', 'text-width', 'th-large', 'toggle-off', 'toggle-on', 'trash', 'share', 'sync', 'undo', 'universal-access', 'user-circle', 'user-edit', 'user-lock', 'user-tag', 'users-cog', 'video', 'wifi', 'wrench'];
        $options = [];
        foreach ($classes as $class) {
            $options[] = (object) ['value' => $class, 'text' => '<i class="fa fa-' . $class . '"></i>'];
        }
        if ($this->get('show_none')) {
            $options[] = (object) ['value' => '0', 'text' => JText::_('JNONE')];
        }
        return $options;
    }
}
