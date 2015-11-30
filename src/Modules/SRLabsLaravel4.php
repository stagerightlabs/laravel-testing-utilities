<?php
namespace Codeception\Module;

class SRLabsLaravel4 extends \Codeception\Module\Laravel4
{
    /**
     * Allow the Codeception Actor to add a binding to the Laravel IOC
     *
     * @return \Illuminate\Foundation\Application
     */
    public function bind($abstract, $instance, $shared = false)
    {
        $this->app->bind($abstract, $instance, $shared = false);
    }

    /**
     * Allow the Codeception Actor to bind an instantiated object to the Laravel IOC
     *
     * @return \Illuminate\Foundation\Application
     */
    public function bindInstance($abstract, $instance)
    {
        $this->app->instance($abstract, $instance);
    }
}
