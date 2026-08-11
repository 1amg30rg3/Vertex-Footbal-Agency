<?php

namespace Database\Factories;

use App\Models\Trainer;
use App\Support\Locales;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trainer>
 */
class TrainerFactory extends Factory
{
    protected $model = Trainer::class;

    public function definition(): array
    {
        return [
            'first_name' => $this->translated(fake()->firstName()),
            'last_name' => $this->translated(fake()->lastName()),
            'role' => $this->translated(fake()->randomElement([
                'Head Coach', 'Goalkeeping Coach', 'Fitness Coach', 'Technical Director',
            ])),
            'bio' => $this->translated('<p>'.fake()->paragraph(5).'</p>'),
            'nationality' => fake()->country(),
            'date_of_birth' => fake()->dateTimeBetween('-65 years', '-30 years')->format('Y-m-d'),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'status' => 'published',
            'sort_order' => 0,
        ];
    }

    protected function translated(string $value): array
    {
        return array_fill_keys(Locales::codes(), $value);
    }
}
