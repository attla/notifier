<?php

namespace Attla\Notifier;

use Attla\Notifier\Pixel\Queue;
use Illuminate\Contracts\Http\Kernel;
use Attla\Notifier\Middlewares\InjectPixelNotifier;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register the service provider
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'notifier');
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
    }

    /**
     * Bootstrap the application events
     *
     * @return void
     */
    public function boot()
    {
        Queue::load();

        $this->publishes([
            $this->configPath() => $this->app->configPath('notifier.php'),
        ], 'attla/notifier/config');

        $this->loadViewsFrom(__DIR__ . '/../views/', 'notifier');

        $this->app
            ->make(Kernel::class)
            ->prependMiddlewareToGroup('web', InjectPixelNotifier::class);
    }

    /**
     * Get config path
     *
     * @param bool
     */
    protected function configPath()
    {
        return __DIR__ . '/../config/notifier.php';
    }
}
