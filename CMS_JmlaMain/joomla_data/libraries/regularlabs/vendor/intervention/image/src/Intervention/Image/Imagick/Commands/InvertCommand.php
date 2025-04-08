<?php

namespace RegularLabs\Scoped\Intervention\Image\Imagick\Commands;

use RegularLabs\Scoped\Intervention\Image\Commands\AbstractCommand;
class InvertCommand extends AbstractCommand
{
    /**
     * Inverts colors of an image
     *
     * @param  \Intervention\Image\Image $image
     * @return boolean
     */
    public function execute($image)
    {
        return $image->getCore()->negateImage(\false);
    }
}
