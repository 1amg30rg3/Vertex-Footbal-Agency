<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ContactMessage;
use App\Models\News;
use App\Models\Player;
use App\Models\TeamMember;
use App\Models\Trainer;
use App\Support\Locales;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => $this->stats(),
            'activity' => ActivityLog::query()
                ->with('user:id,name')
                ->latest()
                ->limit(12)
                ->get()
                ->map(fn (ActivityLog $log) => [
                    'id' => $log->id,
                    'action' => $log->action,
                    'subject' => $log->subjectLabel(),
                    'description' => $log->description,
                    'user' => $log->user?->name,
                    'at' => $log->created_at?->diffForHumans(),
                ])->all(),
            'recentMessages' => ContactMessage::query()
                ->latest()
                ->limit(5)
                ->get(['id', 'name', 'email', 'subject', 'is_read', 'created_at'])
                ->map(fn (ContactMessage $message) => [
                    'id' => $message->id,
                    'name' => $message->name,
                    'email' => $message->email,
                    'subject' => $message->subject,
                    'is_read' => $message->is_read,
                    'at' => $message->created_at?->diffForHumans(),
                ])->all(),
            'expiringContracts' => Player::query()
                ->published()
                ->whereNotNull('contract_until')
                ->whereBetween('contract_until', [now(), now()->addMonths(6)])
                ->orderBy('contract_until')
                ->limit(5)
                ->get()
                ->map(fn (Player $player) => [
                    'id' => $player->id,
                    'name' => $player->fullName(Locales::default()),
                    'photo' => Player::mediaUrl($player->photo_path),
                    'contract_until' => $player->contract_until?->format('d M Y'),
                ])->all(),
        ]);
    }

    protected function stats(): array
    {
        return [
            'players' => [
                'total' => Player::count(),
                'published' => Player::where('status', 'published')->count(),
                'draft' => Player::where('status', 'draft')->count(),
            ],
            'trainers' => [
                'total' => Trainer::count(),
                'published' => Trainer::where('status', 'published')->count(),
                'draft' => Trainer::where('status', 'draft')->count(),
            ],
            'team' => [
                'total' => TeamMember::count(),
                'published' => TeamMember::where('status', 'published')->count(),
                'draft' => TeamMember::where('status', 'draft')->count(),
            ],
            'news' => [
                'total' => News::count(),
                'published' => News::where('status', 'published')->count(),
                'draft' => News::where('status', 'draft')->count(),
                'scheduled' => News::where('status', 'scheduled')->count(),
                'featured' => News::where('is_featured', true)->count(),
            ],
            'messages' => [
                'total' => ContactMessage::count(),
                'unread' => ContactMessage::unread()->count(),
            ],
        ];
    }
}
