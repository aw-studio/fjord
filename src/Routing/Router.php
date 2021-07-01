<?php

namespace Ignite\Routing;

use Closure;
use Illuminate\Support\Facades\Route;

class Router
{
    /**
     * Public middlewares.
     *
     * @var array
     */
    protected $publicMiddlewares = [
        'web',
    ];

    /**
     * The Closure that is used to prepare litstack routes.
     *
     * @var Closure
     */
    protected $preparing;

    /**
     * Create new Router instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->prepareUsing(function () {
            $route = Route::prefix(config('lit.route_prefix'));

            if ($domain = config('lit.domain')) {
                $route->domain($domain);
            }

            return $route;
        });
    }

    /**
     * Prepare litstack routes using the given Closure.
     *
     * @param  Closure $closure
     * @return $this
     */
    public function prepareUsing(Closure $closure)
    {
        $this->preparing = $closure;

        return $this;
    }

    /**
     * Get middlewares.
     *
     * @return array
     */
    protected function getMiddlewares()
    {
        return app(\Ignite\Application\Kernel::class)->getMiddlewares();
    }

    /**
     * Get public middlewares.
     *
     * @return array
     */
    protected function getPublicMiddlewares()
    {
        return app(\Ignite\Application\Kernel::class)->getPublicMiddlewares();
    }

    /**
     * Get guest middlewares.
     *
     * @return array
     */
    protected function getGuestMiddlewares()
    {
        return app(\Ignite\Application\Kernel::class)->getGuestMiddlewares();
    }

    /**
     * Get route preset.
     *
     * @return \Illuminate\Support\Facades\Route
     */
    protected function getRoutePreset()
    {
        return $this->route()
            ->as('lit.')
            ->middleware($this->getMiddelwares());
    }

    /**
     * Get the initial route.
     *
     * @return Route
     */
    public function route()
    {
        return call_user_func($this->preparing);
    }

    /**
     * Create a route group with shared attributes.
     *
     * @param  Closure|array|string $attributes
     * @param  Closure              $closure
     * @return void
     */
    public function group($attributes, Closure $closure = null)
    {
        if (is_callable($attributes) || is_string($attributes)) {
            return $this->getRoutePreset()->group($attributes);
        }

        $attributes['prefix'] = config('lit.route_prefix').'/'.($attributes['prefix'] ?? '');
        $attributes['as'] = 'lit.'.($attributes['as'] ?? '');
        $attributes['middleware'] = array_merge($attributes['middlewares'] ?? [], $this->getMiddelwares());
        Route::group($attributes, $closure);
    }

    /**
     * Public route using Lit route prefix.
     *
     * @return Route
     */
    public function public()
    {
        return $this->route()
            ->as('lit.')
            ->middleware($this->getPublicMiddlewares());
    }

    /**
     * Guest route using Lit route prefix.
     *
     * @return Route
     */
    public function guest()
    {
        return $this->route()
            ->as('lit.')
            ->middleware($this->getGuestMiddlewares());
    }

    /**
     * Initialize defaults for a Lit route.
     * Lit Routes should always be created
     * with \Ignite\Support\Facades\Route.
     *
     * @return \Illuminate\Support\Facades\Route
     */
    public function __call($method, $parameters)
    {
        $route = $this->getRoutePreset();

        return $route->$method(...$parameters);
    }
}
