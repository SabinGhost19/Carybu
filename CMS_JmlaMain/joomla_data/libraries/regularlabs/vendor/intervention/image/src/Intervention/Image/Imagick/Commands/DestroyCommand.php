<?php

namespace RegularLabs\Scoped\Intervention\Image\Imagick\Commands;

use RegularLabs\Scoped\Intervention\Image\Commands\AbstractCommand;
class DestroyCommand extends AbstractCommand
{
    /**
     * Destroys current image core and frees up memory
     *
     * @param  \Intervention\Image\Image $image
     * @return boolean
     */
    public function execute($image)
    {
        // destroy image core
        $image->getCore()->clear();
        // destroy backups
        foreach ($image->getBackups() as $backup) {
            $backup->clear();
        }
        return \true;
    }
}
