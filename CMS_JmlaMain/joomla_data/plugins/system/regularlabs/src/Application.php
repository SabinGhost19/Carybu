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

use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Input as RL_Input;

class Application
{
    static function getThemesDirectory()
    {
        if (JFactory::getApplication()->get('themes.base'))
        {
            return JFactory::getApplication()->get('themes.base');
        }

        if (defined('JPATH_THEMES'))
        {
            return JPATH_THEMES;
        }

        if (defined('JPATH_BASE'))
        {
            return JPATH_BASE . '/themes';
        }

        return __DIR__ . '/themes';
    }

    public function render()
    {
        $app      = JFactory::getApplication();
        $document = RL_Document::get();
        $user     = $app->getIdentity() ?: JFactory::getUser();
        $template = $app->getTemplate(true);
        $clientId = (int) $app->getClientId();

        $document->getWebAssetManager()->getRegistry()->addTemplateRegistryFile($template->template, $clientId);

        $app->loadDocument($document);

        $template_file = RL_Input::getCmd('tmpl', 'index');
        $params        = [
            'template'         => $app->get('theme', $template->template),
            'file'             => $app->get('themeFile', $template_file . '.php'),
            'params'           => $template->params,
            'csp_nonce'        => $app->get('csp_nonce'),
            'templateInherits' => $app->get('themeInherits'),
            'directory'        => self::getThemesDirectory(),
        ];

        // Parse the document.
        $document->parse($params);

        // Trigger the onBeforeRender event.
        JPluginHelper::importPlugin('system');
        $event = AbstractEvent::create(
            'onBeforeRender',
            ['subject' => $app,]
        );
        $app->getDispatcher()->dispatch('onBeforeRender', $event);

        $caching = false;

        if ($app->isClient('site') && $app->get('caching') && $app->get('caching', 2) == 2 && ! $user->get('id'))
        {
            $caching = true;
        }

        // Render the document.
        $data = $document->render($caching, $params);

        // Set the application output data.
        $app->setBody($data);

        // Trigger the onAfterRender event.
        $event = AbstractEvent::create(
            'onAfterRender',
            ['subject' => $app,]
        );
        $app->getDispatcher()->dispatch('onBeforeRender', $event);

        // Mark afterRender in the profiler.
        // Causes issues, so commented out.
        // JDEBUG ? $app->profiler->mark('afterRender') : null;
    }
}
