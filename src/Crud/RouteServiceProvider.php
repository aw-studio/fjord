<?php

namespace Fjord\Crud;

use ReflectionClass;
use Illuminate\Support\Str;
use Illuminate\Routing\Route;
use Fjord\Support\Facades\Crud;
use Fjord\Crud\Config\CrudConfig;
use Fjord\Crud\Config\FormConfig;
use Fjord\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as LaravelRouteServiceProvider;

class RouteServiceProvider extends LaravelRouteServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->macros();
    }

    /**
     * Register macros.
     *
     * @return void
     */
    public function macros()
    {
        RouteFacade::macro('config', function ($class) {
            $this->config = $class;

            if (isset($this->groupStack[0])) {
                $this->groupStack[0]['config'] = $this->config;
            }
            return $this;
        });

        Route::macro('config', function ($config) {
            $this->action['config'] = $config;

            return $this;
        });

        Route::macro('getConfig', function () {
            $key = $this->action['config'] ?? null;
            if (!$key) {
                return;
            }
            return fjord()->config($key);
        });
    }

    /**
     * Map routes.
     *
     * @return void
     */
    public function map()
    {
        $this->mapCrudRoutes();
        $this->mapFormRoutes();
    }

    /**
     * Map crud routes.
     *
     * @return void
     */
    protected function mapCrudRoutes()
    {
        if (!fjord()->installed()) {
            return;
        }

        $files = File::allFiles(fjord_config_path());

        foreach ($files as $file) {
            if ($file->isDir()) {
                continue;
            }

            if (!Str::contains($file, '.php')) {
                continue;
            }

            $namespace = str_replace("/", "\\", "FjordApp" . explode('fjord/app', str_replace('.php', '', $file))[1]);
            $reflection = new ReflectionClass($namespace);

            if (!$reflection->getParentClass()) {
                continue;
            }

            if ($reflection->getParentClass()->name != CrudConfig::class) {
                continue;
            }

            $config = Config::getFromPath($file);

            if (!$config) {
                continue;
            }

            Crud::routes(
                $config
            );
        }
    }

    /**
     * Map form routes.
     *
     * @return void
     */
    protected function mapFormRoutes()
    {
        $configPath = fjord_config_path('Form');
        $directories = glob($configPath . '/*', GLOB_ONLYDIR);

        foreach ($directories as $formDirectory) {
            $configFiles = glob("{$formDirectory}/*.php");;
            foreach ($configFiles as $path) {
                $configKey = collect(config_parser($path))
                    ->map(fn ($item) => Str::snake($item))
                    ->implode('.');

                $config = Config::get($configKey);
                if (!$config) {
                    continue;
                }

                if (!$config->getConfig() instanceof FormConfig) {
                    continue;
                }

                Crud::formRoutes($config);
            }
        }
    }
}
