<?php

namespace RegularLabs\Scoped\Intervention\Image\Imagick\Commands;

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
        /** @var \Imagick $core */
        $core = $image->getCore();
        $this->setOutput(new Size($core->getImageWidth(), $core->getImageHeight()));
        return \true;
    }
}
