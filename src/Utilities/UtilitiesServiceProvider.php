<?php namespace Srlabs\Utilities;

use Illuminate\Support\ServiceProvider;
use SRLabs\Utilities\Commands\TestDB;

class UtilitiesServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('srlabs/testing-utilities');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerTestDBCommand();

		$this->commands('utility:testdb');
	}

	private function registerTestDBCommand()
	{
		$this->app['utility:testdb'] = $this->app->share(function($app)
		{
			return new TestDB(
				$this->app->make('files'),
				$this->app->make('config')
			);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
