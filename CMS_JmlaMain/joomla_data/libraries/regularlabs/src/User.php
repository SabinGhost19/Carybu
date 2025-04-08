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

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\User\User as JUser;
use Joomla\CMS\User\UserFactoryInterface;
defined('_JEXEC') or die;
class User
{
    public static function get(?int $id = null): Juser
    {
        $cache = new \RegularLabs\Library\Cache();
        if ($cache->exists()) {
            return $cache->get();
        }
        $user = $id ? JFactory::getContainer()->get(UserFactoryInterface::class)->loadUserById($id) : JFactory::getApplication()->getIdentity();
        if (!$user) {
            $user = JFactory::getContainer()->get(UserFactoryInterface::class)->loadUserById(0);
        }
        return $cache->set($user);
    }
    public static function getByEmail(string $email): ?JUser
    {
        return self::getByKey('email', $email);
    }
    public static function getById(?int $id = null): ?JUser
    {
        if (!$id) {
            return null;
        }
        $user = static::get($id);
        if ($user->guest) {
            return null;
        }
        return $user;
    }
    public static function getByKey(string $key, string $value): ?JUser
    {
        $id = self::getIdByKey($key, $value);
        if (!$id) {
            return null;
        }
        return self::getById($id);
    }
    public static function getByUsername(string $username): ?JUser
    {
        return self::getByKey('username', $username);
    }
    public static function getEmail(?int $id = null): string
    {
        return (string) self::getValue('email', $id, '');
    }
    public static function getId(?int $id = null): int
    {
        return (int) self::getValue('id', $id, 0);
    }
    public static function getName(?int $id = null): string
    {
        return (string) self::getValue('name', $id, '');
    }
    public static function getUsername(?int $id = null): string
    {
        return (string) self::getValue('username', $id, '');
    }
    public static function getValue(string $key, ?int $id = null, $default = null): mixed
    {
        $user = self::get($id);
        return $user->{$key} ?? $default;
    }
    public static function hasId(int $id): bool
    {
        return self::getId() === $id;
    }
    public static function isAdministrator(?int $id = null): bool
    {
        return self::get($id)->authorise('core.admin') ?? \false;
    }
    public static function isCurrent(int $id): bool
    {
        return self::hasId($id);
    }
    public static function isGuest(?int $id = null): bool
    {
        return (bool) self::getValue('guest', $id, \true);
    }
    private static function getIdByKey(string $key, string $value): int
    {
        $cache = new \RegularLabs\Library\Cache();
        if ($cache->exists()) {
            return $cache->get();
        }
        $db = JFactory::getDbo();
        $query = $db->getQuery(\true)->select($db->quoteName('id'))->from($db->quoteName('#__users'))->where($db->quoteName($key) . ' = :value')->bind(':value', $value)->setLimit(1);
        $db->setQuery($query);
        $id = (int) $db->loadResult();
        return $cache->set($id);
    }
}
