<?php

use App\Models\Token;
use App\Models\User;
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

        $user1 = User::firstOrCreate(
            ['email' => 'zubarevski@gmail.com'],
            [
                'first_name' => 'Игорь',
                'last_name' => 'Зубарев',
                'phone' => '+79611016314',
                'password' => Hash::make('secret'),
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'is_activated' => true,
                'activated_at' => now(),
            ]
        );
        $user1->assignRole('admin');

        $user2 = User::firstOrCreate(
            ['email' => 'okremaa@gmail.com'],
            [
                'first_name' => 'Антон',
                'last_name' => 'Окрема',
                'password' => Hash::make('secret'),
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'is_activated' => true,
                'activated_at' => now(),
            ]
        );
        $user2->assignRole('admin');

        $user3 = User::firstOrCreate(
            ['email' => 'agusev@mera-capital.com'],
            [
                'first_name' => 'Андрей',
                'last_name' => 'Гусев',
                'password' => Hash::make('secret'),
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'is_activated' => true,
                'activated_at' => now(),
            ]
        );
        $user3->assignRole('admin');

        $user4 = User::firstOrCreate(
            ['email' => 'o.pershina@mera-capital.com'],
            [
                'first_name' => 'Ольга',
                'last_name' => 'Першина',
                'password' => Hash::make('secret'),
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'is_activated' => true,
                'activated_at' => now(),
            ]
        );
        $user4->assignRole('admin');

        $user5 = User::firstOrCreate(
            ['email' => 'dg@mera-capital.com'],
            [
                'password' => Hash::make('secret'),
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'is_activated' => true,
                'activated_at' => now(),
            ]
        );
        $user5->assignRole('manager');

        // API tokens
        $users = User::where('api_token', null)->get();
        /** @var User $user */
        foreach ($users as $user) {
            $user->api_token = Token::generate();
            $user->save();
        }
    }
}
