<?php

namespace App\Http\Requests\Admin;

use App\Support\Locales;
use Illuminate\Validation\Rule;

class NewsCategoryRequest extends AdminFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge($this->normalizeTranslatables(['name']));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $category = $this->route('category');

        return array_merge(
            $this->translatable('name', requiredIn: [Locales::default()]),
            [
                'slug' => [
                    'nullable', 'string', 'max:190', 'alpha_dash',
                    Rule::unique('news_categories', 'slug')->ignore($category?->id),
                ],
                'color' => ['nullable', 'string', 'max:20'],
                'sort_order' => ['nullable', 'integer', 'min:0'],
            ],
        );
    }
}
