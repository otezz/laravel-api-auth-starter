<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $arrayOfPermissionNames = [
            'manage-everything',
            'manage-users',
            'manage-permissions',
            'manage-roles',
            'manage-recipes',
            'manage-own-recipes',
        ];
        $permissions = collect($arrayOfPermissionNames)->map(function ($permission) {
            return ['name' => $permission, 'guard_name' => 'web'];
        });

        Permission::insert($permissions->toArray());

        // Create Super Admin role & user
        $role = Role::create(['name' => 'Super Admin']);
        $permission = Permission::whereName('manage-everything');
        $role->givePermissionTo($permission);

        $user = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@email.com',
            'password' => Hash::make('123123123'),
        ]);
        $user->assignRole('Super Admin');

        // Create Basic User Role
        Role::create(['name' => 'User'])->givePermissionTo(['manage-own-recipes']);
    }
}
