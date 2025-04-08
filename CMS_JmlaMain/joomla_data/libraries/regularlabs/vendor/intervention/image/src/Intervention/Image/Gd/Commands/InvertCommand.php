<?php

namespace RegularLabs\Scoped\Intervention\Image\Gd\Commands;

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
        return imagefilter($image->getCore(), \IMG_FILTER_NEGATE);
    }
}
