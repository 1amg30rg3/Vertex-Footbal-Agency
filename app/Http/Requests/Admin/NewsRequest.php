<?php

namespace App\Http\Requests\Admin;

use App\Models\News;
use App\Support\Locales;
use Illuminate\Validation\Rule;

class NewsRequest extends AdminFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge($this->normalizeTranslatables([
            'title', 'excerpt', 'body', 'seo_title', 'seo_description',
        ]));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $article = $this->route('news');

        return array_merge(
            $this->translatable('title', ['nullable', 'string', 'max:255'], [Locales::default()]),
            $this->translatable('excerpt', ['nullable', 'string', 'max:600']),
            $this->translatableRichText('body'),
            $this->translatable('seo_title'),
            $this->translatable('seo_description', ['nullable', 'string', 'max:500']),
            $this->imageRules('cover_path'),
            [
                'slug' => [
                    'nullable', 'string', 'max:190', 'alpha_dash',
                    Rule::unique('news', 'slug')->ignore($article?->id),
                ],
                'external_url' => ['nullable', 'url:http,https', 'max:2048'],
                'news_category_id' => ['nullable', 'integer', 'exists:news_categories,id'],
                'status' => ['required', Rule::in(News::STATUSES)],
                'published_at' => ['nullable', 'date', Rule::requiredIf(fn () => $this->input('status') === 'scheduled')],
                'is_featured' => ['boolean'],
                'featured_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            ],
        );
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'published_at.required' => 'A scheduled article needs a publish date and time.',
        ];
    }
}
