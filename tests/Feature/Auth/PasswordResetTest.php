<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_can_be_requested()
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->postJson(route('password.email'), [
            'email' => $user->email,
        ]);

        $response->assertOk();
        Notification::assertSentTo([$user], ResetPassword::class);
    }

    public function test_reset_password_endpoint_returns_http_status_ok_for_non_existence_email()
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'john@email.com',
        ]);

        $response = $this->postJson(route('password.email'), [
            'email' => 'non-existence-user@email.com',
        ]);

        $response->assertOk();
        Notification::assertNotSentTo([$user], ResetPassword::class);
    }

    public function test_reset_password_endpoint_can_throttle_request()
    {
        $user = User::factory()->create();

        $this->postJson(route('password.email'), [
            'email' => $user->email,
        ]);
        $response = $this->postJson(route('password.email'), [
            'email' => $user->email,
        ]);

        $response->assertStatus(400);
    }

    public function test_check_reset_token_endpoint_validates_user_request()
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        // Valid email & token
        $response = $this->getJson(route('password.reset', ['token' => $token, 'email' => $user->email]));
        $response->assertStatus(200);

        // Invalid email
        $response = $this->getJson(route('password.reset', ['token' => $token, 'email' => 'invalid@email.com']));
        $response->assertStatus(422);

        // Invalid token
        $response = $this->getJson(route('password.reset', ['token' => 'invalid-token', 'email' => $user->email]));
        $response->assertStatus(422);
    }

    public function test_user_can_update_password_with_correct_email_and_token_combination()
    {
        $user = User::factory()->create();

        $token = Password::createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertOk();
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_user_cannot_update_password_with_incorrect_email_and_token_combination()
    {
        $user = User::factory()->create();

        Password::createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => 'wrong-token',
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(400);
    }
}
