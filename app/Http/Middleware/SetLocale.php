<?php

namespace App\Http\Middleware;

use App\Support\Locales;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        if (! Locales::supports($locale)) {
            $locale = $this->negotiate($request);
        }

        app()->setLocale($locale);
        URL::defaults(['locale' => $locale]);
        $request->attributes->set('locale', $locale);

        return $next($request);
    }

    protected function negotiate(Request $request): string
    {
        $remembered = $request->cookie('site_locale');

        if (Locales::supports($remembered)) {
            return $remembered;
        }

        foreach ($this->browserLocales($request) as $candidate) {
            if (Locales::supports($candidate)) {
                return $candidate;
            }
        }

        return Locales::default();
    }

    /** @return list<string> */
    protected function browserLocales(Request $request): array
    {
        return collect(explode(',', (string) $request->header('Accept-Language')))
            ->map(fn (string $part) => strtolower(trim(explode(';', $part)[0])))
            ->map(fn (string $tag) => explode('-', $tag)[0])
            ->filter()
            ->values()
            ->all();
    }
}
