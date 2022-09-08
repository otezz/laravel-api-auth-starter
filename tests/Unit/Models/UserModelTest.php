<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_user_is_suspended_attribute_returns_boolean()
    {
        $activeUser = User::factory()->create();
        $suspendedUser = User::factory()->suspended()->create();

        $this->assertFalse($activeUser->is_suspended);
        $this->assertTrue($suspendedUser->is_suspended);
    }
}
