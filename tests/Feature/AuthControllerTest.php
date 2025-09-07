<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Customer;
use Spatie\Permission\Models\Role;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->mockStripe();

        foreach (['admin', 'player', 'stadium_owner'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }

    protected function mockStripe()
    {
        // Mock Account::create
        $this->partialMock(Account::class, function ($mock) {
            $mock->shouldReceive('create')->andReturn((object) [
                'id' => 'acct_test123'
            ]);
        });

        // Mock AccountLink::create
        $this->partialMock(AccountLink::class, function ($mock) {
            $mock->shouldReceive('create')
                ->andReturn((object) [
                    'url' => 'https://stripe.com/onboarding/test'
                ]);
        });

        // Mock Customer::create
        $this->partialMock(Customer::class, function ($mock) {
            $mock->shouldReceive('create')->andReturn((object) [
                'id' => 'cus_test123'
            ]);
        });
    }

    /** @test */
    public function user_can_register_as_player()
    {
        $response = $this->postJson('/api/register', [
            'email' => 'player@test.com',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
            'role' => 'player',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => true,
                'data' => [
                    'role' => 'player'
                ]
            ]);

        $this->assertDatabaseHas('users', ['email' => 'player@test.com']);
    }

    /** @test */
    public function user_can_register_as_stadium_owner_and_get_onboarding_url()
    {
        $response = $this->postJson('/api/register', [
            'email' => 'owner@test.com',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
            'role' => 'stadium_owner',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'role',
                    'onboarding_url'
                ]
            ])
            ->assertJsonFragment([
                'role' => 'stadium_owner'
            ]);
    }

    /** @test */
    public function user_can_register_as_admin()
    {
        $response = $this->postJson('/api/register', [
            'email' => 'admin@test.com',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
            'role' => 'admin',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => true,
                'data' => [
                    'role' => 'admin'
                ]
            ]);
    }

    /** @test */
    public function user_cannot_login_without_verifying_email()
    {
        $user = User::create([
            'email' => 'notverified@test.com',
            'password' => Hash::make('Password1'),
        ]);
        $user->assignRole('player');

        $response = $this->postJson('/api/login', [
            'email' => 'notverified@test.com',
            'password' => 'Password1'
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => false,
                'message' => 'Email not verified'
            ]);
    }

}
