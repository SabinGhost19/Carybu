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
require_once dirname(__FILE__, 2) . '/vendor/autoload.php';
class MobileDetect extends \RegularLabs\Scoped\Detection\MobileDetect
{
    public function isCurl(): bool
    {
        return $this->match('curl', $this->getUserAgent());
    }
    public function isMac(): bool
    {
        return $this->match('(Mac OS|Mac_PowerPC|Macintosh)', $this->getUserAgent());
    }
}
