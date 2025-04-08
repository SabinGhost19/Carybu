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
use Joomla\CMS\Application\CMSApplication as JCMSApplication;
use Joomla\CMS\Application\CMSApplicationInterface as JCMSApplicationInterface;
use Joomla\CMS\Document\Document as JDocument;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Version as JVersion;
use Joomla\Filesystem\File as JFile;
use Joomla\Filesystem\Path as JPath;
class Php
{
    static $rl_variables;
    public static function execute(string $rl_string, object|false|null $rl_article = null, object|false|null $rl_module = null, bool $has_own_return = \false): mixed
    {
        self::prepareString($rl_string);
        $function_name = self::getFunctionName($rl_string);
        if (!$function_name) {
            // Something went wrong!
            return \true;
        }
        if (!$rl_article && str_contains($rl_string, '$article')) {
            if (\RegularLabs\Library\Input::get('option', '') == 'com_content' && \RegularLabs\Library\Input::get('view', '') == 'article') {
                $rl_article = \RegularLabs\Library\Article::get(\RegularLabs\Library\Input::getInt('id'));
            }
        }
        $rl_pre_variables = array_keys(get_defined_vars());
        ob_start();
        $rl_post_variables = $function_name(self::$rl_variables, $rl_article, $rl_module);
        $rl_output = ob_get_contents();
        ob_end_clean();
        if ($has_own_return) {
            return $rl_post_variables;
        }
        if (!is_array($rl_post_variables)) {
            return $rl_output;
        }
        $rl_diff_variables = array_diff(array_keys($rl_post_variables), $rl_pre_variables);
        foreach ($rl_diff_variables as $rl_diff_key) {
            if (in_array($rl_diff_key, ['Itemid', 'mainframe', 'app', 'document', 'doc', 'database', 'db', 'user', 'article', 'module'], \true) || substr($rl_diff_key, 0, 4) == 'rl_') {
                continue;
            }
            self::$rl_variables[$rl_diff_key] = $rl_post_variables[$rl_diff_key];
        }
        return $rl_output;
    }
    public static function getApplication(): JCMSApplicationInterface
    {
        if (\RegularLabs\Library\Input::get('option', '') != 'com_finder') {
            return JFactory::getApplication();
        }
        return JCMSApplication::getInstance('site');
    }
    public static function getDocument(): JDocument
    {
        if (\RegularLabs\Library\Input::get('option', '') != 'com_finder') {
            return \RegularLabs\Library\Document::get();
        }
        $lang = JFactory::getApplication()->getLanguage();
        $version = new JVersion();
        $attributes = ['charset' => 'utf-8', 'lineend' => 'unix', 'tab' => "\t", 'language' => $lang->getTag(), 'direction' => $lang->isRtl() ? 'rtl' : 'ltr', 'mediaversion' => $version->getMediaVersion()];
        return JDocument::getInstance('html', $attributes);
    }
    private static function createFunctionInMemory(string $string): void
    {
        $file_name = getmypid() . '_' . md5($string);
        $tmp_path = JFactory::getApplication()->get('tmp_path', JPATH_ROOT . '/tmp');
        $temp_file = $tmp_path . '/regularlabs/custom_php/' . $file_name;
        $temp_file = JPath::clean($temp_file);
        // Write file
        if (!file_exists($temp_file) || is_writable($temp_file)) {
            JFile::write($temp_file, $string);
        }
        // Include file
        include_once $temp_file;
        // Delete file
        if (!JFactory::getApplication()->get('debug')) {
            @chmod($temp_file, 0777);
            @unlink($temp_file);
        }
    }
    private static function extractUseStatements(string &$string): string
    {
        $use_statements = [];
        $string = trim($string);
        \RegularLabs\Library\RegEx::matchAll('^use\s+[^\s;]+\s*;', $string, $matches, 'm');
        foreach ($matches as $match) {
            $use_statements[] = $match[0];
            $string = str_replace($match[0], '', $string);
        }
        $string = trim($string);
        return implode("\n", $use_statements);
    }
    private static function generateFileContents(string $function_name = 'rl_function', string $string = ''): string
    {
        $use_statements = self::extractUseStatements($string);
        $init_variables = self::getVarInits();
        $init_variables[] = 'if (is_array($rl_variables)) {' . 'foreach ($rl_variables as $rl_key => $rl_value) {' . '${$rl_key} = $rl_value;' . '}' . '}';
        $contents = ['<?php', 'defined(\'_JEXEC\') or die;', $use_statements, 'function ' . $function_name . '($rl_variables, $article, $module){', implode("\n", $init_variables), $string . ';', 'return get_defined_vars();', ';}'];
        $contents = implode("\n", $contents);
        // Remove Zero Width spaces / (non-)joiners
        $contents = str_replace(["​", "‌", "‍"], '', $contents);
        return $contents;
    }
    private static function getFunctionName(string $string): string|false
    {
        $function_name = 'regularlabs_php_' . md5($string);
        if (function_exists($function_name)) {
            return $function_name;
        }
        $contents = self::generateFileContents($function_name, $string);
        self::createFunctionInMemory($contents);
        if (!function_exists($function_name)) {
            // Something went wrong!
            return \false;
        }
        return $function_name;
    }
    private static function getVarInits(): array
    {
        return ['$app = $mainframe = RegularLabs\Library\Php::getApplication();', '$document = $doc = RegularLabs\Library\Php::getDocument();', '$database = $db = Joomla\CMS\Factory::getDbo();', '$user = $app->getIdentity() ?: Joomla\CMS\Factory::getUser();', '$Itemid = $app->getInput()->getInt(\'Itemid\');'];
    }
    private static function prepareString(string &$string): void
    {
        $string = trim($string);
        $string = str_replace('?><?php', '', $string . '<?php ;');
        if (!str_starts_with($string, '<?php')) {
            $string = '?>' . $string;
            return;
        }
        $string = substr($string, 5);
        $string = trim($string);
    }
}
