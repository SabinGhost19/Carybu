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
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Layout\FileLayout as JFileLayout;
class DownloadKey
{
    public static function get(bool $update = \true): string
    {
        $db = \RegularLabs\Library\DB::get();
        $query = \RegularLabs\Library\DB::getQuery()->select('extra_query')->from('#__update_sites')->where(\RegularLabs\Library\DB::like(\RegularLabs\Library\DB::quoteName('extra_query'), 'k=%'))->where(\RegularLabs\Library\DB::like(\RegularLabs\Library\DB::quoteName('location'), '%download.regularlabs.com%'));
        $db->setQuery($query);
        $key = $db->loadResult();
        if (!$key) {
            return '';
        }
        \RegularLabs\Library\RegEx::match('#k=([a-zA-Z0-9]{8}[A-Z0-9]{8})#', $key, $match);
        if (!$match[1]) {
            return '';
        }
        $key = $match[1];
        if ($update) {
            self::store($key);
        }
        return $key;
    }
    public static function getOutputForComponent(string $extension = 'all', bool $use_modal = \true, bool $hidden = \true, string $callback = ''): string
    {
        $id = 'downloadkey_' . strtolower($extension);
        \RegularLabs\Library\Document::script('regularlabs.script');
        \RegularLabs\Library\Document::script('regularlabs.downloadkey');
        return (new JFileLayout('regularlabs.form.field.downloadkey', JPATH_SITE . '/libraries/regularlabs/layouts'))->render(['id' => $id, 'extension' => strtolower($extension), 'use_modal' => $use_modal, 'hidden' => $hidden, 'callback' => $callback, 'show_label' => \true]);
    }
    public static function isValid(string $key, string $extension = 'all'): string
    {
        $key = trim($key);
        if (!self::isValidFormat($key)) {
            return json_encode(['valid' => \false, 'active' => \false]);
        }
        $cache = new \RegularLabs\Library\Cache();
        $cache->useFiles(1);
        if ($cache->exists()) {
            return $cache->get();
        }
        $result = \RegularLabs\Library\Http::getFromUrl('https://download.regularlabs.com/check_key.php?k=' . $key . '&e=' . $extension);
        return $cache->set($result);
    }
    public static function isValidFormat(string $key): bool
    {
        $key = trim($key);
        if ($key === '') {
            return \true;
        }
        if (strlen($key) != 16) {
            return \false;
        }
        return \RegularLabs\Library\RegEx::match('^[a-zA-Z0-9]{8}[A-Z0-9]{8}$', $key, $match, 's');
    }
    public static function store(string $key): bool
    {
        if (!self::isValidFormat($key)) {
            return \false;
        }
        $query = \RegularLabs\Library\DB::getQuery()->update('#__update_sites')->set(\RegularLabs\Library\DB::is('extra_query', ''))->where(\RegularLabs\Library\DB::like(\RegularLabs\Library\DB::quoteName('location'), '%download.regularlabs.com%'));
        \RegularLabs\Library\DB::get()->setQuery($query)->execute();
        $extra_query = $key ? 'k=' . $key : '';
        $query = \RegularLabs\Library\DB::getQuery()->update('#__update_sites')->set(\RegularLabs\Library\DB::is('extra_query', $extra_query))->where(\RegularLabs\Library\DB::like(\RegularLabs\Library\DB::quoteName('location'), '%download.regularlabs.com%'))->where(\RegularLabs\Library\DB::combine([\RegularLabs\Library\DB::like(\RegularLabs\Library\DB::quoteName('location'), '%&pro=%'), \RegularLabs\Library\DB::like(\RegularLabs\Library\DB::quoteName('location'), '%e=extensionmanager%')], 'OR'));
        $result = \RegularLabs\Library\DB::get()->setQuery($query)->execute();
        JFactory::getCache()->clean('_system');
        return $result;
    }
}
