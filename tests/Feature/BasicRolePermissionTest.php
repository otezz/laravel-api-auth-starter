<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\CreatesApplication;
use Tests\TestCase;

class BasicRolePermissionTest extends TestCase
{
    use RefreshDatabase;
    use CreatesApplication;

    /**
     * Indicates whether the default seeder should run before each test.
     *
     * @var bool
     */
    protected bool $seed = true;

    public function test_permission_can_be_given_to_an_user()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('manage-everything');

        $this->assertTrue($user->can('manage-everything'));
    }

    public function test_role_can_be_assigned_to_an_user()
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $roles = $user->getRoleNames();

        $this->assertTrue($user->can('manage-everything'));
        $this->assertContains('Super Admin', $roles);
    }

    public function test_permission_can_be_revoked_from_an_user()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('manage-everything');

        $this->assertTrue($user->can('manage-everything'));

        $user->revokePermissionTo('manage-everything');

        $this->assertFalse($user->can('manage-everything'));
    }

    public function test_role_can_be_revoked_from_an_user()
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $this->assertTrue($user->can('manage-everything'));

        $user->removeRole('Super Admin');

        $this->assertFalse($user->can('manage-everything'));
    }

    public function test_permission_can_be_attached_to_a_role()
    {
        $role = Role::create(['name' => 'new-role']);
        $permission = Permission::create(['name' => 'new-permission']);
        $role->givePermissionTo($permission);

        $this->assertTrue($role->hasPermissionTo($permission));
    }

    public function test_permission_can_be_revoked_from_a_role()
    {
        $role = Role::create(['name' => 'new-role']);
        $permission = Permission::create(['name' => 'new-permission']);
        $role->givePermissionTo($permission);

        $this->assertTrue($role->hasPermissionTo($permission));

        $role->revokePermissionTo($permission);

        $this->assertFalse($role->hasPermissionTo($permission));
    }
}
