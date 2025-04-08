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
use RegularLabs\Library\Form\FormField as RL_FormField;
class DependencyField extends RL_FormField
{
    protected function getInput()
    {
        $file = $this->get('file', '');
        $label = $this->get('label', 'the main extension');
        \RegularLabs\Library\Form\Field\DependencyFieldHelper::setMessage($file, $label);
        return '';
    }
    protected function getLabel()
    {
        return '';
    }
}
