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
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Form\FormField as RL_FormField;
class LoadMediaField extends RL_FormField
{
    protected function getInput()
    {
        return '';
    }
    protected function getLabel()
    {
        $filetype = $this->get('filetype');
        $file = $this->get('file');
        switch ($filetype) {
            case 'style':
                RL_Document::style($file);
                break;
            case 'script':
                RL_Document::script($file);
                break;
            default:
                break;
        }
        return '';
    }
}
