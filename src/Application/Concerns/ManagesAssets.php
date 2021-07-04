<?php

namespace Ignite\Application\Concerns;

use Ignite\Support\Facades\Route;
use Ignite\Support\FileResponse;

trait ManagesAssets
{
    /**
     * Included css files..
     *
     * @var array
     */
    protected $styles = [];

    /**
     * Included scripts.
     *
     * @var array
     */
    protected $scripts = [];

    /**
     * Included login scripts.
     *
     * @var array
     */
    protected $loginScripts = [];

    /**
     * Add script to the application.
     *
     * @param  string $src
     * @return $this
     */
    public function script($src)
    {
        if (in_array($src, $this->scripts)) {
            return;
        }

        $this->scripts[] = $this->resolveAssetPath($src);

        return $this;
    }

    /**
     * Add script to the application.
     *
     * @param  string $src
     * @return $this
     */
    public function loginScript($src)
    {
        if (in_array($src, $this->loginScripts)) {
            return;
        }

        $this->loginScripts[] = $this->resolveAssetPath($src);

        return $this;
    }

    /**
     * Add css file to the application.
     *
     * @param  string $path
     * @return $this
     */
    public function style($path)
    {
        if (in_array($path, $this->styles)) {
            return;
        }

        $this->styles[] = $this->resolveAssetPath($path);

        return $this;
    }

    /**
     * Get styles.
     *
     * @return array
     */
    public function getStyles()
    {
        return $this->styles;
    }

    /**
     * Get scripts.
     *
     * @return array
     */
    public function getScripts()
    {
        return $this->scripts;
    }

    /**
     * Get login scripts.
     *
     * @return array
     */
    public function getLoginScripts()
    {
        return $this->loginScripts;
    }

    /**
     * Resolve path to asset.
     *
     * @param  string $path
     * @return void
     */
    protected function resolveAssetPath($path)
    {
        if (! file_exists($path)) {
            return $path;
        }

        $info = pathinfo($path);

        $uri = implode('/', [
            $info['extension'] ?? 'dist',
            $info['basename'],
        ]);

        $route = Route::public()->get($uri, fn () => new FileResponse($path));

        return url($route->uri);
    }
}
