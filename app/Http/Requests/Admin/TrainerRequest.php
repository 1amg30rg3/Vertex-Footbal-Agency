<?php

namespace App\Http\Requests\Admin;

use App\Models\Trainer;
use App\Support\Locales;
use Illuminate\Validation\Rule;

class TrainerRequest extends AdminFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge($this->normalizeTranslatables([
            'first_name', 'last_name', 'role', 'bio', 'seo_title', 'seo_description',
        ]));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $trainer = $this->route('trainer');

        return array_merge(
            $this->translatable('first_name', requiredIn: [Locales::default()]),
            $this->translatable('last_name', requiredIn: [Locales::default()]),
            $this->translatable('role'),
            $this->translatableRichText('bio'),
            $this->translatable('seo_title'),
            $this->translatable('seo_description', ['nullable', 'string', 'max:500']),
            $this->imageRules('photo_path'),
            $this->imageRules('cover_path'),
            [
                'slug' => [
                    'nullable', 'string', 'max:190', 'alpha_dash',
                    Rule::unique('trainers', 'slug')->ignore($trainer?->id),
                ],
                'nationality' => ['nullable', 'string', 'max:100'],
                'date_of_birth' => ['nullable', 'date', 'before:today'],
                'email' => ['nullable', 'email:rfc', 'max:190'],
                'phone' => ['nullable', 'string', 'max:50'],
                'instagram' => ['nullable', 'string', 'max:190'],
                'linkedin' => ['nullable', 'string', 'max:190'],
                'status' => ['required', Rule::in(Trainer::STATUSES)],
                'sort_order' => ['nullable', 'integer', 'min:0'],

                'work' => ['array', 'max:40'],
                'work.*.id' => ['nullable', 'integer'],
                'work.*.organization' => ['required', 'string', 'max:150'],
                'work.*.logo_path' => ['nullable'],
                'work.*.started_on' => ['nullable', 'date'],
                'work.*.ended_on' => ['nullable', 'date', 'after_or_equal:work.*.started_on'],
            ],
            $this->nested('work.*.title'),
            $this->nested('work.*.notes'),
        );
    }

    /** @return array<string, mixed> */
    protected function nested(string $path): array
    {
        $out = [$path => ['nullable', 'array']];

        foreach (Locales::codes() as $code) {
            $out["{$path}.{$code}"] = ['nullable', 'string', 'max:2000'];
        }

        return $out;
    }
}
