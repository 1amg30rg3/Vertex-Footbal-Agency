<?php

namespace App\Http\Requests\Admin;

use App\Support\Locales;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

abstract class AdminFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @param  string  $field  attribute name, e.g. "first_name"
     * @param  list<string>  $rules  rules applied to every locale
     * @param  list<string>  $requiredIn  locales that must not be empty
     * @return array<string, mixed>
     */
    protected function translatable(string $field, array $rules = ['nullable', 'string', 'max:255'], array $requiredIn = []): array
    {
        $out = [$field => ['nullable', 'array']];

        foreach (Locales::codes() as $code) {
            $out["{$field}.{$code}"] = in_array($code, $requiredIn, true)
                ? array_merge(['required'], array_values(array_diff($rules, ['nullable'])))
                : $rules;
        }

        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    protected function translatableRichText(string $field, array $requiredIn = []): array
    {
        return $this->translatable($field, ['nullable', 'string', 'max:100000'], $requiredIn);
    }

    /**
     * @return array<string, mixed>
     */
    protected function imageRules(string $field): array
    {
        return [$field => ['nullable']];
    }

    /**
     * Give locale-suffixed keys a readable name, so a message reads
     * "First name (GEO) is required." instead of "The first_name.ka field ...".
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $codes = implode('|', array_map('preg_quote', Locales::codes()));
        $out = [];

        foreach (array_keys($this->rules()) as $key) {
            if (preg_match('/^(?<field>.+)\.(?<locale>'.$codes.')$/', $key, $m)) {
                $label = Locales::find($m['locale'])['label'] ?? strtoupper($m['locale']);
                $out[$key] = $this->humanize($m['field'])." ({$label})";

                continue;
            }

            if (str_contains($key, '.') || str_contains($key, '_')) {
                $out[$key] = $this->humanize($key);
            }
        }

        return $out;
    }

    /**
     * "seasons.*.label" => "Season label", "career.*.club_name" => "Career club name".
     */
    protected function humanize(string $key): string
    {
        $words = collect(explode('.', $key))
            ->reject(fn (string $part) => $part === '*')
            ->flatMap(fn (string $part, int $index) => $index === 0
                ? [Str::singular($part)]
                : [$part])
            ->flatMap(fn (string $part) => explode('_', $part))
            ->filter()
            ->implode(' ');

        return ucfirst(strtolower($words));
    }

    protected function normalizeTranslatables(array $fields): array
    {
        $data = [];

        foreach ($fields as $field) {
            if ($this->has($field)) {
                $data[$field] = Locales::normalizeMap($this->input($field));
            }
        }

        return $data;
    }
}
