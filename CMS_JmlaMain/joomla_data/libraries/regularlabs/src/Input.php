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
/**
 * string   getAlphaNumeric($name, $default = null)     Get an alphanumeric string.
 * string   getBase64($name, $default = null)           Get a base64 encoded string.
 * boolean  getBool($name, $default = null)             Get a boolean value.
 * string   getCmd($name, $default = null)              Get a CMD filtered string.
 * float    getFloat($name, $default = null)            Get a floating-point number.
 * string   getHtml($name, $default = null)             Get a HTML string.
 * integer  getInt($name, $default = null)              Get a signed integer.
 * string   getPath($name, $default = null)             Get a file path.
 * mixed    getRaw($name, $default = null)              Get an unfiltered value.
 * string   getString($name, $default = null)           Get a string.
 * integer  getUint($name, $default = null)             Get an unsigned integer.
 * string   getUsername($name, $default = null)         Get a username.
 * string   getWord($name, $default = null)             Get a word.
 */
class Input
{
    public static function get(string $name, mixed $default = null, string $filter = 'none'): mixed
    {
        return JFactory::getApplication()->getInput()->get($name, $default, $filter);
    }
    public static function getAlphaNumeric(string $name, mixed $default = null): string
    {
        return (string) (self::convertFromArray($name) ?? JFactory::getApplication()->getInput()->getAlnum($name, $default));
    }
    public static function getArray(array $vars = [], mixed $datasource = null): array
    {
        return JFactory::getApplication()->getInput()->getArray($vars, $datasource);
    }
    public static function getAsArray(string $name, ?array $default = []): array
    {
        return (array) JFactory::getApplication()->getInput()->get($name, $default ?? [], 'array');
    }
    public static function getBase64(string $name, mixed $default = null): string
    {
        return (string) (self::convertFromArray($name) ?? JFactory::getApplication()->getInput()->getBase64($name, $default));
    }
    public static function getBool(string $name, ?bool $default = \false): bool
    {
        return (bool) (self::convertFromArray($name) ?? JFactory::getApplication()->getInput()->getBool($name, $default));
    }
    public static function getCmd(string $name, mixed $default = null): string
    {
        return (string) (self::convertFromArray($name) ?? JFactory::getApplication()->getInput()->getCmd($name, $default));
    }
    public static function getFloat(string $name, mixed $default = null): float
    {
        return (float) (self::convertFromArray($name) ?? JFactory::getApplication()->getInput()->getFloat($name, $default));
    }
    public static function getHtml(string $name, mixed $default = null): string
    {
        return (string) (self::convertFromArray($name) ?? JFactory::getApplication()->getInput()->getHtml($name, $default));
    }
    public static function getInt(string $name, mixed $default = null): int
    {
        return (int) (self::convertFromArray($name) ?? JFactory::getApplication()->getInput()->getInt($name, $default));
    }
    public static function getPath(string $name, mixed $default = null): string
    {
        return (string) (self::convertFromArray($name) ?? JFactory::getApplication()->getInput()->getPath($name, $default));
    }
    public static function getRaw(string $name, mixed $default = null): mixed
    {
        return JFactory::getApplication()->getInput()->getRaw($name, $default);
    }
    public static function getString(string $name, mixed $default = null): string
    {
        return (string) (self::convertFromArray($name) ?? JFactory::getApplication()->getInput()->getString($name, $default));
    }
    public static function getUint(string $name, mixed $default = null): int
    {
        return (int) (self::convertFromArray($name) ?? JFactory::getApplication()->getInput()->getUint($name, $default));
    }
    public static function getUsername(string $name, mixed $default = null): string
    {
        return (string) (self::convertFromArray($name) ?? JFactory::getApplication()->getInput()->getUsername($name, $default));
    }
    public static function getWord(string $name, mixed $default = null): string
    {
        return (string) (self::convertFromArray($name) ?? JFactory::getApplication()->getInput()->getWord($name, $default));
    }
    public static function set(string $name, mixed $value): void
    {
        JFactory::getApplication()->getInput()->set($name, $value);
    }
    public static function setCookie(string $name, mixed $value, array $options = []): void
    {
        JFactory::getApplication()->getInput()->cookie->set($name, $value, $options);
    }
    private static function convertFromArray(string $name): mixed
    {
        $value = JFactory::getApplication()->getInput()->get($name, null);
        if (is_array($value)) {
            return $value[0] ?? null;
        }
        return null;
    }
}
