<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Book\Entities\StadiumSlotBooking;
use Modules\Stadium\Entities\Stadium;
use Modules\Stadium\Entities\StadiumSlot;
use App\Models\User;

class BookControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_player_can_book_slot_successfully()
    {
        $user = User::factory()->create();
        $stadium = Stadium::factory()->create(['user_id' => $user->id]);
        $slot = StadiumSlot::factory()->create(['stadium_id' => $stadium->id, 'status' => 'available']);

        $payload = [
            'stadium_slot_id' => $slot->id,
            'stadium_id' => $stadium->id,
            'date' => now()->addDay()->format('Y-m-d'),
            'payment_type' => 'full',
        ];

        $response = $this->actingAs($user, 'api')->postJson('/api/Booking/create', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status' => true,
                'message' => 'Slot booked successfully.'
            ]);

        $this->assertDatabaseHas('stadium_slot_bookings', [
            'stadium_slot_id' => $slot->id,
            'stadium_id' => $stadium->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_cannot_book_slot_if_already_booked()
    {
        $user = User::factory()->create();
        $stadium = Stadium::factory()->create(['user_id' => $user->id]);
        $slot = StadiumSlot::factory()->create(['stadium_id' => $stadium->id, 'status' => 'available']);

        StadiumSlotBooking::factory()->create([
            'stadium_slot_id' => $slot->id,
            'stadium_id' => $stadium->id,
            'date' => now()->addDay()->format('Y-m-d'),
            'status' => 'booked',
            'payment_type' => 'full',
        ]);

        $payload = [
            'stadium_slot_id' => $slot->id,
            'stadium_id' => $stadium->id,
            'date' => now()->addDay()->format('Y-m-d'),
            'payment_type' => 'full',
        ];

        $response = $this->actingAs($user, 'api')->postJson('/api/Booking/create', $payload);

        $response->assertStatus(409)
            ->assertJson([
                'status' => false,
                'message' => 'This slot is already booked for this date.',
            ]);
    }

    public function test_cannot_book_slot_if_in_maintenance()
    {
        $user = User::factory()->create();
        $stadium = Stadium::factory()->create(['user_id' => $user->id]);
        $slot = StadiumSlot::factory()->create(['stadium_id' => $stadium->id, 'status' => 'maintenance']);

        $payload = [
            'stadium_slot_id' => $slot->id,
            'stadium_id' => $stadium->id,
            'date' => now()->addDay()->format('Y-m-d'),
            'payment_type' => 'deposit',
        ];

        $response = $this->actingAs($user, 'api')->postJson('/api/Booking/create', $payload);

        $response->assertStatus(409)
            ->assertJson([
                'status' => false,
                'message' => 'This slot is under maintenance and cannot be booked.',
            ]);
    }


}
