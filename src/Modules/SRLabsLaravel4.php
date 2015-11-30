<?php
namespace Codeception\Module;

use SRLabs\Utilities\Libraries\Laravel4Connector;

class SRLabsLaravel4 extends \Codeception\Module\Laravel4
{
    /**
     * Initialize hook.
     */
    public function _initialize()
    {
        $this->checkStartFileExists();
        $this->registerAutoloaders();
        $this->revertErrorHandler();
        $this->client = new Laravel4Connector($this);
    }

    /**
     * Allow the Codeception Actor to add a binding to the Laravel IOC
     *
     * @return \Illuminate\Foundation\Application
     */
    public function bindService($abstract, $instance, $shared = false)
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
