<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use App\Support\Locales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $locale = app()->getLocale();
        $isAdmin = $request->is('admin', 'admin/*');

        $uiLocale = $isAdmin ? config('localization.admin', 'en') : $locale;

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user()?->only(['id', 'name', 'email', 'role', 'theme']),
            ],

            'locale' => $uiLocale,
            'contentLocale' => $locale,
            'defaultLocale' => Locales::default(),
            'locales' => Locales::forSwitcher(),
            'translations' => fn () => $this->translations($uiLocale),

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'info' => fn () => $request->session()->get('info'),
            ],

            'site' => fn () => Setting::publicPayload(),

            'ziggy' => fn () => [
                'location' => $request->url(),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function translations(string $locale): array
    {
        $load = function (string $code): array {
            $path = lang_path("{$code}/ui.php");

            return File::exists($path) ? (array) require $path : [];
        };

        return array_replace_recursive($load(Locales::default()), $load($locale));
    }
}
