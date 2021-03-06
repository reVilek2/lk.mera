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
        Role::create(['name' => 'client']);
        Role::create(['name' => 'introducer']);
        Role::create(['name' => 'manager']);
        Role::create(['name' => 'admin'])->givePermissionTo(Permission::all());

        foreach ($this->baseUsers() as $baseUser) {
            $user = User::firstOrCreate(
                ['email' => $baseUser['email']],
                [
                    'first_name' => $baseUser['first_name'],
                    'last_name' => $baseUser['last_name'],
                    'phone' => $baseUser['phone'],
                    'password' => Hash::make($baseUser['password']),
                    'email_verified_at' => now(),
                    'phone_verified_at' => $baseUser['phone'] ? now() : null,
                    'is_activated' => true,
                    'activated_at' => now(),
                    'api_token' => Token::generate(),
                ]
            );
            $user->assignRole($baseUser['role']);
            $user = null;
        }
        //create demo chats
        $allUsers = User::all();
        $usersAdminsIds = User::role('admin')->get()->pluck('id')->toArray();

        foreach (User::all() as $key => $user) {
            foreach ($allUsers as $user2) {
                if ($user->id !== $user2->id) {
                    ChatService::createChat([$user->id, $user2->id], '?????????????????? ??????');
                }
            }
            unset($allUsers[$key]);
        }
        ChatService::createChat($usersAdminsIds, '????????????????????????????', false);

        // ???????????????? ??????????????????
        $managersIds = [];
        $faker = Faker\Factory::create('ru_RU');
        $limit = 6;
        $count = 0;
        for ($i = 0; $i < $limit; $i++) {
            $count++;
            $user = User::firstOrCreate(
                ['email' => 'manager'.$count.'@gmail.com'],
                [
                    'first_name' => ($i % 2) == 0 ? $faker->firstNameMale : $faker->firstNameFemale,
                    'second_name' => ($i % 2) == 0 ? $faker->middleNameMale : $faker->middleNameFemale,
                    'last_name' => $faker->lastName,
                    'phone' => $faker->unique()->numerify('+7##########'),
                    'password' => Hash::make('devMC@manager'),
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                    'is_activated' => true,
                    'activated_at' => now(),
                    'api_token' => Token::generate(),
                ]
            );
            $user->assignRole('manager');
            $managersIds[] = $user->id;
            $user = null;
        }

        // ???????????????? ??????????????
        $limit = 5;
        $count = 0;
        for ($i = 0; $i < $limit; $i++) {
            $count++;
            $user = User::firstOrCreate(
                ['email' => 'user'.$count.'@gmail.com'],
                [
                    'first_name' => ($i % 2) == 0 ? $faker->firstNameMale : $faker->firstNameFemale,
                    'second_name' => ($i % 2) == 0 ? $faker->middleNameMale : $faker->middleNameFemale,
                    'last_name' => $faker->lastName,
                    'phone' => $faker->unique()->numerify('+7##########'),
                    'password' => Hash::make('devMC@user'),
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                    'is_activated' => true,
                    'activated_at' => now(),
                    'api_token' => Token::generate(),
                ]
            );
            $user->assignRole('client');
            if ($i < 3) {
                $user->manager()->attach($managersIds[0]);
                ChatService::createChat([$user->id, $managersIds[0]], '?????????????????? ?????? c ????????????????????', true, true);
            } else {
                $user->manager()->attach($managersIds[1]);
                ChatService::createChat([$user->id, $managersIds[1]], '?????????????????? ?????? c ????????????????????', true, true);
            }
            $user = null;
        }

        // ???????????????? ????????????????????????
        $limit = 15;
        for ($i = 0; $i < $limit; $i++) {
            $count++;
            $user = User::firstOrCreate(
                ['email' => 'user'.$count.'@gmail.com'],
                [
                    'first_name' => ($i % 2) == 0 ? $faker->firstNameMale : $faker->firstNameFemale,
                    'second_name' => ($i % 2) == 0 ? $faker->middleNameMale : $faker->middleNameFemale,
                    'last_name' => $faker->lastName,
                    'phone' => $faker->unique()->numerify('+7##########'),
                    'password' => Hash::make('devMC@user'),
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                    'is_activated' => true,
                    'activated_at' => now(),
                    'api_token' => Token::generate(),
                ]
            );
            $user->assignRole('user');
            $user = null;
        }
    }

    public function baseUsers()
    {
        return [
            [
                'email' => 'zubarevski@gmail.com',
                'phone' => '+79611016314',
                'first_name' => '??????????',
                'last_name' => '??????????????',
                'password' => 'secret',
                'role' => ['admin', 'manager'],
            ],
            [
                'email' => 'okremaa@gmail.com',
                'phone' => null,
                'first_name' => '??????????',
                'last_name' => '????????????',
                'password' => 'secret',
                'role' => ['admin', 'manager'],
            ],
            [
                'email' => 'agusev@mera-capital.com',
                'phone' => null,
                'first_name' => '????????????',
                'last_name' => '??????????',
                'password' => 'secret',
                'role' => ['admin', 'manager'],
            ],
            [
                'email' => 'o.pershina@mera-capital.com',
                'phone' => null,
                'first_name' => '??????????',
                'last_name' => '??????????????',
                'password' => 'secret',
                'role' => ['admin', 'manager'],
            ],
            [
                'email' => 'dg@mera-capital.com',
                'phone' => null,
                'first_name' => '??????????',
                'last_name' => '????????????????????????',
                'password' => 'secret',
                'role' => ['admin', 'manager'],
            ],
        ];
    }

}
