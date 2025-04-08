<?php

namespace RegularLabs\Scoped\Intervention\Image\Filters;

use RegularLabs\Scoped\Intervention\Image\Image;
interface FilterInterface
{
    /**
     * Applies filter to given image
     *
     * @param  \Intervention\Image\Image $image
     * @return \Intervention\Image\Image
     */
    public function applyFilter(Image $image);
}
