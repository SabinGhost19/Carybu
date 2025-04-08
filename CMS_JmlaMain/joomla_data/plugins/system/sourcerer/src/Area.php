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

namespace RegularLabs\Plugin\System\Sourcerer;

defined('_JEXEC') or die;

use RegularLabs\Library\RegEx as RL_RegEx;

class Area
{
    static $prefix = 'SRC';

    public static function get(string &$string, string $area = ''): array
    {
        if (empty($string) || empty($area))
        {
            return [];
        }

        $start = '<!-- START: ' . self::$prefix . '_' . strtoupper($area) . ' -->';
        $end   = '<!-- END: ' . self::$prefix . '_' . strtoupper($area) . ' -->';

        $matches = explode($start, $string);
        array_shift($matches);

        foreach ($matches as $i => $match)
        {
            [$text] = explode($end, $match, 2);
            $matches[$i] = [
                $start . $text . $end,
                $text,
            ];
        }

        return $matches;
    }

    public static function tag(string &$string, string $area = ''): bool
    {
        if (empty($string) || empty($area))
        {
            return false;
        }

        $string = '<!-- START: ' . self::$prefix . '_' . strtoupper($area) . ' -->' . $string . '<!-- END: ' . self::$prefix . '_' . strtoupper($area) . ' -->';

        if ($area != 'article_text')
        {
            return true;
        }

        $string = RL_RegEx::replace(
            '#(<hr class="system-pagebreak".*?>)#si',
            '<!-- END: ' . self::$prefix . '_' . strtoupper($area) . ' -->\1<!-- START: ' . self::$prefix . '_' . strtoupper($area) . ' -->',
            $string
        );

        return true;
    }
}
