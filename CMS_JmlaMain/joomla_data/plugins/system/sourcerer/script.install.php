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

use Joomla\Filesystem\File as JFile;
use Joomla\Filesystem\Folder as JFolder;

class PlgSystemSourcererInstallerScript
{
    public function postflight($install_type, $adapter)
    {
        if ( ! in_array($install_type, ['install', 'update']))
        {
            return true;
        }

        self::deleteJoomla3Files();

        return true;
    }

    private static function delete($files = [])
    {
        foreach ($files as $file)
        {
            if (is_dir($file))
            {
                JFolder::delete($file);
            }

            if (is_file($file))
            {
                JFile::delete($file);
            }
        }
    }

    private static function deleteJoomla3Files()
    {
        self::delete(
            [
                JPATH_SITE . '/media/sourcerer/css',
                JPATH_SITE . '/media/sourcerer/js/script.js',
                JPATH_SITE . '/media/sourcerer/js/script.min.js',
                JPATH_SITE . '/media/sourcerer/less',
                JPATH_SITE . '/plugins/system/sourcerer/src/Code.php',
                JPATH_SITE . '/plugins/system/sourcerer/vendor',
            ]
        );
    }
}
