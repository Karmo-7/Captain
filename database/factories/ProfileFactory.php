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
            'user_id' => User::factory(), // ينشئ مستخدم تلقائياً
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'birthdate' => $this->faker->date('Y-m-d'),
            'address' => $this->faker->address,
            'phone_number' => $this->faker->phoneNumber,
            'gender' => $this->faker->randomElement(['male', 'female']),
            //'mine' => $this->faker->randomElement(['player', 'coach']),
            'height' => $this->faker->numberBetween(160, 200),
            'weight' => $this->faker->numberBetween(50, 100),
            'years_of_experience' => $this->faker->numberBetween(1, 10),
            'avatar' => null, // أو مسار وهمي إن احتجت
        ];
    }
}
