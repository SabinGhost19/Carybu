<?php
/**
 * @package         Sourcerer
 * @version         12.0.2
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2025 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Extension as RL_Extension;
use RegularLabs\Library\Html as RL_Html;
use RegularLabs\Library\Protect as RL_Protect;
use RegularLabs\Library\SystemPlugin as RL_SystemPlugin;
use RegularLabs\Plugin\System\Sourcerer\Area;
use RegularLabs\Plugin\System\Sourcerer\Clean;
use RegularLabs\Plugin\System\Sourcerer\Params;
use RegularLabs\Plugin\System\Sourcerer\Protect;
use RegularLabs\Plugin\System\Sourcerer\Replace;
use RegularLabs\Plugin\System\Sourcerer\Security;

// Do not instantiate plugin on install pages
// to prevent installation/update breaking because of potential breaking changes
if (
    in_array(JFactory::getApplication()->input->getCmd('option'), ['com_installer', 'com_regularlabsmanager'])
    && JFactory::getApplication()->input->getCmd('action') != ''
)
{
    return;
}

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/regularlabs.xml')
    || ! class_exists('RegularLabs\Library\Parameters')
    || ! class_exists('RegularLabs\Library\DownloadKey')
    || ! class_exists('RegularLabs\Library\SystemPlugin')
)
{
    JFactory::getApplication()->getLanguage()->load('plg_system_sourcerer', __DIR__);
    JFactory::getApplication()->enqueueMessage(
        JText::sprintf('SRC_EXTENSION_CAN_NOT_FUNCTION', JText::_('SOURCERER'))
        . ' ' . JText::_('SRC_REGULAR_LABS_LIBRARY_NOT_INSTALLED'),
        'error'
    );

    return;
}

if ( ! RL_Document::isJoomlaVersion(4, 'SOURCERER'))
{
    RL_Extension::disable('sourcerer', 'plugin');

    RL_Document::adminError(
        JText::sprintf('RL_PLUGIN_HAS_BEEN_DISABLED', JText::_('SOURCERER'))
    );

    return;
}

if (true)
{
    class PlgSystemSourcerer extends RL_SystemPlugin
    {
        public $_lang_prefix        = 'SRC';
        public $_can_disable_by_url = false;
        public $_jversion           = 4;

        protected function handleOnContentPrepare($area, $context, &$article, &$params, $page = 0)
        {
            $src_params = Params::get();

            $area = isset($article->created_by) ? 'articles' : 'other';

            $remove = $src_params->remove_from_search
                && in_array($context, ['com_search.search', 'com_search.search.article', 'com_finder.indexer']);


            if (isset($article->description))
            {
                Replace::replace($article->description, $area, $article, $remove);
            }

            if (isset($article->title))
            {
                Replace::replace($article->title, $area, $article, $remove);
            }

            // Don't handle article texts in category list view
            if (RL_Document::isCategoryList($context))
            {
                return false;
            }

            if (isset($article->text))
            {
                Replace::replace($article->text, $area, $article, $remove);

                // Don't also do stuff on introtext/fulltext if text is set
                return false;
            }

            if (isset($article->introtext))
            {
                Replace::replace($article->introtext, $area, $article, $remove);
            }

            if (isset($article->fulltext))
            {
                Replace::replace($article->fulltext, $area, $article, $remove);
            }

            return false;
        }

        /**
         * @param object $module
         * @param array  $params
         */
        protected function handleOnAfterRenderModule(&$module, &$params): void
        {
            if ( ! isset($module->content))
            {
                return;
            }

            Replace::replace($module->content, 'module');
        }

        protected function changeDocumentBuffer(&$buffer)
        {
            if ( ! RL_Document::isHtml())
            {
                return false;
            }

            return Area::tag($buffer, 'component');
        }

        protected function changeFinalHtmlOutput(&$html)
        {
            $params = Params::get();

            [$pre, $body, $post] = RL_Html::getBody($html);

            Protect::_($body);
            Replace::replaceInTheRest($body);

            Clean::cleanFinalHtmlOutput($body);
            RL_Protect::unprotect($body);

            $params->enable_in_head
                ? Replace::replace($pre, 'head')
                : Clean::cleanTagsFromHead($pre);

            $html = $pre . $body . $post;

            return true;
        }
    }
}
