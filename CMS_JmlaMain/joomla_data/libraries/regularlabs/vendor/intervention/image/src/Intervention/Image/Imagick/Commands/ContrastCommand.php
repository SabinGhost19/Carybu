<?php

namespace RegularLabs\Scoped\Intervention\Image\Imagick\Commands;

use RegularLabs\Scoped\Intervention\Image\Commands\AbstractCommand;
class ContrastCommand extends AbstractCommand
{
    /**
     * Changes contrast of image
     *
     * @param  \Intervention\Image\Image $image
     * @return boolean
     */
    public function execute($image)
    {
        $level = $this->argument(0)->between(-100, 100)->required()->value();
        return $image->getCore()->sigmoidalContrastImage($level > 0, $level / 4, 0);
    }
}
