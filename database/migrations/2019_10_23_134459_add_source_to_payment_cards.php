<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSourceToPaymentCards extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_cards', function (Blueprint $table) {
            $table->string('source')->nullable()->after('id');
            $table->index('source');
        });

        DB::table('payment_cards')
            ->where('source', null)
            ->update(['source' => 'yandex']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_cards', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
}
