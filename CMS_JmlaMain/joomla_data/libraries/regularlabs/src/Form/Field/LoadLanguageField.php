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
use RegularLabs\Library\Form\FormField as RL_FormField;
use RegularLabs\Library\Language as RL_Language;
class LoadLanguageField extends RL_FormField
{
    protected function getInput()
    {
        $extension = $this->get('extension');
        $admin = (bool) $this->get('admin', 1);
        self::loadLanguage($extension, $admin);
        return '';
    }
    protected function getLabel()
    {
        return '';
    }
    private static function loadLanguage(string $extension, bool $admin = \true): void
    {
        if (!$extension) {
            return;
        }
        RL_Language::load($extension, $admin ? JPATH_ADMINISTRATOR : JPATH_SITE);
    }
}
