<?php

namespace App\Http\Requests\Admin;

use App\Models\TeamMember;
use App\Support\Locales;
use Illuminate\Validation\Rule;

class TeamMemberRequest extends AdminFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge($this->normalizeTranslatables(['name', 'role', 'bio']));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $member = $this->route('member');

        return array_merge(
            $this->translatable('name', requiredIn: [Locales::default()]),
            $this->translatable('role'),
            $this->translatableRichText('bio'),
            $this->imageRules('photo_path'),
            [
                'slug' => [
                    'nullable', 'string', 'max:190', 'alpha_dash',
                    Rule::unique('team_members', 'slug')->ignore($member?->id),
                ],
                'email' => ['nullable', 'email:rfc', 'max:190'],
                'phone' => ['nullable', 'string', 'max:50'],
                'status' => ['required', Rule::in(TeamMember::STATUSES)],
                'sort_order' => ['nullable', 'integer', 'min:0'],
                'social_links' => ['array', 'max:10'],
                'social_links.*.platform' => ['required', 'string', 'max:40'],
                'social_links.*.url' => ['required', 'url', 'max:255'],
            ],
        );
    }
}
