<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserTableSeeder extends Seeder
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

        // create permissions
        Permission::create(['name' => 'edit users']);
        Permission::create(['name' => 'delete users']);

        // create roles and assign created permissions

        // this can be done as separate statements
        Role::create(['name' => 'user']);
        Role::create(['name' => 'manager']);
        Role::create(['name' => 'admin'])->givePermissionTo(Permission::all());
    }
}
