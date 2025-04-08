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
use RegularLabs\Library\HtmlTag as RL_HtmlTag;
class ImageField extends RL_FormField
{
    protected function getInput()
    {
        $attributes = ['src' => (string) (string) $this->element['src']];
        if ($this->element['alt']) {
            $attributes['alt'] = (string) $this->element['alt'];
        }
        if ($this->element['title']) {
            $attributes['title'] = (string) $this->element['title'];
        }
        if ($this->element['height']) {
            $attributes['height'] = (string) $this->element['height'];
        }
        if ($this->element['width']) {
            $attributes['width'] = (string) $this->element['width'];
        }
        $attributes = RL_HtmlTag::combineAttributes($attributes, (string) $this->element['attributes']);
        return '<img ' . $attributes . '>';
    }
}
