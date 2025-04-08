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
class HeaderLibraryField extends \RegularLabs\Library\Form\Field\HeaderField
{
    protected function getInput()
    {
        $extensions = [
            'Advanced Module Manager',
            'Articles Anywhere',
            'Articles Field',
            'Better Frontend Link',
            'Cache Cleaner',
            'CDN for Joomla!',
            'Conditional Content',
            //            'Content Templater',
            'DB Replacer',
            'GeoIP',
            'IP Login',
            //            'Keyboard Shortcuts',
            //            'Modals',
            'Modules Anywhere',
            'Quick Index',
            'Regular Labs Extension Manager',
            'ReReplacer',
            'Snippets',
            'Sourcerer',
            //            'Tabs & Accordions',
            //            'Tooltips',
            'What? Nothing!',
        ];
        $list = '<ul><li>' . implode('</li><li>', $extensions) . '</li></ul>';
        $attributes = $this->element->attributes();
        $warning = '';
        if (isset($attributes['warning'])) {
            $warning = '<div class="alert alert-danger">' . JText::_($attributes['warning']) . '</div>';
        }
        $this->element->attributes()['description'] = JText::sprintf($attributes['description'], $warning, $list);
        return parent::getInput();
    }
}
