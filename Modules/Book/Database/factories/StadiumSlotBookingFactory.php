<?php

namespace Modules\Book\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Book\Entities\StadiumSlotBooking;
use Modules\Stadium\Entities\Stadium;
use Modules\Stadium\Entities\StadiumSlot;
use App\Models\User;

class StadiumSlotBookingFactory extends Factory
{
    protected $model = StadiumSlotBooking::class;

    public function definition(): array
    {
        return [
            'stadium_id' => Stadium::factory(),
            'stadium_slot_id' => StadiumSlot::factory(),
            'user_id' => User::factory(),
            'date' => $this->faker->date(),
            'status' => 'booked',
            'payment_status' => 'pending',
            'payment_type' => $this->faker->randomElement(['full', 'deposit']), 
            'amount_paid' => $this->faker->randomFloat(2, 10, 100),
        ];    }
}
