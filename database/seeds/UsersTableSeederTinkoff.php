<?php

use Illuminate\Database\Seeder;

class UsersTableSeederTinkoff extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('billing_transaction_type')->insert([
            ['code' => 'tinkoff_in', 'name' => 'Пополнение с tinkoff'],
            ['code' => 'tinkoff_out', 'name' => 'Возврат в tinkoff'],
        ]);

        DB::table('billing_account_type')->insert([
            ['code' => 'tinkoff', 'name' => 'Счет Tinkoff', 'type' => 'passive'],
        ]);
    }
}
