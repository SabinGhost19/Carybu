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
use RegularLabs\Library\DB as RL_DB;
use RegularLabs\Library\Form\FormField as RL_FormField;
class AccessLevelsField extends RL_FormField
{
    static $options;
    public bool $is_select_list = \true;
    public function getNamesByIds(array $values, array $attributes): array
    {
        $query = $this->db->getQuery(\true)->select('a.title')->from('#__viewlevels AS a')->where(RL_DB::is('a.id', $values))->order('a.ordering ASC');
        $this->db->setQuery($query);
        return $this->db->loadColumn();
    }
    protected function getOptions()
    {
        if (!is_null(self::$options)) {
            return self::$options;
        }
        $query = $this->db->getQuery(\true)->select('a.id as value, a.title as text')->from('#__viewlevels AS a')->order('a.ordering ASC');
        $this->db->setQuery($query);
        self::$options = $this->db->loadObjectList();
        return self::$options;
    }
}
