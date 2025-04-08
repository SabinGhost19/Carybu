<?php

namespace RegularLabs\Scoped\Intervention\Image\Gd\Commands;

use RegularLabs\Scoped\Intervention\Image\Commands\AbstractCommand;
use RegularLabs\Scoped\Intervention\Image\Size;
class GetSizeCommand extends AbstractCommand
{
    /**
     * Reads size of given image instance in pixels
     *
     * @param  \Intervention\Image\Image $image
     * @return boolean
     */
    public function execute($image)
    {
        $this->setOutput(new Size(imagesx($image->getCore()), imagesy($image->getCore())));
        return \true;
    }
}
