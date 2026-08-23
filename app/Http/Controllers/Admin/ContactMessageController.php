<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContactMessageController extends Controller
{
    public function index(Request $request): Response
    {
        $messages = ContactMessage::query()
            ->search($request->string('search')->toString() ?: null)
            ->when($request->string('state')->toString() === 'unread', fn ($q) => $q->unread())
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Messages/Index', [
            'messages' => [
                'data' => collect($messages->items())->map(fn (ContactMessage $message) => [
                    'id' => $message->id,
                    'name' => $message->name,
                    'email' => $message->email,
                    'phone' => $message->phone,
                    'subject' => $message->subject,
                    'message' => $message->message,
                    'locale' => $message->locale,
                    'is_read' => $message->is_read,
                    'created_at' => $message->created_at?->format('Y-m-d H:i'),
                    'at' => $message->created_at?->diffForHumans(),
                ])->all(),
                'meta' => $this->paginationMeta($messages),
            ],
            'filters' => $request->only('search', 'state'),
            'unreadCount' => ContactMessage::unread()->count(),
        ]);
    }

    public function markRead(ContactMessage $message): RedirectResponse
    {
        $message->markRead();

        return back();
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $message->delete();

        return back()->with('success', 'Message deleted.');
    }
}
