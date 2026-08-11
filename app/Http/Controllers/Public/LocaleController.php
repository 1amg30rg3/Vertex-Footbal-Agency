<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Support\Locales;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function redirectToLocalizedHome(Request $request): RedirectResponse
    {
        $locale = $request->attributes->get('locale', Locales::default());

        return redirect()->route('public.home', ['locale' => $locale]);
    }

    public function remember(Request $request, string $locale): RedirectResponse
    {
        abort_unless(Locales::supports($locale), 404);

        return redirect()
            ->route('public.home', ['locale' => $locale])
            ->withCookie(cookie()->forever('site_locale', $locale));
    }
}
