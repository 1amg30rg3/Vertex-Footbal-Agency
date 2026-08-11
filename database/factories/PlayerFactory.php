<?php

namespace Database\Factories;

use App\Models\Player;
use App\Support\Locales;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Player>
 */
class PlayerFactory extends Factory
{
    protected $model = Player::class;

    public function definition(): array
    {
        $first = fake()->firstNameMale();
        $last = fake()->lastName();

        return [
            'first_name' => $this->translated($first),
            'last_name' => $this->translated($last),
            'date_of_birth' => fake()->dateTimeBetween('-34 years', '-17 years')->format('Y-m-d'),
            'nationality' => fake()->country(),
            'height_cm' => fake()->numberBetween(165, 198),
            'weight_kg' => fake()->numberBetween(62, 92),
            'position' => fake()->randomElement(Player::POSITIONS),
            'specific_position' => $this->translated(fake()->randomElement([
                'Central Midfielder', 'Left Back', 'Second Striker', 'Sweeper Keeper',
            ])),
            'preferred_foot' => fake()->randomElement(Player::FEET),
            'current_club' => fake()->company().' FC',
            'contract_until' => fake()->dateTimeBetween('now', '+3 years')->format('Y-m-d'),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'instagram' => '@'.fake()->userName(),
            'city' => fake()->city(),
            'country' => fake()->country(),
            'playing_style' => $this->translated('<p>'.fake()->paragraph(4).'</p>'),
            'pitch_x' => fake()->randomFloat(1, 15, 85),
            'pitch_y' => fake()->randomFloat(1, 15, 85),
            'goals_short_term' => $this->translated('<ul><li>'.fake()->sentence().'</li></ul>'),
            'goals_mid_term' => $this->translated('<ul><li>'.fake()->sentence().'</li></ul>'),
            'goals_long_term' => $this->translated('<ul><li>'.fake()->sentence().'</li></ul>'),
            'quote' => $this->translated(fake()->sentence(8)),
            'status' => 'published',
            'sort_order' => 0,
            'is_featured' => fake()->boolean(30),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft']);
    }

    protected function translated(string $value): array
    {
        return array_fill_keys(Locales::codes(), $value);
    }
}
