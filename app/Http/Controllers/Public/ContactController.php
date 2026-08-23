<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function index(): Response
    {
        $this->seo()->title(__('ui.contacts.title'))->description(__('ui.contacts.lead'));

        return Inertia::render('Public/Contacts');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureNotFlooding($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40', 'regex:/^\\+?[0-9 ()\\-]{6,}$/'],
            'subject' => ['nullable', 'string', 'max:190'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'website' => ['prohibited'],
        ], [
            'website.prohibited' => 'Your submission could not be processed.',
        ]);

        ContactMessage::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],
            'locale' => app()->getLocale(),
            'ip_address' => $request->ip(),
        ]);

        RateLimiter::hit($this->throttleKey($request), 3600);

        return back()->with('success', __('ui.contacts.success'));
    }

    protected function ensureNotFlooding(Request $request): void
    {
        if (RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            throw ValidationException::withMessages([
                'message' => __('Too many messages sent. Please try again later.'),
            ]);
        }
    }

    protected function throttleKey(Request $request): string
    {
        return 'contact:'.$request->ip();
    }
}
