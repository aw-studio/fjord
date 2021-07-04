<?php

namespace Ignite\Application\Controllers;

use Ignite\Support\FileResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class FileController extends Controller
{
    /**
     * app.js.
     *
     * @return Response
     */
    public function litJs()
    {
        return new FileResponse(lit_vendor_path('public/js/app.js'));
    }

    /**
     * app2.js.
     *
     * @return Response
     */
    public function lit2Js()
    {
        return new FileResponse(lit_vendor_path('public/js/app2.js'));
    }

    /**
     * prism.js.
     *
     * @return Response
     */
    public function prismJs()
    {
        return new FileResponse(lit_vendor_path('public/js/prism.js'));
    }

    /**
     * app.css.
     *
     * @return Response
     */
    public function litCss()
    {
        return new FileResponse(lit_vendor_path('public/css/app.css'));
    }

    /**
     * lit-logo.png.
     *
     * @return Response
     */
    public function litLogo()
    {
        return new FileResponse(lit_vendor_path('public/images/logo.png'));
    }

    /**
     * favicon-32x32.png.
     *
     * @return Response
     */
    public function litFaviconBig()
    {
        return new FileResponse(lit_vendor_path('public/favicon/favicon-32x32.png'));
    }

    /**
     * favicon-16x16.png.
     *
     * @return Response
     */
    public function litFaviconSmall()
    {
        return new FileResponse(lit_vendor_path('public/favicon/favicon-16x16.png'));
    }
}
