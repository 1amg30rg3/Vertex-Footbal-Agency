<?php

namespace App\Http\Requests\Admin;

use App\Models\Player;
use App\Support\PlayerMedia;
use App\Support\Locales;
use Illuminate\Validation\Rule;

class PlayerRequest extends AdminFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge($this->normalizeTranslatables([
            'first_name', 'last_name', 'specific_position', 'playing_style',
            'goals_short_term', 'goals_mid_term', 'goals_long_term', 'quote',
            'seo_title', 'seo_description',
        ]));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $player = $this->route('player');

        return array_merge(
            $this->translatable('first_name', requiredIn: [Locales::default()]),
            $this->translatable('last_name', requiredIn: [Locales::default()]),
            $this->translatable('specific_position'),
            $this->imageRules('photo_path'),
            $this->imageRules('cover_path'),
            $this->imageRules('current_club_logo_path'),
            [
                'slug' => [
                    'nullable', 'string', 'max:190', 'alpha_dash',
                    Rule::unique('players', 'slug')->ignore($player?->id),
                ],
                'date_of_birth' => ['nullable', 'date', 'before:today'],
                'nationality' => ['nullable', 'string', 'max:100'],
                'height_cm' => ['nullable', 'integer', 'min:100', 'max:250'],
                'weight_kg' => ['nullable', 'integer', 'min:30', 'max:200'],
                'position' => ['nullable', Rule::in(Player::POSITIONS)],
                'preferred_foot' => ['nullable', Rule::in(Player::FEET)],
                'current_club' => ['nullable', 'string', 'max:150'],
                'contract_until' => ['nullable', 'date'],
                'phone' => ['nullable', 'string', 'max:50'],
                'email' => ['nullable', 'email:rfc', 'max:190'],
                'instagram' => ['nullable', 'string', 'max:190'],
                'city' => ['nullable', 'string', 'max:100'],
                'country' => ['nullable', 'string', 'max:100'],

                'pitch_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
                'pitch_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
                'skills' => ['array', 'max:24'],
                'skills.*.id' => ['nullable', 'integer'],
                'skills.*.value' => ['required', 'integer', 'min:0', 'max:100'],

                'career' => ['array', 'max:40'],
                'career.*.id' => ['nullable', 'integer'],
                'career.*.club_name' => ['required', 'string', 'max:150'],
                'career.*.club_logo_path' => ['nullable'],
                'career.*.started_on' => ['nullable', 'date'],
                'career.*.ended_on' => ['nullable', 'date', 'after_or_equal:career.*.started_on'],
                'career.*.category' => ['nullable', 'string', 'max:80'],

                'achievements' => ['array', 'max:60'],
                'achievements.*.id' => ['nullable', 'integer'],
                'achievements.*.year' => ['nullable', 'string', 'max:20'],

                'seasons' => ['array', 'max:30'],
                'seasons.*.id' => ['nullable', 'integer'],
                'seasons.*.label' => ['required', 'string', 'max:40'],
                'seasons.*.club_name' => ['nullable', 'string', 'max:150'],
                'seasons.*.matches_played' => ['required', 'integer', 'min:0', 'max:2000'],
                'seasons.*.goals' => ['required', 'integer', 'min:0', 'max:2000'],
                'seasons.*.assists' => ['required', 'integer', 'min:0', 'max:2000'],
                'seasons.*.minutes_played' => ['required', 'integer', 'min:0', 'max:200000'],
                'seasons.*.starting_pct' => ['required', 'integer', 'min:0', 'max:100'],
                'seasons.*.substitute_pct' => ['required', 'integer', 'min:0', 'max:100'],
                'seasons.*.not_in_squad_pct' => ['required', 'integer', 'min:0', 'max:100'],
                'seasons.*.is_current' => ['boolean'],
                'seasons.*.months' => ['array', 'max:12'],
                'seasons.*.months.*.month' => ['required', 'integer', 'min:1', 'max:12'],
                'seasons.*.months.*.goals' => ['required', 'integer', 'min:0', 'max:200'],
                'seasons.*.months.*.assists' => ['required', 'integer', 'min:0', 'max:200'],

                'photos' => ['array', 'max:60'],
                'photos.*.id' => ['nullable', 'integer'],
                'photos.*.path' => ['required', function (string $attribute, mixed $value, \Closure $fail) {
                    if (PlayerMedia::isExternal($value) && PlayerMedia::kind($value) === null) {
                        $fail('Paste a YouTube or Vimeo link, or a direct link to a video file.');
                    }
                }],

                'links' => ['array', 'max:20'],
                'links.*.id' => ['nullable', 'integer'],
                'links.*.url' => ['required', 'url:http,https', 'max:2048'],

                'status' => ['required', Rule::in(Player::STATUSES)],
                'sort_order' => ['nullable', 'integer', 'min:0'],
                'is_featured' => ['boolean'],
            ],
            $this->translatableRichText('playing_style'),
            $this->translatableRichText('goals_short_term'),
            $this->translatableRichText('goals_mid_term'),
            $this->translatableRichText('goals_long_term'),
            $this->translatable('quote', ['nullable', 'string', 'max:500']),
            $this->translatable('seo_title'),
            $this->translatable('seo_description', ['nullable', 'string', 'max:500']),
            $this->nestedTranslatable('skills.*.label'),
            $this->nestedTranslatable('career.*.league'),
            $this->nestedTranslatable('career.*.notes'),
            $this->nestedTranslatable('achievements.*.text'),
            $this->nestedTranslatable('photos.*.caption'),
            $this->nestedTranslatable('links.*.label'),
        );
    }

    /** @return array<string, mixed> */
    protected function nestedTranslatable(string $path): array
    {
        $out = [$path => ['nullable', 'array']];

        foreach (Locales::codes() as $code) {
            $out["{$path}.{$code}"] = ['nullable', 'string', 'max:2000'];
        }

        return $out;
    }

    public function playingTimeWarnings(): array
    {
        $warnings = [];

        foreach ($this->input('seasons', []) as $index => $season) {
            $total = (int) ($season['starting_pct'] ?? 0)
                + (int) ($season['substitute_pct'] ?? 0)
                + (int) ($season['not_in_squad_pct'] ?? 0);

            if ($total !== 0 && $total !== 100) {
                $warnings[] = "Season \"{$season['label']}\" playing-time split totals {$total}%, not 100%.";
            }
        }

        return $warnings;
    }
}
