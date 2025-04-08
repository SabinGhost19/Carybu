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
use Joomla\CMS\Router\Route as JRoute;
use Joomla\CMS\Uri\Uri as JUri;
class Uri
{
    /**
     * Adds the given url parameter (key + value) to the url or replaces it already exists
     */
    public static function addParameter(string $url, string $key, string $value = '', bool $replace = \true): string
    {
        if ($key == '') {
            return $url;
        }
        $uri = parse_url($url);
        $query = self::parse_query($uri['query'] ?? '');
        if (!$replace && isset($query[$key])) {
            return $url;
        }
        $query[$key] = $value;
        $uri['query'] = http_build_query($query);
        return self::createUrlFromArray($uri);
    }
    /**
     * Appends the given hash to the url or replaces it if there is already one
     */
    public static function appendHash(string $url = '', string $hash = ''): string
    {
        if ($hash == '') {
            return $url;
        }
        $uri = parse_url($url);
        $uri['fragment'] = $hash;
        return self::createUrlFromArray($uri);
    }
    /**
     * Converts an array of url parts (like made by parse_url) to a string
     */
    public static function createUrlFromArray(array $uri): string
    {
        $user = $uri['user'] ?? '';
        $pass = !empty($uri['pass']) ? ':' . $uri['pass'] : '';
        return (!empty($uri['scheme']) ? $uri['scheme'] . '://' : '') . ($user || $pass ? $user . $pass . '@' : '') . (!empty($uri['host']) ? $uri['host'] : '') . (!empty($uri['port']) ? ':' . $uri['port'] : '') . (!empty($uri['path']) ? $uri['path'] : '') . (!empty($uri['query']) ? '?' . $uri['query'] : '') . (!empty($uri['fragment']) ? '#' . $uri['fragment'] : '');
    }
    public static function decode(string $string, bool $urldecode = \true): string
    {
        if ($urldecode) {
            $string = urldecode($string);
        }
        $string = base64_decode($string);
        $deflated = @gzinflate($string);
        if ($string === $deflated || !$deflated) {
            return $string;
        }
        return $deflated;
    }
    public static function encode(string $string, bool $urlencode = \true): string
    {
        $string = base64_encode(gzdeflate($string));
        if ($urlencode) {
            $string = urlencode($string);
        }
        return $string;
    }
    /**
     * Returns the full uri and optionally adds/replaces the hash
     */
    public static function get(string $hash = ''): string
    {
        $url = JUri::getInstance()->toString();
        if ($hash == '') {
            return $url;
        }
        return self::appendHash($url, $hash);
    }
    public static function getCompressedAttributes(): string
    {
        $compressed = '';
        for ($i = 0; $i < 10; $i++) {
            $compressed .= \RegularLabs\Library\Input::getString('rlatt_' . $i, '');
        }
        return self::decode($compressed, \false);
    }
    /**
     * Get the value of a given url parameter from the url
     */
    public static function getParameter(string $url, string $key): mixed
    {
        if (empty($key)) {
            return '';
        }
        $uri = parse_url($url);
        if (!isset($uri['query'])) {
            return '';
        }
        $query = self::parse_query($uri['query']);
        return $query[$key] ?? '';
    }
    /**
     * Get all url parameters from the url
     */
    public static function getParameters(string $url): object
    {
        $uri = parse_url($url);
        if (!isset($uri['query'])) {
            return (object) [];
        }
        $query = self::parse_query($uri['query']);
        return (object) $query;
    }
    public static function isExternal(string $url): bool
    {
        if (!str_contains($url, '://')) {
            return \false;
        }
        // hostname: give preference to SERVER_NAME, because this includes subdomains
        $hostname = $_SERVER['SERVER_NAME'] ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST'];
        return !str_starts_with(\RegularLabs\Library\RegEx::replace('^.*?://', '', $url), $hostname);
    }
    /**
     * removes the given url parameter from the url
     */
    public static function removeParameter(string $url, string $key): string
    {
        if (empty($key)) {
            return $url;
        }
        $uri = parse_url($url);
        if (!isset($uri['query'])) {
            return $url;
        }
        $query = self::parse_query($uri['query']);
        unset($query[$key]);
        $uri['query'] = http_build_query($query);
        return self::createUrlFromArray($uri);
    }
    public static function route(string $url): string
    {
        return JRoute::_(JUri::root(\true) . '/' . $url);
    }
    /**
     * Parse a query string into an associative array.
     */
    private static function parse_query(string $string): array
    {
        $result = [];
        if ($string === '') {
            return $result;
        }
        $decoder = fn($value) => rawurldecode(str_replace('+', ' ', $value));
        foreach (explode('&', $string) as $kvp) {
            $parts = explode('=', $kvp, 2);
            $key = $decoder($parts[0]);
            $value = isset($parts[1]) ? $decoder($parts[1]) : null;
            if (!isset($result[$key])) {
                $result[$key] = $value;
                continue;
            }
            if (!is_array($result[$key])) {
                $result[$key] = [$result[$key]];
            }
            $result[$key][] = $value;
        }
        return $result;
    }
}
