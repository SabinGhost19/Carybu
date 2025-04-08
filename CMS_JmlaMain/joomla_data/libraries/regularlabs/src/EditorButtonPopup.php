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
use Exception;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use Joomla\Registry\Registry as JRegistry;
use ReflectionClass;
class EditorButtonPopup
{
    public $editor_name = '';
    public $form;
    public $params;
    protected $extension = '';
    protected $main_type = 'plugin';
    protected $require_core_auth = \true;
    private $_params;
    public function render()
    {
        if (!\RegularLabs\Library\Extension::isAuthorised($this->require_core_auth)) {
            throw new Exception(JText::_("ALERTNOTAUTH"));
        }
        $this->params = $this->getParams();
        if (!\RegularLabs\Library\Extension::isEnabledInArea($this->params)) {
            throw new Exception(JText::_("ALERTNOTAUTH"));
        }
        $this->loadLanguages();
        $doc = \RegularLabs\Library\Document::get();
        $asset_manager = \RegularLabs\Library\Document::getAssetManager();
        $direction = $doc->getDirection();
        $template_params = $this->getTemplateParams();
        // Get the hue value
        preg_match('#^hsla?\(([0-9]+)[\D]+([0-9]+)[\D]+([0-9]+)[\D]+([0-9](?:.\d+)?)?\)$#i', $template_params->get('hue', 'hsl(214, 63%, 20%)'), $matches);
        // Enable assets
        $asset_manager->getRegistry()->addTemplateRegistryFile('atum', 1);
        $asset_manager->usePreset('template.atum.' . ($direction === 'rtl' ? 'rtl' : 'ltr'))->addInlineStyle(':root {
                --hue: ' . $matches[1] . ';
                --template-bg-light: ' . $template_params->get('bg-light', '--template-bg-light') . ';
                --template-text-dark: ' . $template_params->get('text-dark', '--template-text-dark') . ';
                --template-text-light: ' . $template_params->get('text-light', '--template-text-light') . ';
                --template-link-color: ' . $template_params->get('link-color', '--template-link-color') . ';
                --template-special-color: ' . $template_params->get('special-color', '--template-special-color') . ';
            }');
        // No template.js for modals
        //$asset_manager->disableScript('template.atum');
        // Override 'template.active' asset to set correct ltr/rtl dependency
        $asset_manager->registerStyle('template.active', '', [], [], ['template.atum.' . ($direction === 'rtl' ? 'rtl' : 'ltr')]);
        // Browsers support SVG favicons
        $doc->addHeadLink(JHtml::_('image', 'joomla-favicon.svg', '', [], \true, 1), 'icon', 'rel', ['type' => 'image/svg+xml']);
        $doc->addHeadLink(JHtml::_('image', 'favicon.ico', '', [], \true, 1), 'alternate icon', 'rel', ['type' => 'image/vnd.microsoft.icon']);
        $doc->addHeadLink(JHtml::_('image', 'joomla-favicon-pinned.svg', '', [], \true, 1), 'mask-icon', 'rel', ['color' => '#000']);
        \RegularLabs\Library\Document::script('regularlabs.admin-form');
        \RegularLabs\Library\Document::style('regularlabs.admin-form');
        \RegularLabs\Library\Document::style('regularlabs.popup');
        $this->init();
        $this->loadScripts();
        $this->loadStyles();
        echo $this->renderTemplate();
    }
    protected function getParams()
    {
        if (!is_null($this->_params)) {
            return $this->_params;
        }
        switch ($this->main_type) {
            case 'component':
                if (\RegularLabs\Library\Protect::isComponentInstalled($this->extension)) {
                    // Load component parameters
                    $this->_params = \RegularLabs\Library\Parameters::getComponent($this->extension);
                }
                break;
            case 'plugin':
            default:
                if (\RegularLabs\Library\Protect::isSystemPluginInstalled($this->extension)) {
                    // Load plugin parameters
                    $this->_params = \RegularLabs\Library\Parameters::getPlugin($this->extension);
                }
                break;
        }
        return $this->_params;
    }
    protected function getTemplateParams()
    {
        $db = \RegularLabs\Library\DB::get();
        $query = \RegularLabs\Library\DB::getQuery()->select(\RegularLabs\Library\DB::quoteName('s.params'))->from(\RegularLabs\Library\DB::quoteName('#__template_styles', 's'))->where(\RegularLabs\Library\DB::is('s.template', 'atum'))->order(\RegularLabs\Library\DB::quoteName('s.home'));
        $db->setQuery($query, 0, 1);
        $template = $db->loadObject();
        return new JRegistry($template->params ?? null);
    }
    protected function init()
    {
    }
    protected function loadLanguages()
    {
        \RegularLabs\Library\Language::load('joomla', JPATH_ADMINISTRATOR);
        \RegularLabs\Library\Language::load('plg_system_regularlabs');
        \RegularLabs\Library\Language::load('plg_editors-xtd_' . $this->extension);
        \RegularLabs\Library\Language::load('plg_system_' . $this->extension);
    }
    protected function loadScripts()
    {
    }
    protected function loadStyles()
    {
    }
    private function getDir(): string
    {
        $rc = new ReflectionClass(static::class);
        return dirname($rc->getFileName());
    }
    private function renderTemplate(): string
    {
        $layout = \RegularLabs\Library\Input::getString('layout', '');
        $file = 'popup' . ($layout ? '.' . $layout : '') . '.php';
        ob_start();
        include dirname($this->getDir()) . '/tmpl/' . $file;
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
}
