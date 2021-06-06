<?php

use Illuminate\Database\Seeder;

class UsersTableSeederPaykeeper extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('billing_transaction_type')->insert([
            ['code' => 'paykeeper_in', 'name' => 'Пополнение с paykeeper'],
            ['code' => 'paykeeper_out', 'name' => 'Возврат в paykeeper'],
        ]);

        DB::table('billing_account_type')->insert([
            ['code' => 'paykeeper', 'name' => 'Счет paykeeper', 'type' => 'passive'],
        ]);
    }
}
