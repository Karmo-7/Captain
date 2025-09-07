<?php

namespace Modules\Stadium\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Stadium\Entities\StadiumSlot;
use Modules\Stadium\Entities\Stadium;

class StadiumSlotFactory extends Factory
{
    protected $model = StadiumSlot::class;

    public function definition(): array
    {
        $start = $this->faker->time('H:i:s', '18:00:00');
        $end = date('H:i:s', strtotime($start) + 3600);

        return [
            'stadium_id' => Stadium::factory(),
            'start_time' => $start,
            'end_time' => $end,
            'status' => 'available',
        ];
    }
}
