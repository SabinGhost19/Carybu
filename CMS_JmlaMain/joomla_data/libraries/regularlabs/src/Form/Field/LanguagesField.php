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
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use RegularLabs\Library\Form\FormField as RL_FormField;
class LanguagesField extends RL_FormField
{
    public bool $is_select_list = \true;
    public function getNamesByIds(array $values, array $attributes): array
    {
        $languages = JHtml::_('contentlanguage.existing');
        $names = [];
        foreach ($languages as $language) {
            if (empty($language->value)) {
                continue;
            }
            if (!in_array($language->value, $values)) {
                continue;
            }
            $names[] = $language->text . ' [' . $language->value . ']';
        }
        return $names;
    }
    protected function getOptions()
    {
        $languages = JHtml::_('contentlanguage.existing');
        $value = $this->get('value', []);
        if (!is_array($value)) {
            $value = [$value];
        }
        $options = [];
        foreach ($languages as $language) {
            if (empty($language->value)) {
                continue;
            }
            $options[] = (object) ['value' => $language->value, 'text' => $language->text . ' [' . $language->value . ']', 'selected' => in_array($language->value, $value, \true)];
        }
        return $options;
    }
}
