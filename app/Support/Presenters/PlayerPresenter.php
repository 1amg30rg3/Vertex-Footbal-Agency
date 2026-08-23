<?php

namespace App\Support\Presenters;

use App\Models\Player;
use App\Models\PlayerSeason;
use App\Support\PlayerMedia;
use App\Support\RichText;
use Illuminate\Database\Eloquent\Model;

class PlayerPresenter extends Presenter
{
    public function card(Model $player): array
    {
        /** @var Player $player */
        return [
            'id' => $player->id,
            'slug' => $player->slug,
            'first_name' => $this->t($player, 'first_name'),
            'last_name' => $this->t($player, 'last_name'),
            'full_name' => $player->fullName($this->locale),
            'photo' => Player::mediaUrl($player->photo_path),
            'cover' => Player::mediaUrl($player->cover_path),
            'position' => $player->position,
            'specific_position' => $this->t($player, 'specific_position'),
            'nationality' => $player->nationality,
            'current_club' => $player->current_club,
            'current_club_logo' => Player::mediaUrl($player->current_club_logo_path),
            'age' => $player->age,
            'is_featured' => $player->is_featured,
        ];
    }

    public function detail(Model $player): array
    {
        /** @var Player $player */
        return array_merge($this->card($player), [
            'date_of_birth' => $this->date($player->date_of_birth),
            'height_cm' => $player->height_cm,
            'weight_kg' => $player->weight_kg,
            'preferred_foot' => $player->preferred_foot,
            'contract_until' => $this->date($player->contract_until),
            'contact' => array_filter([
                'phone' => $player->phone,
                'email' => $player->email,
                'instagram' => $player->instagram,
                'city' => $player->city,
                'country' => $player->country,
            ], fn ($value) => filled($value)),

            'playing_style' => $this->t($player, 'playing_style'),
            'pitch' => collect($player->pitch_positions ?? [])
                ->map(fn ($spot) => ['x' => (float) $spot['x'], 'y' => (float) $spot['y']])
                ->values()
                ->all(),
            'skills' => $player->skills->map(fn ($skill) => [
                'id' => $skill->id,
                'label' => $this->t($skill, 'label'),
                'value' => $skill->value,
            ])->values()->all(),

            'career' => $player->careerEntries->map(fn ($entry) => [
                'id' => $entry->id,
                'club_name' => $entry->club_name,
                'club_logo' => $entry::mediaUrl($entry->club_logo_path),
                'started_on' => $this->date($entry->started_on),
                'ended_on' => $this->date($entry->ended_on),
                'is_current' => $entry->isCurrent(),
                'category' => $entry->category,
                'league' => $this->t($entry, 'league'),
                'notes' => $this->t($entry, 'notes'),
            ])->values()->all(),
            'achievements' => $player->achievements->map(fn ($item) => [
                'id' => $item->id,
                'text' => $this->t($item, 'text'),
                'year' => $item->year,
            ])->values()->all(),

            'seasons' => $player->seasons->map(fn (PlayerSeason $season) => [
                'id' => $season->id,
                'label' => $season->label,
                'club_name' => $season->club_name,
                'is_current' => $season->is_current,
                'matches_played' => $season->matches_played,
                'goals' => $season->goals,
                'assists' => $season->assists,
                'minutes_played' => $season->minutes_played,
                'playing_time' => $season->playingTimeSplit(),
                'months' => $season->months->map(fn ($month) => [
                    'month' => $month->month,
                    'goals' => $month->goals,
                    'assists' => $month->assists,
                ])->values()->all(),
            ])->values()->all(),
            'totals' => $player->careerTotals(),

            'photos' => $player->photos->map(fn ($photo) => [
                'id' => $photo->id,
                'url' => $photo::mediaUrl($photo->path),
                'kind' => PlayerMedia::kind($photo->path),
                'embed_url' => PlayerMedia::embedUrl($photo->path),
                'poster' => PlayerMedia::posterUrl($photo->path),
                'caption' => $this->t($photo, 'caption'),
            ])->values()->all(),

            'links' => $player->links->map(fn ($link) => [
                'id' => $link->id,
                'label' => $this->t($link, 'label'),
                'url' => $link->url,
            ])->values()->all(),

            'goals' => array_filter([
                'short_term' => $this->t($player, 'goals_short_term'),
                'mid_term' => $this->t($player, 'goals_mid_term'),
                'long_term' => $this->t($player, 'goals_long_term'),
            ], fn ($value) => filled($value)),
            'quote' => $this->t($player, 'quote'),

            'seo' => [
                'title' => $this->t($player, 'seo_title') ?: $player->fullName($this->locale),
                'description' => $this->t($player, 'seo_description')
                    ?: RichText::excerpt($this->t($player, 'playing_style')),
                'image' => Player::absoluteMediaUrl($player->cover_path ?? $player->photo_path),
            ],
        ]);
    }
}
