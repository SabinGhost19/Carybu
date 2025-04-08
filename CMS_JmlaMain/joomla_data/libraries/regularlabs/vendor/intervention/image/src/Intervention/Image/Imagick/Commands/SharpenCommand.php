<?php

namespace RegularLabs\Scoped\Intervention\Image\Imagick\Commands;

use RegularLabs\Scoped\Intervention\Image\Commands\AbstractCommand;
class SharpenCommand extends AbstractCommand
{
    /**
     * Sharpen image
     *
     * @param  \Intervention\Image\Image $image
     * @return boolean
     */
    public function execute($image)
    {
        $amount = $this->argument(0)->between(0, 100)->value(10);
        return $image->getCore()->unsharpMaskImage(1, 1, $amount / 6.25, 0);
    }
}
