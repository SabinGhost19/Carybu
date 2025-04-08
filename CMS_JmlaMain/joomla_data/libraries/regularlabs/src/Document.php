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
use Joomla\CMS\Document\Document as JDocument;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\WebAsset\WebAssetManager as JWebAssetManager;
class Document
{
    public static function adminError(string $message): void
    {
        self::adminMessage($message, 'error');
    }
    public static function adminMessage(string $message, string $type = 'message'): void
    {
        if (!self::isAdmin()) {
            return;
        }
        self::message($message, $type);
    }
    public static function error(string $message): void
    {
        self::message($message, 'error');
    }
    public static function get(): ?JDocument
    {
        $app = JFactory::getApplication();
        if (!method_exists($app, 'getDocument')) {
            return null;
        }
        $document = JFactory::getApplication()->getDocument();
        if (!is_null($document)) {
            return $document;
        }
        JFactory::getApplication()->loadDocument();
        return JFactory::getApplication()->getDocument();
    }
    public static function getAssetManager(): ?JWebAssetManager
    {
        $document = self::get();
        if (is_null($document)) {
            return null;
        }
        return $document->getWebAssetManager();
    }
    public static function getComponentBuffer(): ?string
    {
        $buffer = self::get()->getBuffer('component') ?? null;
        if (empty($buffer) || !is_string($buffer)) {
            return null;
        }
        $buffer = trim($buffer);
        if (empty($buffer)) {
            return null;
        }
        return $buffer;
    }
    public static function isAdmin(bool $exclude_login = \false): bool
    {
        $cache = new \RegularLabs\Library\Cache();
        if ($cache->exists()) {
            return $cache->get();
        }
        $user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();
        $is_admin = self::isClient('administrator') && (!$exclude_login || !$user->get('guest')) && \RegularLabs\Library\Input::get('task', '') != 'preview' && !(\RegularLabs\Library\Input::get('option', '') == 'com_finder' && \RegularLabs\Library\Input::get('format', '') == 'json');
        return $cache->set($is_admin);
    }
    public static function isCategoryList(string $context): bool
    {
        $cache = new \RegularLabs\Library\Cache();
        if ($cache->exists()) {
            return $cache->get();
        }
        // Return false if it is not a category page
        if ($context != 'com_content.category' || \RegularLabs\Library\Input::get('view', '') != 'category') {
            return $cache->set(\false);
        }
        // Return false if layout is set and it is not a list layout
        if (\RegularLabs\Library\Input::get('layout', '') && \RegularLabs\Library\Input::get('layout', '') != 'list') {
            return $cache->set(\false);
        }
        // Return false if default layout is set to blog
        if (JFactory::getApplication()->getParams()->get('category_layout') == '_:blog') {
            return $cache->set(\false);
        }
        // Return true if it IS a list layout
        return $cache->set(\true);
    }
    public static function isCli(): bool
    {
        $cache = new \RegularLabs\Library\Cache();
        if ($cache->exists()) {
            return $cache->get();
        }
        $is_cli = (new \RegularLabs\Library\MobileDetect())->isCurl();
        return $cache->set($is_cli);
    }
    public static function isClient(string $identifier): bool
    {
        $identifier = $identifier == 'admin' ? 'administrator' : $identifier;
        $cache = new \RegularLabs\Library\Cache();
        if ($cache->exists()) {
            return $cache->get();
        }
        return $cache->set(JFactory::getApplication()->isClient($identifier));
    }
    public static function isDebug(): bool
    {
        return JFactory::getApplication()->get('debug') || \RegularLabs\Library\Input::get('debug');
    }
    public static function isEditPage(): bool
    {
        $cache = new \RegularLabs\Library\Cache();
        if ($cache->exists()) {
            return $cache->get();
        }
        $option = \RegularLabs\Library\Input::get('option', '');
        // always return false for these components
        if (in_array($option, ['com_rsevents', 'com_rseventspro'], \true)) {
            return $cache->set(\false);
        }
        $task = \RegularLabs\Library\Input::get('task', '');
        if (str_contains($task, '.')) {
            $task = explode('.', $task);
            $task = array_pop($task);
        }
        $view = \RegularLabs\Library\Input::get('view', '');
        if (str_contains($view, '.')) {
            $view = explode('.', $view);
            $view = array_pop($view);
        }
        $is_edit_page = in_array($option, ['com_config', 'com_contentsubmit', 'com_cckjseblod'], \true) || $option == 'com_comprofiler' && in_array($task, ['', 'userdetails'], \true) || in_array($task, ['edit', 'form', 'submission'], \true) || in_array($view, ['edit', 'form'], \true) || in_array(\RegularLabs\Library\Input::get('do', ''), ['edit', 'form'], \true) || in_array(\RegularLabs\Library\Input::get('layout', ''), ['edit', 'form', 'write'], \true) || self::isAdmin();
        return $cache->set($is_edit_page);
    }
    public static function isFeed(): bool
    {
        $cache = new \RegularLabs\Library\Cache();
        if ($cache->exists()) {
            return $cache->get();
        }
        $is_feed = self::get() && ((self::get()->getType() ?? null) == 'feed' || in_array(\RegularLabs\Library\Input::getWord('format'), ['feed', 'xml'], \true) || in_array(\RegularLabs\Library\Input::getWord('type'), ['rss', 'atom'], \true));
        return $cache->set($is_feed);
    }
    public static function isHtml(): bool
    {
        $cache = new \RegularLabs\Library\Cache();
        if ($cache->exists()) {
            return $cache->get();
        }
        $is_html = self::get() ? self::get()->getType() == 'html' : \false;
        return $cache->set($is_html);
    }
    public static function isHttps(): bool
    {
        $cache = new \RegularLabs\Library\Cache();
        if ($cache->exists()) {
            return $cache->get();
        }
        $is_https = !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off' || isset($_SERVER['SSL_PROTOCOL']) || isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443 || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https';
        return $cache->set($is_https);
    }
    public static function isJSON(): bool
    {
        $cache = new \RegularLabs\Library\Cache();
        if ($cache->exists()) {
            return $cache->get();
        }
        $is_json = \RegularLabs\Library\Input::get('format', '') == 'json';
        return $cache->set($is_json);
    }
    /**
     * Check if the current setup matches the given main version number
     */
    public static function isJoomlaVersion(int $version, string $title = ''): bool
    {
        $jversion = \RegularLabs\Library\Version::getMajorJoomlaVersion();
        if ($jversion == $version) {
            return \true;
        }
        if ($title && self::isAdmin()) {
            \RegularLabs\Library\Language::load('plg_system_regularlabs');
            JFactory::getApplication()->enqueueMessage(JText::sprintf('RL_NOT_COMPATIBLE_WITH_JOOMLA_VERSION', JText::_($title), $jversion), 'error');
        }
        return \false;
    }
    public static function isPDF(): bool
    {
        $cache = new \RegularLabs\Library\Cache();
        if ($cache->exists()) {
            return $cache->get();
        }
        $is_pdf = self::get() && ((self::get()->getType() ?? null) == 'pdf' || \RegularLabs\Library\Input::getWord('format') == 'pdf' || \RegularLabs\Library\Input::getWord('cAction') == 'pdf');
        return $cache->set($is_pdf);
    }
    public static function message(string $message, string $type = 'message'): void
    {
        \RegularLabs\Library\Language::load('plg_system_regularlabs');
        JFactory::getApplication()->enqueueMessage($message, $type);
    }
    /**
     * @depecated Use RegularLabs\Library\StringHelper::minify()
     */
    public static function minify(string $string): string
    {
        return \RegularLabs\Library\StringHelper::minify($string);
    }
    public static function removeScriptTag(string &$string, string $folder, string $name): void
    {
        $regex_name = \RegularLabs\Library\RegEx::quote($name);
        $regex_name = str_replace('\*', '[^"]*', $regex_name);
        $string = \RegularLabs\Library\RegEx::replace('\s*<script [^>]*href="[^"]*(' . $folder . '/js|js/' . $folder . ')/' . $regex_name . '\.[^>]*( /)?>', '', $string);
    }
    public static function removeScriptsOptions(string &$string, string $name, string $alias = ''): void
    {
        \RegularLabs\Library\RegEx::match('(<script type="application/json" class="joomla-script-options new">)(.*?)(</script>)', $string, $match);
        if (empty($match)) {
            return;
        }
        $alias = $alias ?: \RegularLabs\Library\Extension::getAliasByName($name);
        $scripts = json_decode($match[2]);
        if (!isset($scripts->{'rl_' . $alias})) {
            return;
        }
        unset($scripts->{'rl_' . $alias});
        $string = str_replace($match[0], $match[1] . json_encode($scripts) . $match[3], $string);
    }
    public static function removeScriptsStyles(string &$string, string $name, string $alias = ''): void
    {
        [$start, $end] = \RegularLabs\Library\Protect::getInlineCommentTags($name, null, \true);
        $alias = $alias ?: \RegularLabs\Library\Extension::getAliasByName($name);
        $string = \RegularLabs\Library\RegEx::replace('((?:;\s*)?)(;?)' . $start . '.*?' . $end . '\s*', '\1', $string);
        $string = \RegularLabs\Library\RegEx::replace('\s*<link [^>]*href="[^"]*/(' . $alias . '/css|css/' . $alias . ')/[^"]*\.css[^"]*"[^>]*( /)?>', '', $string);
        $string = \RegularLabs\Library\RegEx::replace('\s*<script [^>]*src="[^"]*/(' . $alias . '/js|js/' . $alias . ')/[^"]*\.js[^"]*"[^>]*></script>', '', $string);
        $string = \RegularLabs\Library\RegEx::replace('\s*<script></script>', '', $string);
    }
    public static function removeStyleTag(string &$string, string $folder, string $name): void
    {
        $name = \RegularLabs\Library\RegEx::quote($name);
        $name = str_replace('\*', '[^"]*', $name);
        $string = \RegularLabs\Library\RegEx::replace('\s*<link [^>]*href="[^"]*(' . $folder . '/css|css/' . $folder . ')/' . $name . '\.[^>]*( /)?>', '', $string);
    }
    public static function script(string $name, array $attributes = ['defer' => \true], array $dependencies = [], bool $convert_dots = \true): void
    {
        $file = $name;
        if ($convert_dots) {
            $file = str_replace('.', '/', $file) . '.min.js';
        }
        if ($name == 'regularlabs.regular') {
            $attributes['defer'] = \false;
        }
        self::getAssetManager()->registerAndUseScript($name, $file, [], $attributes, $dependencies);
    }
    public static function scriptDeclaration(string $content = '', string $name = '', bool $minify = \true, string $position = 'before'): void
    {
        if ($minify) {
            $content = \RegularLabs\Library\StringHelper::minify($content);
        }
        if ($name == '') {
            $content = \RegularLabs\Library\Protect::wrapScriptDeclaration($content, $name, $minify);
        }
        self::getAssetManager()->addInlineScript($content, ['position' => $position]);
    }
    public static function scriptOptions(array $options = [], string $name = ''): void
    {
        JHtml::_('behavior.core');
        $alias = \RegularLabs\Library\RegEx::replace('[^a-z0-9_-]', '', strtolower($name));
        $key = 'rl_' . $alias;
        self::get()->addScriptOptions($key, $options);
    }
    public static function setComponentBuffer(string $buffer = ''): void
    {
        self::get()->setBuffer($buffer, 'component');
    }
    public static function style(string $name, array $attributes = [], bool $convert_dots = \true): void
    {
        $file = $name;
        if ($convert_dots) {
            $file = str_replace('.', '/', $file) . '.min.css';
        }
        self::getAssetManager()->registerAndUseStyle($name, $file, [], $attributes);
    }
    public static function styleDeclaration(string $content = '', string $name = '', bool $minify = \true): void
    {
        if ($minify) {
            $content = \RegularLabs\Library\StringHelper::minify($content);
        }
        if ($name == '') {
            $content = \RegularLabs\Library\Protect::wrapStyleDeclaration($content, $name, $minify);
        }
        self::getAssetManager()->addInlineStyle($content);
    }
    public static function usePreset(string $name): void
    {
        self::getAssetManager()->usePreset($name);
    }
    public static function useScript(string $name): void
    {
        self::getAssetManager()->useScript($name);
    }
    public static function useStyle(string $name): void
    {
        self::getAssetManager()->useStyle($name);
    }
}
