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

use Joomla\CMS\Uri\Uri as JUri;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\EditorButtonPlugin as RL_EditorButtonPlugin;
use RegularLabs\Library\Extension as RL_Extension;

defined('_JEXEC') or die;

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/regularlabs.xml')
    || ! class_exists('RegularLabs\Library\Parameters')
    || ! class_exists('RegularLabs\Library\DownloadKey')
    || ! class_exists('RegularLabs\Library\EditorButtonPlugin')
)
{
    return;
}

if ( ! RL_Document::isJoomlaVersion(4))
{
    RL_Extension::disable('sourcerer', 'plugin', 'editors-xtd');

    return;
}

if (true)
{
    class PlgButtonSourcerer extends RL_EditorButtonPlugin
    {
        protected $button_icon = '<svg viewBox="0 0 24 24" style="fill:none;" width="24" height="24" fill="none" stroke="currentColor">'
        . '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />'
        . '</svg>';

        protected function loadScripts()
        {
            $params = $this->getParams();

            RL_Document::scriptOptions([
                'syntax_word'    => $params->syntax_word,
                'tag_characters' => explode('.', $params->tag_characters),
                'color_code'     => (bool) $params->color_code,
                'root'           => JUri::root(true),
            ], 'sourcerer_button');

            RL_Document::script('sourcerer.button');
        }
    }
}
