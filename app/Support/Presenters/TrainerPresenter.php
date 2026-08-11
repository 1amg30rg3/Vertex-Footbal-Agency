<?php

namespace App\Support\Presenters;

use App\Models\Trainer;
use App\Support\RichText;
use Illuminate\Database\Eloquent\Model;

class TrainerPresenter extends Presenter
{
    public function card(Model $trainer): array
    {
        /** @var Trainer $trainer */
        return [
            'id' => $trainer->id,
            'slug' => $trainer->slug,
            'first_name' => $this->t($trainer, 'first_name'),
            'last_name' => $this->t($trainer, 'last_name'),
            'full_name' => $trainer->fullName($this->locale),
            'role' => $this->t($trainer, 'role'),
            'photo' => Trainer::mediaUrl($trainer->photo_path),
            'cover' => Trainer::mediaUrl($trainer->cover_path),
            'nationality' => $trainer->nationality,
            'excerpt' => RichText::excerpt($this->t($trainer, 'bio'), 130),
        ];
    }

    public function detail(Model $trainer): array
    {
        /** @var Trainer $trainer */
        return array_merge($this->card($trainer), [
            'bio' => $this->t($trainer, 'bio'),
            'date_of_birth' => $this->date($trainer->date_of_birth),
            'contact' => array_filter([
                'email' => $trainer->email,
                'phone' => $trainer->phone,
                'instagram' => $trainer->instagram,
                'linkedin' => $trainer->linkedin,
            ], fn ($value) => filled($value)),

            'career' => $trainer->workEntries->map(fn ($entry) => [
                'id' => $entry->id,
                'club_name' => $entry->organization,
                'club_logo' => $entry::mediaUrl($entry->logo_path),
                'started_on' => $this->date($entry->started_on),
                'ended_on' => $this->date($entry->ended_on),
                'is_current' => $entry->isCurrent(),
                'category' => $this->t($entry, 'title'),
                'league' => null,
                'notes' => $this->t($entry, 'notes'),
            ])->values()->all(),

            'seo' => [
                'title' => $this->t($trainer, 'seo_title') ?: $trainer->fullName($this->locale),
                'description' => $this->t($trainer, 'seo_description')
                    ?: RichText::excerpt($this->t($trainer, 'bio')),
                'image' => Trainer::absoluteMediaUrl($trainer->cover_path ?? $trainer->photo_path),
            ],
        ]);
    }
}
