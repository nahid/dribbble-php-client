<?php
namespace Nahid\DribbbleClient;
use Illuminate\Container\Container;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;
class DribbbleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupConfig();
    }
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerDribbbleClient();

    }
    /**
     * Setup the config.
     */
    protected function setupConfig()
    {
        $source = realpath(__DIR__ . '/../config/dribbble.php');
        // Check if the application is a Laravel OR Lumen instance to properly merge the configuration file.
        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$source => config_path('dribbble.php')]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('dribbble');
        }
        $this->mergeConfigFrom($source, 'dribbble');
    }

    /**
     * Register Talk class.
     *
     * @return void
     */
    protected function registerDribbbleClient()
    {
        $this->app->singleton('Dribbble', function (Container $app) {
            return new Dribbble($app['config']->get('dribbble'));
        });
    }
    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            Dribbble::class
        ];
    }
}
