<?php

namespace RegularLabs\Scoped\Intervention\Image;

use RegularLabs\Scoped\League\Container\ServiceProvider\AbstractServiceProvider;
class ImageServiceProviderLeague extends AbstractServiceProvider
{
    /**
     * @var array $config
     */
    protected $config;
    /**
     * @var array $provides
     */
    protected $provides = ['RegularLabs\Scoped\Intervention\Image\ImageManager'];
    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->config = $config;
    }
    /**
     * Register the server provider.
     *
     * @return void
     */
    public function register()
    {
        $this->getContainer()->share('RegularLabs\Scoped\Intervention\Image\ImageManager', function () {
            return new ImageManager($this->config);
        });
    }
}
