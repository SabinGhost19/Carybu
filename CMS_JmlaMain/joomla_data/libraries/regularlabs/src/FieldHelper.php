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
class FieldHelper
{
    private static $articles_field_names = null;
    public static function correctFieldValue(int|string $field_name, mixed &$field_value): void
    {
        if (is_array($field_value) && (count($field_value) > 1 || !isset($field_value[0]))) {
            foreach ($field_value as $key => &$value) {
                self::correctFieldValue($key, $value);
            }
            return;
        }
        if (!in_array($field_name, self::getArticlesFieldNames())) {
            return;
        }
        $field_value = (array) $field_value;
        if (count($field_value) == 1 && str_contains($field_value[0], ',')) {
            $field_value = explode(',', $field_value[0]);
        }
    }
    private static function getArticlesFieldNames(): array
    {
        if (!is_null(self::$articles_field_names)) {
            return self::$articles_field_names;
        }
        self::$articles_field_names = [];
        $db = \RegularLabs\Library\DB::get();
        $query = \RegularLabs\Library\DB::getQuery()->select([\RegularLabs\Library\DB::quoteName('f.name'), \RegularLabs\Library\DB::quoteName('f.id')])->from(\RegularLabs\Library\DB::quoteName('#__fields', 'f'))->where(\RegularLabs\Library\DB::quoteName('f.type') . ' = ' . $db->quote('articles'));
        $db->setQuery($query);
        $fields = $db->loadAssocList();
        foreach ($fields as $field) {
            self::$articles_field_names[] = 'field' . $field['id'];
            self::$articles_field_names[] = $field['name'];
        }
        return self::$articles_field_names;
    }
}
