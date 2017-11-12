<?php namespace Larabookir\Saderat;
/**
 * @Author     Hamed Pakdaman - pakdaman.it@gmail.com
 * @website    larabook.ir
 * @link       http://pear.php.net/package/PackageName
 * @version    1.00
 */

use Larabookir\Saderat\SaderatResolver;
use Illuminate\Support\ServiceProvider;

class SaderatServiceProvider extends ServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		#$this->loadViewsFrom(null, 'saderat');
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton('saderat', function () {
			return new SaderatResolver();
		});

	}
}
