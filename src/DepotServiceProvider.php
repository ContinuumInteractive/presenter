<?php

namespace DBonner\Depot;

use Illuminate\Support\ServiceProvider;
use DBonner\Depot\Presentation\PresentationDecorator;

class DepotServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/depot.php' => config_path('depot.php'),
        ]);

        PresentationDecorator::registerPresenters($this->app['config']->get('depot.presenters', []));
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //...
    }
}
