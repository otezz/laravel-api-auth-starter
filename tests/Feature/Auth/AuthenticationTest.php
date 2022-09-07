<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_correct_credentials()
    {
        $user = User::factory()->create([
            'password' => Hash::make('correct-password'),
        ]);

        $response = $this->postJson(route('login'), [
            'email' => $user->email,
            'password' => 'correct-password',
        ]);

        $response
            ->assertOk()
            ->assertJson(
                fn (AssertableJson $json) => $json->whereType('token', 'string')
            );
    }

    public function test_users_can_not_authenticate_with_invalid_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('correct-password'),
        ]);

        $response = $this->postJson(route('login'), [
            'email' => $user->email,
            'password' => 'incorrect-password',
        ]);

        $response->assertStatus(422);
    }

    public function test_suspended_user_cannot_login()
    {
        $user = User::factory()->suspended()->create([
            'password' => Hash::make('correct-password'),
        ]);

        $response = $this->postJson(route('login'), [
            'email' => $user->email,
            'password' => 'correct-password',
        ]);

        $response
            ->assertUnauthorized()
            ->assertJson(
                fn (AssertableJson $json) => $json->whereType('message', 'string')
            );
    }

    public function test_authenticated_user_can_be_logged_out()
    {
        $user = User::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$user->createToken('api')->plainTextToken,
        ])->postJson(route('logout'));

        $response->assertOk();
    }
}
