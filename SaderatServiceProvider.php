<?php namespace Larabookir\Saderat;
/**
 * @Author     Hamed Pakdaman - pakdaman.it@gmail.com
 * @website    larabook.ir
 * @link       https://github.com/larabook/gateway/
 * @version    1.00
 */

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
        $config = __DIR__ . '/Config/saderat.php';
        $views = __DIR__ . '/Views/';
        //php artisan vendor:publish --provider=Larabookir\Saderat\SaderatServiceProvider --tag=config
        $this->publishes([
            $config => config_path('saderat.php'),
        ], 'config');


        $this->loadViewsFrom($views, 'saderat');
        // php artisan vendor:publish --provider=Larabookir\Saderat\SaderatServiceProvider --tag=views
        $this->publishes([
            $views => base_path('resources/views/vendor/saderat'),
        ], 'views');
        //$this->mergeConfigFrom( $config,'gateway')
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
