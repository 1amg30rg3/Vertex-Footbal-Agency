<?php

namespace App\Support\Presenters;

use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Model;

class TeamMemberPresenter extends Presenter
{
    public function card(Model $member): array
    {
        /** @var TeamMember $member */
        return [
            'id' => $member->id,
            'slug' => $member->slug,
            'name' => $this->t($member, 'name'),
            'role' => $this->t($member, 'role'),
            'bio' => $this->t($member, 'bio'),
            'photo' => TeamMember::mediaUrl($member->photo_path),
            'email' => $member->email,
            'phone' => $member->phone,
            'socials' => collect($member->social_links ?? [])
                ->filter(fn ($link) => filled($link['url'] ?? null))
                ->values()
                ->all(),
        ];
    }
}
