<?php

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Seeder;

class BillingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('billing_transaction_type')->insert([
            ['code' => 'manual_in', 'name' => 'Ручное пополнение счета'],
            ['code' => 'manual_out', 'name' => 'Ручное списание со счета'],
            ['code' => 'card_in', 'name' => 'Пополнение с карты'],
            ['code' => 'card_out', 'name' => 'Возврат на карту'],
            ['code' => 'service_in', 'name' => 'Оплата услуг'],
            ['code' => 'service_out', 'name' => 'Возврат оплаты за услуги'],
        ]);

        DB::table('billing_transaction_status')->insert([
            ['code' => 'pending', 'name' => 'Ожидает исполнения'],
            ['code' => 'success', 'name' => 'Исполнен'],
            ['code' => 'error', 'name' => 'Ошибка'],
        ]);

        DB::table('billing_operation_type')->insert([
            ['code' => 'incoming', 'name' => 'Входящий платеж'],
            ['code' => 'outgoing', 'name' => 'Исходящий платеж'],
        ]);

        DB::table('billing_account_type')->insert([
            ['code' => 'balance', 'name' => 'Счет основного баланса', 'type' => 'active'],
            ['code' => 'virtual', 'name' => 'Счет ручного пополнения', 'type' => 'passive'],
            ['code' => 'kassa_yandex', 'name' => 'Счет яндекс кассы', 'type' => 'passive'],
            ['code' => 'service', 'name' => 'Счет оказанных услуг', 'type' => 'passive'],
        ]);
    }
}
