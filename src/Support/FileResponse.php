<?php

namespace Ignite\Support;

use Carbon\CarbonInterface;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FileResponse implements Responsable
{
    /**
     * Path to the file.
     *
     * @var string
     */
    protected $path;

    /**
     * Expires time.
     *
     * @var CarbonInterface
     */
    protected $expires;

    /**
     * The time the file was last modified.
     *
     * @var CarbonInterface
     */
    protected $modified;

    /**
     * Create new FileResponse instance.
     *
     * @param  string          $path
     * @param  CarbonInterface $expired
     * @return void
     */
    public function __construct($path, CarbonInterface $expires = null)
    {
        $this->path = $path;
        $this->expires = $expires ?: now()->addYear();
    }

    /**
     * The time the file was last modified.
     *
     * @return CarbonInterface
     */
    public function lastModified()
    {
        return $this->modified
            ?: $this->modified = Carbon::createFromTimestamp(
                filemtime($this->path), config('app.timezone')
            );
    }

    /**
     * Get mime type.
     *
     * @return string|null
     */
    public function getMimeType()
    {
        if (! File::exists($this->path)) {
            return;
        }

        $info = pathinfo($this->path);

        if (! array_key_exists('extension', $info)) {
            return 'text/plain';
        }

        return [
            'js'  => 'application/javascript',
            'png' => 'image/png',
        ][$info['extension']] ?? "text/{$info['extension']}";
    }

    /**
     * Determine if cache matches.
     *
     * @return bool
     */
    public function matchesCache()
    {
        return @strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '') === $this->lastModified()->timestamp;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request                   $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        if (! File::exists($this->path)) {
            throw new NotFoundHttpException();
        }

        $cacheControl = 'public, max-age=31536000';

        if ($this->matchesCache()) {
            return response()->make('', 304, [
                'Expires'       => $this->expires->toRfc7231String(),
                'Cache-Control' => $cacheControl,
            ]);
        }

        return response()->file($this->path, [
            'Content-Type'  => $this->getMimeType().'; charset=utf-8',
            'Expires'       => $this->expires->toRfc7231String(),
            'Cache-Control' => $cacheControl,
            'Last-Modified' => $this->lastModified()->toRfc7231String(),
        ]);
    }
}
