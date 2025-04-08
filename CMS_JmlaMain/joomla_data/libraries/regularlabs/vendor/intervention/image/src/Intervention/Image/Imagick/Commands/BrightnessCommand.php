<?php

namespace RegularLabs\Scoped\Intervention\Image\Imagick\Commands;

use RegularLabs\Scoped\Intervention\Image\Commands\AbstractCommand;
class BrightnessCommand extends AbstractCommand
{
    /**
     * Changes image brightness
     *
     * @param  \Intervention\Image\Image $image
     * @return boolean
     */
    public function execute($image)
    {
        $level = $this->argument(0)->between(-100, 100)->required()->value();
        return $image->getCore()->modulateImage(100 + $level, 100, 100);
    }
}
