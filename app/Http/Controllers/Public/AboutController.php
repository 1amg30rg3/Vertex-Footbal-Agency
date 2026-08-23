<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class AboutController extends Controller
{
    public function __invoke(): Response
    {
        $this->seo()->title(__('ui.about.title'))->description(__('ui.about.lead'));

        return Inertia::render('Public/About');
    }
}
