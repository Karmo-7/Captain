<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Database\Seeders\RolePermissionSeeder;

class ProfileApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['auth.defaults.guard' => 'api']);
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_player_can_create_profile()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $user->assignRole('player');
        Passport::actingAs($user);

        $response = $this->postJson('/api/profile/create', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'birthdate' => '2000-01-01',
            'address' => 'Somewhere',
            'phone_number' => '1234567890',
            'gender' => 'male',
            'height' => 180,
            'weight' => 75,
            'Sport' => 'Football',
            'positions_played' => 'Striker',
            'years_of_experience' => 2,
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
            'notable_achievements' => 'MVP 2020',
            'previous_teams' => 'Team A, Team B',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'status',
            'status_code',
            'message',
            'data' => ['profile']
        ]);

        $this->assertDatabaseHas('profiles', ['first_name' => 'John']);
    }

    public function test_player_cannot_create_duplicate_profile()
    {
        $user = User::factory()->create();
        $user->assignRole('player');
        Passport::actingAs($user);

        Profile::factory()->create(['user_id' => $user->id]);

        $response = $this->postJson('/api/profile/create', [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'birthdate' => '1995-05-05',
            'address' => 'Another place',
            'phone_number' => '1234567891',
            'gender' => 'female',
            'height' => 170,
            'weight' => 60,
            'Sport' => 'Basketball',
            'positions_played' => 'Center',
            'years_of_experience' => 4,
        ]);

        $response->assertStatus(409);
    }

    public function test_player_can_update_own_profile()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $user->assignRole('player');
        Passport::actingAs($user);

        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'first_name' => 'OldName'
        ]);

        $response = $this->putJson("/api/profile/update/{$profile->id}", [
            'first_name' => 'UpdatedName',
            'avatar' => UploadedFile::fake()->image('updated.jpg'),
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('profiles', ['first_name' => 'UpdatedName']);
    }

    public function test_player_can_delete_own_profile()
    {
        $user = User::factory()->create();
        $user->assignRole('player');
        Passport::actingAs($user);

        $profile = Profile::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/profile/delete/{$profile->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('profiles', ['id' => $profile->id]);
    }

    public function test_profile_validation_fails_without_required_fields()
    {
        $user = User::factory()->create();
        $user->assignRole('player');
        Passport::actingAs($user);

        $response = $this->postJson('/api/profile/create', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'first_name',
            'last_name',
            'birthdate',
            'address',
            'Sport',
            'phone_number',
            'gender',
            'height',
            'weight',
            'positions_played',
            'years_of_experience',
        ]);
    }
}
