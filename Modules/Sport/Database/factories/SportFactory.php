<?php

namespace Modules\Sport\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Sport\Entities\Sport;

class SportFactory extends Factory
{
    protected $model = Sport::class;

    public function definition(): array
    {
        return [
            
            'name' => $this->faker->word,
            'photo' => $this->faker->imageUrl(640, 480, 'sports', true), // صورة وهمية
            'max_players_per_team' => $this->faker->numberBetween(5, 15),
        ];

    }
}
