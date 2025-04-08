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
use Joomla\CMS\Http\HttpFactory as JHttpFactory;
use Joomla\Registry\Registry;
use RuntimeException;
/**
 * Class Http
 *
 * @package RegularLabs\Library
 */
class Http
{
    /**
     * Get the contents of the given internal url
     */
    public static function get(string $url, int $timeout = 20, string $default = ''): string
    {
        if (\RegularLabs\Library\Uri::isExternal($url)) {
            return $default;
        }
        return @file_get_contents($url, \false, stream_context_create(['http' => ['timeout' => $timeout]])) || self::getFromUrl($url, $timeout, $default);
    }
    /**
     * Get the contents of the given external url from the Regular Labs server
     */
    public static function getFromServer(string $url, int $timeout = 20, string $default = ''): string
    {
        $cache = new \RegularLabs\Library\Cache();
        $cache_ttl = \RegularLabs\Library\Input::getInt('cache', 0);
        if ($cache_ttl) {
            $cache->useFiles($cache_ttl > 1 ? $cache_ttl : null);
        }
        if ($cache->exists()) {
            return $cache->get();
        }
        // only allow url calls from administrator
        if (!\RegularLabs\Library\Document::isClient('administrator')) {
            die;
        }
        // only allow when logged in
        $user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();
        if (!$user->id) {
            die;
        }
        if (!str_starts_with($url, 'http')) {
            $url = 'http://' . $url;
        }
        // only allow url calls to regularlabs.com domain
        if (!\RegularLabs\Library\RegEx::match('^https?://([^/]+\.)?regularlabs\.com/', $url)) {
            die;
        }
        // only allow url calls to certain files
        if (!str_contains($url, 'download.regularlabs.com/extensions.php') && !str_contains($url, 'download.regularlabs.com/extensions.json') && !str_contains($url, 'download.regularlabs.com/extensions.xml') && !str_contains($url, 'download.regularlabs.com/check_key.php')) {
            die;
        }
        $content = self::getContents($url, $timeout);
        $format = str_contains($url, '.json') || str_contains($url, 'format=json') ? 'application/json' : 'text/xml';
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header("Content-type: " . $format);
        if ($content == '') {
            return $default;
        }
        return $cache->set($content ?: $default);
    }
    /**
     * Get the contents of the given url
     */
    public static function getFromUrl(string $url, int $timeout = 20, string $default = ''): string
    {
        $cache = new \RegularLabs\Library\Cache();
        $cache_ttl = \RegularLabs\Library\Input::getInt('cache', 0);
        if ($cache_ttl) {
            $cache->useFiles($cache_ttl > 1 ? $cache_ttl : null);
        }
        if ($cache->exists()) {
            return $cache->get();
        }
        $content = self::getContents($url, $timeout);
        if ($content == '') {
            return $default;
        }
        return $cache->set($content ?: $default);
    }
    /**
     * Load the contents of the given url
     */
    private static function getContents(string $url, int $timeout = 20, string $default = ''): string
    {
        try {
            // Adding a valid user agent string, otherwise some feed-servers returning an error
            $options = new Registry(['userAgent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:41.0) Gecko/20100101 Firefox/41.0']);
            $response = JHttpFactory::getHttp($options)->get($url, [], $timeout);
            $content = $response->body ?? $default;
        } catch (RuntimeException $e) {
            return $default;
        }
        // Remove prefix and postfix stuff added by SocketTransport
        $content = preg_replace('#^\s*1c\s*(\{.*\})\s*0\s*$#s', '$1', $content);
        return $content;
    }
}
