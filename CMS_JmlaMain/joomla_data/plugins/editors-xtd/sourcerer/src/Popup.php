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

namespace RegularLabs\Plugin\EditorButton\Sourcerer;

defined('_JEXEC') or die;

use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\EditorButtonPopup as RL_EditorButtonPopup;
use RegularLabs\Library\Input as RL_Input;
use RegularLabs\Library\RegEx as RL_RegEx;

class Popup extends RL_EditorButtonPopup
{
    protected $extension         = 'sourcerer';
    protected $require_core_auth = false;

    protected function loadScripts()
    {
        $editor_name = RL_Input::getString('editor', 'text');
        // Remove any dangerous character to prevent cross site scripting
        $editor_name = RL_RegEx::replace('[\'\";\s]', '', $editor_name);

        RL_Document::script('sourcerer.popup');

        $script = "document.addEventListener('DOMContentLoaded', function(){RegularLabs.SourcererPopup.init('" . $editor_name . "')});";
        RL_Document::scriptDeclaration($script, 'Sourcerer Button', true, 'after');
    }
}
