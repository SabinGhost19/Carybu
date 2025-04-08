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
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Input as RL_Input;

class SearchHelper
{
    public static function load()
    {
        // Only in frontend search component view
        if ( ! RL_Document::isClient('site') || RL_Input::getCmd('option') != 'com_search')
        {
            return;
        }

        $classes = get_declared_classes();

        if (in_array('SearchModelSearch', $classes) || in_array('searchmodelsearch', $classes))
        {
            return;
        }

        require_once JPATH_LIBRARIES . '/regularlabs/helpers/search.php';
    }
}
