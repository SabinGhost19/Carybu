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
namespace RegularLabs\Library;

defined('_JEXEC') or die;
class Color
{
    public static function save(string $table, int|string $item_id, ?string $color = null, string $id_column = 'id'): bool
    {
        if (empty($color)) {
            return \true;
        }
        if (in_array($color, ['none', 'transparent'])) {
            $color = '';
        }
        $db = \RegularLabs\Library\DB::get();
        $query = $db->getQuery(\true)->select(\RegularLabs\Library\DB::quoteName($id_column))->from(\RegularLabs\Library\DB::quoteName('#__' . $table))->where(\RegularLabs\Library\DB::quoteName($id_column) . ' = ' . $item_id);
        $item_exists = $db->setQuery($query)->loadResult();
        if ($item_exists) {
            $query = $db->getQuery(\true)->update(\RegularLabs\Library\DB::quoteName('#__' . $table))->set(\RegularLabs\Library\DB::quoteName('color') . ' = ' . \RegularLabs\Library\DB::quote($color))->where(\RegularLabs\Library\DB::quoteName($id_column) . ' = ' . $item_id);
            $db->setQuery($query)->execute();
            return \true;
        }
        $query = 'SHOW COLUMNS FROM `#__' . $table . '`';
        $db->setQuery($query);
        $columns = $db->loadColumn();
        $values = array_fill_keys($columns, '');
        $values[$id_column] = $item_id;
        $values['color'] = $color;
        $query = $db->getQuery(\true)->insert(\RegularLabs\Library\DB::quoteName('#__' . $table))->columns(\RegularLabs\Library\DB::quoteName($columns))->values(implode(',', \RegularLabs\Library\DB::quote($values)));
        $db->setQuery($query)->execute();
        return \true;
    }
}
