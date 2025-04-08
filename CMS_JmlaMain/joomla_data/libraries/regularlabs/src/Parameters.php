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
use Joomla\CMS\Component\ComponentHelper as JComponentHelper;
use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;
use RegularLabs\Library\ObjectHelper as RL_Object;
class Parameters
{
    /**
     * Get a usable parameter object for the component
     */
    public static function getComponent(string $name, object|array|string|null $params = null, bool $use_cache = \true): object
    {
        $name = 'com_' . \RegularLabs\Library\RegEx::replace('^com_', '', $name);
        $cache = new \RegularLabs\Library\Cache();
        if ($use_cache && $cache->exists()) {
            return $cache->get();
        }
        if (empty($params) && JComponentHelper::isInstalled($name)) {
            $params = JComponentHelper::getParams($name);
        }
        return $cache->set(self::getObjectFromRegistry($params, JPATH_ADMINISTRATOR . '/components/' . $name . '/config.xml'));
    }
    /**
     * Get a usable parameter object for the module
     */
    public static function getModule(string $name, bool $admin = \true, object|array|string|null $params = null, bool $use_cache = \true): object
    {
        $name = 'mod_' . \RegularLabs\Library\RegEx::replace('^mod_', '', $name);
        $cache = new \RegularLabs\Library\Cache();
        if ($use_cache && $cache->exists()) {
            return $cache->get();
        }
        if (empty($params)) {
            $params = null;
        }
        return $cache->set(self::getObjectFromRegistry($params, ($admin ? JPATH_ADMINISTRATOR : JPATH_SITE) . '/modules/' . $name . '/' . $name . '.xml'));
    }
    /**
     * Get a usable parameter object based on the Joomla Registry object
     * The object will have all the available parameters with their value (default value if none is set)
     */
    public static function getObjectFromRegistry(object|array|string|null $params, string $path = '', string $default = '', bool $use_cache = \true): object
    {
        $cache = new \RegularLabs\Library\Cache();
        if ($use_cache && $cache->exists()) {
            return $cache->get();
        }
        $xml = self::loadXML($path, $default);
        if (empty($params)) {
            return $cache->set((object) $xml);
        }
        if (is_array($params)) {
            $params = (object) $params;
        }
        if (is_string($params)) {
            $params = json_decode($params);
        }
        if (is_object($params) && method_exists($params, 'toObject')) {
            $params = $params->toObject();
        }
        if (is_null($xml)) {
            $xml = (object) [];
        }
        if (!$params) {
            return $cache->set((object) $xml);
        }
        if (empty($xml)) {
            return $cache->set($params);
        }
        foreach ($xml as $key => $val) {
            if (isset($params->{$key}) && $params->{$key} != '') {
                continue;
            }
            $params->{$key} = $val;
        }
        return $cache->set($params);
    }
    /**
     * Get a usable parameter object for the plugin
     */
    public static function getPlugin(string $name, string $type = 'system', object|array|string|null $params = null, bool $use_cache = \true): object
    {
        $cache = new \RegularLabs\Library\Cache();
        if ($use_cache && $cache->exists()) {
            return $cache->get();
        }
        if (empty($params)) {
            $plugin = JPluginHelper::getPlugin($type, $name);
            $params = is_object($plugin) && isset($plugin->params) ? $plugin->params : null;
        }
        return $cache->set(self::getObjectFromRegistry($params, JPATH_PLUGINS . '/' . $type . '/' . $name . '/' . $name . '.xml'));
    }
    /**
     * Returns an array based on the data in a given xml file
     */
    public static function loadXML(string $path, ?string $default = '', bool $use_cache = \true, bool $full_info = \false): array
    {
        $cache = new \RegularLabs\Library\Cache();
        if ($use_cache && $cache->exists()) {
            return $cache->get();
        }
        if (!$path || !file_exists($path)) {
            return $cache->set([]);
        }
        $file = file_get_contents($path);
        if (!$file) {
            return $cache->set([]);
        }
        $xml = [];
        $xml_parser = xml_parser_create();
        xml_parse_into_struct($xml_parser, $file, $fields);
        xml_parser_free($xml_parser);
        $default = $default ? strtoupper($default) : 'DEFAULT';
        foreach ($fields as $field) {
            if ($field['tag'] != 'FIELD' || !isset($field['attributes']) || !isset($field['attributes']['NAME']) || $field['attributes']['NAME'] == '' || $field['attributes']['NAME'][0] == '@' || !isset($field['attributes']['TYPE']) || $field['attributes']['TYPE'] == 'spacer') {
                continue;
            }
            if ($full_info) {
                $full_object = $xml[$field['attributes']['NAME']] = RL_Object::changeKeyCase($field['attributes'], 'lower');
                $full_object->multiple ??= 'false';
            }
            if (isset($field['attributes'][$default])) {
                $field['attributes']['DEFAULT'] = $field['attributes'][$default];
            }
            if (!isset($field['attributes']['DEFAULT'])) {
                $field['attributes']['DEFAULT'] = '';
            }
            if ($field['attributes']['TYPE'] == 'textarea') {
                $field['attributes']['DEFAULT'] = str_replace('<br>', "\n", $field['attributes']['DEFAULT']);
            }
            if ($full_info) {
                $full_object->value = $field['attributes']['DEFAULT'];
                continue;
            }
            $xml[$field['attributes']['NAME']] = $field['attributes']['DEFAULT'];
        }
        return $cache->set($xml);
    }
    public static function overrideFromObject(object $params, ?object $object = null): object
    {
        if (empty($object)) {
            return $params;
        }
        foreach ($params as $key => $value) {
            if (!isset($object->{$key})) {
                continue;
            }
            $params->{$key} = $object->{$key};
        }
        return $params;
    }
}
