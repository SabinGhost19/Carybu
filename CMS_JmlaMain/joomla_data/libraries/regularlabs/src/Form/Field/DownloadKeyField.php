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
use Joomla\CMS\Layout\FileLayout as JFileLayout;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Form\FormField as RL_FormField;
class DownloadKeyField extends RL_FormField
{
    protected function getInput()
    {
        RL_Document::script('regularlabs.script');
        RL_Document::script('regularlabs.downloadkey');
        return (new JFileLayout('regularlabs.form.field.downloadkey', JPATH_SITE . '/libraries/regularlabs/layouts'))->render(['id' => $this->id, 'extension' => strtolower($this->get('extension', 'all')), 'use_modal' => $this->get('use-modal', \true)]);
    }
}
