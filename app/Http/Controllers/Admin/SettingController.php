<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\EditsTranslations;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use App\Models\User;
use App\Support\Locales;
use App\Support\MediaUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    use EditsTranslations;

    public function edit(Request $request): Response
    {
        $settings = Setting::cached();

        return Inertia::render('Admin/Settings', [
            'settings' => [
                'site_name' => $this->mapRaw($settings['site_name'] ?? null),
                'site_tagline' => $this->mapRaw($settings['site_tagline'] ?? null),
                'copyright' => $this->mapRaw($settings['copyright'] ?? null),
                'contact_address' => $this->mapRaw($settings['contact_address'] ?? null),
                'contact_email' => $settings['contact_email'] ?? null,
                'contact_phone' => $settings['contact_phone'] ?? null,
                'featured_news_limit' => (int) ($settings['featured_news_limit'] ?? 3),
                'logo_path' => $settings['logo_path'] ?? null,
                'logo_url' => Setting::mediaUrl($settings['logo_path'] ?? null),
                'logo_light_path' => $settings['logo_light_path'] ?? null,
                'logo_light_url' => Setting::mediaUrl($settings['logo_light_path'] ?? null),
                'share_image_path' => $settings['share_image_path'] ?? null,
                'share_image_url' => Setting::mediaUrl($settings['share_image_path'] ?? null),
                'socials' => array_values($settings['socials'] ?? []),
            ],
            'users' => $request->user()->isAdmin()
                ? User::query()->orderBy('name')->get()->map(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'last_login_at' => $user->last_login_at?->diffForHumans(),
                    'is_self' => $user->id === $request->user()->id,
                ])->all()
                : [],
            'roles' => User::ROLES,
            'canManageUsers' => $request->user()->isAdmin(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = array_merge(
            $this->translatableRules('site_name'),
            $this->translatableRules('site_tagline'),
            $this->translatableRules('copyright'),
            $this->translatableRules('contact_address'),
            [
                'contact_email' => ['nullable', 'email:rfc', 'max:190'],
                'contact_phone' => ['nullable', 'string', 'max:50'],
                'featured_news_limit' => ['required', 'integer', 'min:1', 'max:12'],
                'logo_path' => ['nullable'],
                'logo_light_path' => ['nullable'],
                'share_image_path' => ['nullable'],
                'socials' => ['array', 'max:12'],
                'socials.*.platform' => ['required', 'string', 'max:40'],
                'socials.*.url' => ['required', 'url', 'max:255'],
            ],
        );

        $data = $request->validate($rules);
        $existing = Setting::cached();

        DB::transaction(function () use ($data, $existing) {
            Setting::put('site_name', Locales::normalizeMap($data['site_name'] ?? []));
            Setting::put('site_tagline', Locales::normalizeMap($data['site_tagline'] ?? []));
            Setting::put('copyright', Locales::normalizeMap($data['copyright'] ?? []));
            Setting::put('contact_address', Locales::normalizeMap($data['contact_address'] ?? []), 'contact');
            Setting::put('contact_email', $data['contact_email'] ?? null, 'contact');
            Setting::put('contact_phone', $data['contact_phone'] ?? null, 'contact');
            Setting::put('featured_news_limit', (int) $data['featured_news_limit']);
            Setting::put('socials', array_values($data['socials'] ?? []), 'contact');
            Setting::put('logo_path', MediaUploader::store(
                $data['logo_path'] ?? null, 'site', $existing['logo_path'] ?? null
            ), 'branding');
            Setting::put('logo_light_path', MediaUploader::store(
                $data['logo_light_path'] ?? null, 'site', $existing['logo_light_path'] ?? null
            ), 'branding');
            Setting::put('share_image_path', MediaUploader::store(
                $data['share_image_path'] ?? null, 'site', $existing['share_image_path'] ?? null
            ), 'branding');
        });

        return back()->with('success', 'Settings saved.');
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::in(User::ROLES)],
        ]);

        $user = User::create($data);

        ActivityLog::record('created', $user, "Created {$user->role} account for {$user->name}");

        return back()->with('success', 'Account created.');
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::in(User::ROLES)],
        ]);

        if ($user->isAdmin() && $data['role'] !== 'admin' && User::where('role', 'admin')->count() === 1) {
            return back()->with('error', 'There must be at least one administrator.');
        }

        $user->fill(array_filter([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => $data['password'] ?? null,
        ], fn ($value) => $value !== null))->save();

        return back()->with('success', 'Account updated.');
    }

    public function destroyUser(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->isAdmin() && User::where('role', 'admin')->count() === 1) {
            return back()->with('error', 'There must be at least one administrator.');
        }

        $user->delete();

        return back()->with('success', 'Account deleted.');
    }

    /** @return array<string, mixed> */
    protected function translatableRules(string $field, int $max = 255): array
    {
        $rules = [$field => ['nullable', 'array']];

        foreach (Locales::codes() as $code) {
            $rules["{$field}.{$code}"] = ['nullable', 'string', "max:{$max}"];
        }

        return $rules;
    }
}
