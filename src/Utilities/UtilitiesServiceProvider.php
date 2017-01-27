<?php namespace Srlabs\Utilities;

use Illuminate\Support\ServiceProvider;
use SRLabs\Utilities\Commands\TestDB;

class UtilitiesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands(
                [
                    TestDB::class,
                ]
            );
        }
    }
}
