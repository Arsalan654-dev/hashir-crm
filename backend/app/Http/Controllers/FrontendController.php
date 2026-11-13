<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class FrontendController extends Controller
{
    /**
     * Serve the dashboard (index) page.
     */
    public function index(): View
    {
        return view('pages.index');
    }

    /**
     * Serve a static frontend page by slug (e.g. dashboard-sales, ui-alerts).
     */
    public function page(string $slug): View|Response
    {
        $view = 'pages.'.$slug;

        if (! view()->exists($view)) {
            abort(404);
        }

        return view($view);
    }
}
