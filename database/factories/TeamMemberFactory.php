<?php

namespace Database\Factories;

use App\Models\TeamMember;
use App\Support\Locales;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeamMember>
 */
class TeamMemberFactory extends Factory
{
    protected $model = TeamMember::class;

    public function definition(): array
    {
        return [
            'name' => $this->translated(fake()->name()),
            'role' => $this->translated(fake()->randomElement([
                'Player Agent', 'Head of Scouting', 'Legal Counsel', 'Communications Manager',
            ])),
            'bio' => $this->translated('<p>'.fake()->paragraph(3).'</p>'),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'social_links' => [['platform' => 'linkedin', 'url' => 'https://linkedin.com/']],
            'status' => 'published',
            'sort_order' => 0,
        ];
    }

    protected function translated(string $value): array
    {
        return array_fill_keys(Locales::codes(), $value);
    }
}
