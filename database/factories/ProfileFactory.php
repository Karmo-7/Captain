<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'team_id' => null,
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'birthdate' => $this->faker->date(),
            'address' => $this->faker->address(),
            'phone_number' => $this->faker->unique()->numerify('##########'),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'height' => $this->faker->numberBetween(150, 200),
            'weight' => $this->faker->numberBetween(50, 100),
            'Sport' => $this->faker->randomElement(['Football', 'Basketball', 'Tennis']),
            'positions_played' => $this->faker->word(),
            'notable_achievements' => $this->faker->sentence(),
            'years_of_experience' => $this->faker->numberBetween(0, 10),
            'previous_teams' => $this->faker->company(),
            'avatar' => null,
        ];
    }
}
