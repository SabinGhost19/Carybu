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

namespace RegularLabs\Plugin\System\RegularLabs;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use RegularLabs\Library\Document as JDocument;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Http as RL_Http;
use RegularLabs\Library\Input as RL_Input;
use RegularLabs\Library\RegEx as RL_RegEx;

class QuickPage
{
    public static function render()
    {
        if ( ! RL_Input::getInt('rl_qp', 0))
        {
            return;
        }

        $url = RL_Input::getString('url', '');

        if ($url)
        {
            echo RL_Http::getFromServer($url, RL_Input::getInt('timeout', ''));

            die;
        }

        if ( ! self::passClassCheck())
        {
            die;
        }

        self::setRequestOptionToContent();
        self::setHeaderContentType();
        self::loadTemplateAssets();

        echo self::getHtml();

        die;
    }

    private static function getHtml()
    {
        $class  = RL_Input::getString('class', '');
        $method = RL_Input::getString('method', 'render');

        $class = '\\RegularLabs\\' . str_replace('.', '\\', $class);

        ob_start();
        (new $class)->$method();
        $html = ob_get_contents();
        ob_end_clean();

        RL_Document::setComponentBuffer($html);

        $app = new Application;
        $app->render();

        $html = JFactory::getApplication()->getBody();

        $html = RL_RegEx::replace('\s*<link [^>]*href="[^"]*templates/system/[^"]*\.css[^"]*"[^>]*( /)?>', '', $html);
        $html = RL_RegEx::replace('(<body [^>]*class=")', '\1rl-popup ', $html);
        $html = str_replace('<body>', '<body class="rl-popup"', $html);

        return $html;
    }

    private static function loadTemplateAssets()
    {
        $app           = JFactory::getApplication();
        $asset_manager = JDocument::getAssetManager();
        $template      = $app->getTemplate(true);
        $clientId      = (int) $app->getClientId();

        if ( ! empty($template->parent))
        {
            $asset_manager->getRegistry()->addTemplateRegistryFile($template->parent, $clientId);
        }

        $asset_manager->getRegistry()->addTemplateRegistryFile($template->template, $clientId);
    }

    private static function passClassCheck()
    {
        $class = RL_Input::getString('class', '');

        if ( ! $class)
        {
            return false;
        }

        $allowed = [
            'Plugin.EditorButton.ArticlesAnywhere.Popup',
            'Plugin.EditorButton.ConditionalContent.Popup',
            'Plugin.EditorButton.ContentTemplater.Data',
            'Plugin.EditorButton.ContentTemplater.Popup',
            'Plugin.EditorButton.DummyContent.Popup',
            'Plugin.EditorButton.Modals.Popup',
            'Plugin.EditorButton.ModulesAnywhere.Popup',
            'Plugin.EditorButton.Sliders.data.php',
            'Plugin.EditorButton.Sliders.Popup',
            'Plugin.EditorButton.Snippets.Popup',
            'Plugin.EditorButton.Sourcerer.Popup',
            'Plugin.EditorButton.TabsAccordions.Popup',
            'Plugin.EditorButton.Tooltips.Popup',
        ];

        return in_array($class, $allowed) !== false;
    }

    private static function setHeaderContentType()
    {
        switch (RL_Input::getCmd('format', 'html'))
        {
            case 'json' :
                $format = 'application/json';
                break;

            default:
            case 'html' :
                $format = 'text/html';
                break;
        }

        header('Content-Type: ' . $format . '; charset=utf-8');
    }

    private static function setRequestOptionToContent()
    {
        $_REQUEST['tmpl'] = 'component';
        RL_Input::set('option', 'com_content');
    }
}
