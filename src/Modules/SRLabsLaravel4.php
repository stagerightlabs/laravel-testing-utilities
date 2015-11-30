<?php
namespace Codeception\Module;

class SRLabsLaravel4 extends \Codeception\Module\Laravel4
{
    /**
     * Provides access the Laravel application object.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function bindIntance($abstract, $instance)
    {
        $this->app->instance($abstract, $instance);
    }
}