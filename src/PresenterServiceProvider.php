<?php

namespace Continuum\Presenter;

use Continuum\Presenter\Decorator;
use Continuum\Presenter\Presenter;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Container\Container;

/**
 * continuum/presenter package takes influence from the Laravel
 * Auto Presenter package with some added touches and
 * functionality required to service our needs.
 *
 * It has also evolved out of the dbonner1987/depot.
 *
 * Credit to:
 * https://github.com/laravel-auto-presenter
 * https://github.com/GrahamCampbell
 *
 */

class PresenterServiceProvider extends ServiceProvider
{
    /**
     * @var boolean
     */
    protected $sharedDecorated = false;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/presenter.php' => config_path('presenter.php'),
        ]);

        $this->setComposer($this->app);
        $this->setListener($this->app);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerPresenter($this->app);
    }

    /**
     * Set a composer on the whitelisted views and trigger an event.
     *
     * @param Illuminate\Contracts\Container\Container $app
     */
    public function setComposer(Container $app)
    {
        $app['view']->composer($app['config']->get('presenter.view_whitelist', []), function ($view) use ($app) {
            if ($view instanceof View) {
                $app['events']->fire('presentable-view.rendering', $view);
            }
        });
    }

    /**
     * Set the event listener to listen on view rendering.
     *
     * @param Illuminate\Contracts\Container\Container $app
     */
    protected function setListener(Container $app)
    {
        // $app['events']->listen('presentable-view.rendering.shared', function ($view) use ($presenter, $skip) {
        //     $this->loopAndDecorate($view, $presenter, $skip);
        //     $this->sharedDecorated = true;
        // });

        // Decorate the view data
        $app['events']->listen('presentable-view.rendering', function ($view) use ($app) {
            $data = array_merge($view->getFactory()->getShared(), $view->getData());

            if ($data) {
                foreach ($data as $key => $value) {
                    if (!in_array($key, $app['config']->get('presenter.skip'))) {
                        $view[$key] = $app['continuum.presenter']->present($value);
                    }
                }
            }
        });
    }

    /**
     * Register the presenter instance.
     *
     * @param  Illuminate\Contracts\Container\Container $app
     * @return void
     */
    protected function registerPresenter(Container $app)
    {
        $app->singleton('continuum.presenter', function (Container $app) {
            $presenter = new Presenter();
            $presenter->registerPresentables($app['config']->get('presenter.presentables', []));
            $presenter->registerDecorator(new Decorator($presenter, $app));

            return $presenter;
        });

        $app->bind(Presenter::class, function ($app) {
            return $app['continuum.presenter'];
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'continuum.presenter',
            Presenter::class,
        ];
    }
}
