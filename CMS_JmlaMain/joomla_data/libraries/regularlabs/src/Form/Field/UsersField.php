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
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\DB as RL_DB;
use RegularLabs\Library\Form\Form;
use RegularLabs\Library\Form\FormField as RL_FormField;
class UsersField extends RL_FormField
{
    static $users;
    static $users_count;
    public $attributes = ['show_current' => \false];
    public bool $is_select_list = \true;
    public bool $use_ajax = \true;
    public function getNamesByIds(array $values, array $attributes): array
    {
        $query = $this->db->getQuery(\true)->select('u.name, u.username, u.id, u.block as disabled')->from('#__users AS u')->where(RL_DB::is('u.id', $values))->order('name');
        $this->db->setQuery($query);
        $users = $this->db->loadObjectList();
        if (in_array('current', $values)) {
            array_unshift($users, (object) ['id' => 'current', 'name' => JText::_('RL_CURRENT_USER'), 'add_id' => \false]);
        }
        return Form::getNamesWithExtras($users, ['username', 'id', 'disabled']);
    }
    protected function getListOptions(array $attributes): array|int
    {
        if ($this->max_list_count && $this->getUsersCount() > $this->max_list_count) {
            return -1;
        }
        $users = $this->getUsers();
        $options = $this->getOptionsByList($users, ['username', 'id', 'disabled'], 0, $this->get('username_as_value') ? 'username' : 'id');
        if (!empty($attributes['show_current'])) {
            array_unshift($options, JHtml::_('select.option', 'current', '- ' . JText::_('RL_CURRENT_USER') . ' -'));
        }
        return $options;
    }
    private function getUsers(): array
    {
        if (!is_null(self::$users)) {
            return self::$users;
        }
        $query = $this->db->getQuery(\true)->select('u.name, u.username, u.id, u.block as disabled')->from('#__users AS u')->order('name');
        $this->db->setQuery($query);
        self::$users = $this->db->loadObjectList();
        return self::$users;
    }
    private function getUsersCount(): int
    {
        if (!is_null(self::$users_count)) {
            return self::$users_count;
        }
        $query = $this->db->getQuery(\true)->select('COUNT(*)')->from('#__users AS u');
        $this->db->setQuery($query);
        self::$users_count = $this->db->loadResult();
        return self::$users_count;
    }
}
