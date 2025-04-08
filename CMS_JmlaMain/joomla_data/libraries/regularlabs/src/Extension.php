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
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Helper\ModuleHelper as JModuleHelper;
use Joomla\CMS\Installer\Installer as JInstaller;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;
class Extension
{
    /**
     * Check if all extension types of a given extension are installed
     */
    public static function areInstalled(string $extension, array $types = ['plugin']): bool
    {
        foreach ($types as $type) {
            $folder = 'system';
            if (is_array($type)) {
                [$type, $folder] = $type;
            }
            if (!self::isInstalled($extension, $type, $folder)) {
                return \false;
            }
        }
        return \true;
    }
    public static function disable(string $alias, string $type = 'plugin', string $folder = 'system'): void
    {
        $element = self::getElementByAlias($alias);
        $element = match ($element) {
            'module' => 'mod_' . $element,
            'component' => 'com_' . $element,
            default => $element,
        };
        $db = \RegularLabs\Library\DB::get();
        $query = \RegularLabs\Library\DB::getQuery()->update(\RegularLabs\Library\DB::quoteName('#__extensions'))->set(\RegularLabs\Library\DB::quoteName('enabled') . ' = 0')->where(\RegularLabs\Library\DB::is('element', $element))->where(\RegularLabs\Library\DB::is('type', $type));
        if ($type == 'plugin') {
            $query->where(\RegularLabs\Library\DB::is('folder', $folder));
        }
        $db->setQuery($query);
        $db->execute();
    }
    /**
     * Return an alias and element name based on the given extension name
     */
    public static function getAliasAndElement(string &$name): array
    {
        $name = self::getNameByAlias($name);
        $alias = self::getAliasByName($name);
        $element = self::getElementByAlias($alias);
        return [$alias, $element];
    }
    public static function getAliasByName(string $name): string
    {
        $alias = \RegularLabs\Library\RegEx::replace('[^a-z0-9]', '', strtolower($name));
        return match ($alias) {
            'advancedmodules' => 'advancedmodulemanager',
            'what-nothing' => 'whatnothing',
            default => $alias,
        };
    }
    public static function getById(int|string $id): ?object
    {
        $db = \RegularLabs\Library\DB::get();
        $query = \RegularLabs\Library\DB::getQuery()->select(\RegularLabs\Library\DB::quoteName(['extension_id', 'manifest_cache']))->from(\RegularLabs\Library\DB::quoteName('#__extensions'))->where(\RegularLabs\Library\DB::is('extension_id', (int) $id));
        $db->setQuery($query);
        return $db->loadObject();
    }
    /**
     * Return an element name based on the given extension alias
     */
    public static function getElementByAlias(string $alias): string
    {
        $alias = self::getAliasByName($alias);
        return match ($alias) {
            'advancedmodulemanager' => 'advancedmodules',
            default => $alias,
        };
    }
    public static function getNameByAlias(string $alias): string
    {
        // Alias is a language string
        if (!str_contains($alias, ' ') && strtoupper($alias) == $alias) {
            return JText::_($alias);
        }
        // Alias has a space and/or capitals, so is already a name
        if (str_contains($alias, ' ') || $alias !== strtolower($alias)) {
            return $alias;
        }
        return JText::_(self::getXMLValue('name', $alias));
    }
    /**
     * Get the full path to the extension folder
     */
    public static function getPath(string $extension = 'plg_system_regularlabs', string $basePath = JPATH_ADMINISTRATOR, string $check_folder = ''): string
    {
        $basePath = $basePath ?: JPATH_SITE;
        if (!in_array($basePath, [JPATH_ADMINISTRATOR, JPATH_SITE], \true)) {
            return $basePath;
        }
        $extension = str_replace('.sys', '', $extension);
        switch (\true) {
            case str_starts_with($extension, 'mod_'):
                $path = 'modules/' . $extension;
                break;
            case str_starts_with($extension, 'plg_'):
                [$prefix, $folder, $name] = explode('_', $extension, 3);
                $path = 'plugins/' . $folder . '/' . $name;
                break;
            case str_starts_with($extension, 'com_'):
            default:
                $path = 'components/' . $extension;
                break;
        }
        $check_folder = $check_folder ? '/' . $check_folder : '';
        if (is_dir($basePath . '/' . $path . $check_folder)) {
            return $basePath . '/' . $path;
        }
        if (is_dir(JPATH_ADMINISTRATOR . '/' . $path . $check_folder)) {
            return JPATH_ADMINISTRATOR . '/' . $path;
        }
        if (is_dir(JPATH_SITE . '/' . $path . $check_folder)) {
            return JPATH_SITE . '/' . $path;
        }
        return $basePath;
    }
    /**
     * Return an extensions main xml array
     */
    public static function getXML(string $alias, string $type = '', string $folder = ''): array|false
    {
        $file = self::getXMLFile($alias, $type, $folder);
        if (!$file) {
            return \false;
        }
        return JInstaller::parseXMLInstallFile($file);
    }
    /**
     * Return an extensions main xml file name (including path)
     */
    public static function getXMLFile(string $alias, string $type = '', string $folder = '', bool $get_params = \false): string
    {
        $element = self::getElementByAlias($alias);
        $files = [];
        // Components
        if ($type == '' || $type == 'component') {
            $file = $get_params ? 'config' : $element;
            $files[] = JPATH_ADMINISTRATOR . '/components/com_' . $element . '/' . $file . '.xml';
            $files[] = JPATH_SITE . '/components/com_' . $element . '/' . $file . '.xml';
            if (!$get_params) {
                $files[] = JPATH_ADMINISTRATOR . '/components/com_' . $element . '/com_' . $element . '.xml';
                $files[] = JPATH_SITE . '/components/com_' . $element . '/com_' . $element . '.xml';
            }
        }
        // Plugins
        if ($type == '' || $type == 'plugin') {
            if ($folder != '') {
                $files[] = JPATH_PLUGINS . '/' . $folder . '/' . $element . '/' . $element . '.xml';
            }
            // System Plugins
            $files[] = JPATH_PLUGINS . '/system/' . $element . '/' . $element . '.xml';
            // Editor Button Plugins
            $files[] = JPATH_PLUGINS . '/editors-xtd/' . $element . '/' . $element . '.xml';
            // Field Plugins
            $field_name = \RegularLabs\Library\RegEx::replace('field$', '', $element);
            $files[] = JPATH_PLUGINS . '/fields/' . $field_name . '/' . $field_name . '.xml';
        }
        // Modules
        if ($type == '' || $type == 'module') {
            $files[] = JPATH_ADMINISTRATOR . '/modules/mod_' . $element . '/' . $element . '.xml';
            $files[] = JPATH_SITE . '/modules/mod_' . $element . '/' . $element . '.xml';
            $files[] = JPATH_ADMINISTRATOR . '/modules/mod_' . $element . '/mod_' . $element . '.xml';
            $files[] = JPATH_SITE . '/modules/mod_' . $element . '/mod_' . $element . '.xml';
        }
        foreach ($files as $file) {
            if (!file_exists($file)) {
                continue;
            }
            return $file;
        }
        return '';
    }
    /**
     * Return a value from an extensions main xml file based on the given key
     */
    public static function getXMLValue(string $key, string $alias, string $type = '', string $folder = ''): string
    {
        $xml = self::getXML($alias, $type, $folder);
        if (!$xml) {
            return '';
        }
        if (!isset($xml[$key])) {
            return '';
        }
        return $xml[$key] ?? '';
    }
    public static function isAuthorised(bool $require_core_auth = \true): bool
    {
        $user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();
        if ($user->get('guest')) {
            return \false;
        }
        if (!$require_core_auth) {
            return \true;
        }
        if (!$user->authorise('core.edit', 'com_content') && !$user->authorise('core.edit.own', 'com_content') && !$user->authorise('core.create', 'com_content')) {
            return \false;
        }
        return \true;
    }
    /**
     * Check if the Regular Labs Library is enabled
     */
    public static function isEnabled(string $extension, string $type = 'component', string $folder = 'system'): bool
    {
        $extension = strtolower($extension);
        if (!self::isInstalled($extension, $type, $folder)) {
            return \false;
        }
        switch ($type) {
            case 'component':
                $extension = str_replace('com_', '', $extension);
                return JComponentHelper::isEnabled('com_' . $extension);
            case 'module':
                $extension = str_replace('mod_', '', $extension);
                return JModuleHelper::isEnabled('mod_' . $extension);
            case 'plugin':
                return JPluginHelper::isEnabled($folder, $extension);
            default:
                return \false;
        }
    }
    public static function isEnabledInArea(object $params): bool
    {
        if (!isset($params->enable_frontend)) {
            return \true;
        }
        // Only allow in frontend
        if ($params->enable_frontend == 2 && \RegularLabs\Library\Document::isClient('administrator')) {
            return \false;
        }
        // Do not allow in frontend
        if (!$params->enable_frontend && \RegularLabs\Library\Document::isClient('site')) {
            return \false;
        }
        return \true;
    }
    public static function isEnabledInComponent(object $params): bool
    {
        if (!isset($params->disabled_components)) {
            return \true;
        }
        return !\RegularLabs\Library\Protect::isRestrictedComponent($params->disabled_components);
    }
    /**
     * Check if the Regular Labs Library is enabled
     */
    public static function isFrameworkEnabled(): bool
    {
        return JPluginHelper::isEnabled('system', 'regularlabs');
    }
    public static function isInstalled(string $extension, string $type = 'component', string $folder = 'system'): bool
    {
        $extension = strtolower($extension);
        switch ($type) {
            case 'component':
                $extension = str_replace('com_', '', $extension);
                return file_exists(JPATH_ADMINISTRATOR . '/components/com_' . $extension . '/' . $extension . '.xml') || file_exists(JPATH_SITE . '/components/com_' . $extension . '/' . $extension . '.xml');
            case 'plugin':
                return file_exists(JPATH_PLUGINS . '/' . $folder . '/' . $extension . '/' . $extension . '.php');
            case 'module':
                $extension = str_replace('mod_', '', $extension);
                return file_exists(JPATH_ADMINISTRATOR . '/modules/mod_' . $extension . '/' . $extension . '.php') || file_exists(JPATH_ADMINISTRATOR . '/modules/mod_' . $extension . '/mod_' . $extension . '.php') || file_exists(JPATH_SITE . '/modules/mod_' . $extension . '/' . $extension . '.php') || file_exists(JPATH_SITE . '/modules/mod_' . $extension . '/mod_' . $extension . '.php');
            case 'library':
                $extension = str_replace('lib_', '', $extension);
                return is_dir(JPATH_LIBRARIES . '/' . $extension);
            default:
                return \false;
        }
    }
    public static function orderPluginFirst(string $name, string $folder = 'system'): void
    {
        $db = \RegularLabs\Library\DB::get();
        $query = \RegularLabs\Library\DB::getQuery()->select(['e.ordering'])->from(\RegularLabs\Library\DB::quoteName('#__extensions', 'e'))->where(\RegularLabs\Library\DB::is('e.type', 'plugin'))->where(\RegularLabs\Library\DB::is('e.folder', $folder))->where(\RegularLabs\Library\DB::is('e.element', $name));
        $db->setQuery($query);
        $current_ordering = $db->loadResult();
        if ($current_ordering == '') {
            return;
        }
        $query = \RegularLabs\Library\DB::getQuery()->select('e.ordering')->from(\RegularLabs\Library\DB::quoteName('#__extensions', 'e'))->where(\RegularLabs\Library\DB::is('e.type', 'plugin'))->where(\RegularLabs\Library\DB::is('e.folder', $folder))->where(\RegularLabs\Library\DB::like(\RegularLabs\Library\DB::quoteName('e.manifest_cache'), '%"author":"Regular Labs%'))->where(\RegularLabs\Library\DB::isNot('e.element', $name))->order('e.ordering ASC');
        $db->setQuery($query);
        $min_ordering = $db->loadResult();
        if ($min_ordering == '') {
            return;
        }
        if ($current_ordering < $min_ordering) {
            return;
        }
        if ($min_ordering < 1 || $current_ordering == $min_ordering) {
            $new_ordering = max($min_ordering, 1);
            $query = \RegularLabs\Library\DB::getQuery()->update(\RegularLabs\Library\DB::quoteName('#__extensions'))->set(\RegularLabs\Library\DB::quoteName('ordering') . ' = ' . $new_ordering)->where(\RegularLabs\Library\DB::is('ordering', $min_ordering))->where(\RegularLabs\Library\DB::is('type', 'plugin'))->where(\RegularLabs\Library\DB::is('folder', $folder))->where(\RegularLabs\Library\DB::isNot('element', $name))->where(\RegularLabs\Library\DB::like(\RegularLabs\Library\DB::quoteName('manifest_cache'), '%"author":"Regular Labs%'));
            $db->setQuery($query);
            $db->execute();
            $min_ordering = $new_ordering;
        }
        if ($current_ordering == $min_ordering) {
            return;
        }
        $new_ordering = $min_ordering - 1;
        $query = $db->getQuery(\true)->update(\RegularLabs\Library\DB::quoteName('#__extensions'))->set(\RegularLabs\Library\DB::quoteName('ordering') . ' = ' . $new_ordering)->where(\RegularLabs\Library\DB::is('type', 'plugin'))->where(\RegularLabs\Library\DB::is('folder', $folder))->where(\RegularLabs\Library\DB::is('element', $name));
        $db->setQuery($query);
        $db->execute();
    }
}
