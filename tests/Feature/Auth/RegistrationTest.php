<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_users_can_register()
    {
        $user = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->postJson(route('register'), $user);

        $response->assertCreated();

        $this->assertDatabaseHas('users', [
            'email' => $user['email'],
            'name' => $user['name'],
        ]);
    }

    public function test_registered_event_dispatched_when_new_user_registered()
    {
        Event::fake();

        $user = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
        $response = $this->postJson(route('register'), $user);

        $response->assertCreated();

        Event::assertDispatched(Registered::class);
    }

    public function test_email_verification_was_sent_when_user_registered()
    {
        Notification::fake();

        $user = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
        $response = $this->postJson(route('register'), $user);

        $response->assertCreated();

        $user = User::where('name', $user['name'])->where('email', $user['email'])->first();
        $this->assertNotNull($user);

        Notification::assertSentTo([$user], VerifyEmail::class);
    }

    public function test_user_cannot_register_if_email_already_used()
    {
        User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $user = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
        $response = $this->postJson(route('register'), $user);

        $response->assertStatus(422);
    }

    public function test_user_cannot_register_if_password_did_not_match_password_confirmation()
    {
        $user = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password-did-not-match',
        ];
        $response = $this->postJson(route('register'), $user);

        $response->assertStatus(422);
    }
}
