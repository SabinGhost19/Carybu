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
namespace RegularLabs\Library\Form\Field;

defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\Form\FormField as RL_FormField;
class AgentsField extends RL_FormField
{
    public $attributes = ['group' => 'os'];
    public bool $is_select_list = \true;
    public function getNamesByIds(array $values, array $attributes): array
    {
        $agents = $this->getAgents($attributes);
        $names = [];
        foreach ($agents as $agent) {
            if (!in_array($agent[1], $values)) {
                continue;
            }
            $names[] = $agent[0];
        }
        return $names;
    }
    protected function getListOptions(array $attributes): array|int
    {
        $agents = $this->getAgents($attributes);
        $options = [];
        foreach ($agents as $agent) {
            $option = JHtml::_('select.option', $agent[1], $agent[0]);
            $options[] = $option;
        }
        return $options;
    }
    private function getAgents(array $attributes): array
    {
        $agents = [];
        switch ($attributes['group']) {
            /* OS */
            case 'os':
                $agents[] = ['Windows', 'Windows'];
                $agents[] = ['Mac OS', '#(Mac OS|Mac_PowerPC|Macintosh)#'];
                $agents[] = ['Linux', '#(Linux|X11)#'];
                $agents[] = ['Open BSD', 'OpenBSD'];
                $agents[] = ['Sun OS', 'SunOS'];
                $agents[] = ['QNX', 'QNX'];
                $agents[] = ['BeOS', 'BeOS'];
                $agents[] = ['OS/2', 'OS/2'];
                break;
            /* Browsers */
            case 'browser':
                $agents[] = ['Chrome', 'Chrome'];
                $agents[] = ['Firefox', 'Firefox'];
                $agents[] = ['Microsoft Edge', 'MSIE Edge'];
                // missing MSIE is added to agent string in RegularLabs\Component\Conditions\Administrator\Condition\Agent\Agent
                $agents[] = ['Internet Explorer', 'MSIE [0-9]'];
                // missing MSIE is added to agent string in RegularLabs\Component\Conditions\Administrator\Condition\Agent\Agent
                $agents[] = ['Opera', 'Opera'];
                $agents[] = ['Safari', 'Safari'];
                break;
            /* Mobile browsers */
            case 'mobile':
                $agents[] = [JText::_('JALL'), 'mobile'];
                $agents[] = ['Android', 'Android'];
                $agents[] = ['Android Chrome', '#Android.*Chrome#'];
                $agents[] = ['Blackberry', 'Blackberry'];
                $agents[] = ['IE Mobile', 'IEMobile'];
                $agents[] = ['iPad', 'iPad'];
                $agents[] = ['iPhone', 'iPhone'];
                $agents[] = ['iPod Touch', 'iPod'];
                $agents[] = ['NetFront', 'NetFront'];
                $agents[] = ['Nokia', 'NokiaBrowser'];
                $agents[] = ['Opera Mini', 'Opera Mini'];
                $agents[] = ['Opera Mobile', 'Opera Mobi'];
                $agents[] = ['UC Browser', 'UC Browser'];
                break;
            default:
                break;
        }
        return $agents;
    }
}
