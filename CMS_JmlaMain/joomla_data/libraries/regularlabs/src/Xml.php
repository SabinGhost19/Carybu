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
use SimpleXMLElement;
class Xml
{
    /**
     * Get an object filled with data from an xml file
     */
    public static function toObject(string $url, string $root = ''): object
    {
        $cache = new \RegularLabs\Library\Cache();
        if ($cache->exists()) {
            return $cache->get();
        }
        if (!str_contains($url, '<') && file_exists($url)) {
            $xml = @new SimpleXMLElement($url, \LIBXML_NONET | \LIBXML_NOCDATA, 1);
        } else {
            $xml = simplexml_load_string($url, "SimpleXMLElement", \LIBXML_NONET | \LIBXML_NOCDATA);
        }
        if (!@count($xml)) {
            return $cache->set((object) []);
        }
        if ($root) {
            if (!isset($xml->{$root})) {
                return $cache->set((object) []);
            }
            $xml = $xml->{$root};
        }
        $json = json_encode($xml);
        $xml = json_decode($json);
        if (is_null($xml)) {
            $xml = (object) [];
        }
        if ($root && isset($xml->{$root})) {
            $xml = $xml->{$root};
        }
        return $cache->set($xml);
    }
}
