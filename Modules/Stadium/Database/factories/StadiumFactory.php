<?php

namespace Modules\Stadium\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Stadium\Entities\Stadium;
use Modules\Sport\Entities\Sport;
use App\Models\User;

class StadiumFactory extends Factory
{
    protected $model = Stadium::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'sport_id' => Sport::factory(),
            'name' => $this->faker->company,
            'location' => $this->faker->address,
            'description' => $this->faker->sentence,
            'photos' => null,
            'Length' => $this->faker->randomFloat(2, 30, 120),
            'Width' => $this->faker->randomFloat(2, 20, 80),
            'owner_number' => $this->faker->numerify('##########'),
            'start_time' => '08:00:00',
            'end_time' => '22:00:00',
            'price' => $this->faker->randomFloat(2, 20, 200),
            'deposit' => $this->faker->randomFloat(2, 5, 50),
            'duration' => 60,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
        ];
    }
}
