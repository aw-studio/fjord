<?php

namespace Ignite\Application;

use Ignite\Application\Composer\HttpErrorComposer;
use Ignite\Application\Controllers\NotFoundController;
use Ignite\Application\Kernel\HandleRouteMiddleware;
use Ignite\Application\Kernel\HandleViewComposer;
use Ignite\Application\Navigation\PresetFactory;
use Ignite\Application\Providers\ArtisanServiceProvider;
use Ignite\Application\Providers\RouteServiceProvider;
use Ignite\Support\Facades\Config;
use Ignite\Support\Facades\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewClass;

class ApplicationServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(ArtisanServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);

        $this->registerProviders();

        $this->registerVueApplication();

        $this->loadAssets();

        $this->registerNavigationPresetFactoryApplication();
    }

    /**
     * Register providers.
     *
     * @return void
     */
    protected function registerProviders()
    {
        foreach (config('lit.providers') as $provider) {
            $this->app->register($provider);
        }
    }

    /**
     * Register Vue application component.
     *
     * @return void
     */
    protected function registerVueApplication()
    {
        $this->app->singleton('lit.vue.app', function () {
            return new AppComponent;
        });
    }

    /**
     * Register Vue application component.
     *
     * @return void
     */
    protected function registerNavigationPresetFactoryApplication()
    {
        $this->app->singleton('lit.nav', function () {
            return new PresetFactory;
        });
    }

    /**
     * Bootstrap the application services.
     *
     * @param  \Illuminate\Routing\Router $router
     * @return void
     */
    public function boot(Router $router): void
    {
        $this->loadViewsFrom(
            $this->app['lit']->vendorPath('/resources/views'),
            'litstack'
        );

        $this->handleKernel($router);
        $this->litstackErrorPages();
    }

    /**
     * Add css files from config "lit.assets".
     *
     * @return void
     */
    protected function loadAssets()
    {
        $this->app->afterResolving('lit.app', function (Application $app) {
            $styles = config('lit.assets.styles') ?? [];
            foreach ($styles as $style) {
                $app->style($style);
            }

            $scripts = config('lit.assets.scripts') ?? [];

            foreach ($scripts as $script) {
                $app->script($script);
            }
        });
    }

    /**
     * Handle kernel methods "handleRoute" and "handleView".
     *
     * @param  \Illuminate\Routing\Router $router
     * @return void
     */
    protected function handleKernel(Router $router)
    {
        // Kernel method handleRoute gets executed here.
        $router->pushMiddlewareToGroup('web', HandleRouteMiddleware::class);

        // Kernel method handleView gets executed here.
        View::composer('litstack::app', HandleViewComposer::class);
    }

    /**
     * Better Litstack error pages.
     *
     * @return void
     */
    public function litstackErrorPages()
    {
        // Register route {any} after all service providers have been booted to
        // not override other routes.
        $this->app->booted(function () {
            Route::get('{any}', NotFoundController::class)
                ->where('any', '.*')
                ->name('not_found');
        });

        // Set view composer for error pages to use lit error pages when needed.
        View::composer('errors::*', HttpErrorComposer::class);

        // This macro is needed to override the error page view.
        ViewClass::macro('setView', function (string $view) {
            $this->view = $view;
            $this->setPath(view('litstack::app')->getPath());

            return $this;
        });
    }
}
