<?php

namespace Tests\Feature\General;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use function route;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_suspended_user_cannot_access_profile_endpoints()
    {
        $user = User::factory()->suspended()->create();

        $response = $this->actingAs($user)
            ->getJson(route('profile.index'));

        $response->assertUnauthorized();
    }

    public function test_user_can_get_his_profile_details()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson(route('profile.index'));

        $response->assertOk();
    }

    public function test_unauthenticated_user_cannot_get_his_profile_details()
    {
        $response = $this->getJson(route('profile.index'));

        $response->assertUnauthorized();
    }

    public function test_user_can_update_his_profile_details()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('profile.update'), [
                'name' => 'Updated User',
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', [
            'email' => $user->email,
            'name' => 'Updated User',
        ]);
    }

    public function test_user_can_change_his_password()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('profile.password'), [
                'old_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response->assertOk();
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_user_cannot_change_his_password_if_old_password_wrong()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('profile.password'), [
                'old_password' => 'wrong-old-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response->assertStatus(422);
        $this->assertFalse(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_user_cannot_change_his_password_if_new_password_did_not_match_password_confirmation()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('profile.password'), [
                'old_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'password-did-not-match',
            ]);

        $response->assertStatus(422);
        $this->assertFalse(Hash::check('new-password', $user->fresh()->password));
    }
}
