<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RefactorPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('yandex_payments', function (Blueprint $table) {
            $table->dropColumn('paid');
            $table->string('source')->nullable()->after('idempotency_key');
        });

        Schema::rename('yandex_payments', 'payments');

        DB::table('payments')
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
        Schema::rename('payments', 'yandex_payments');

        Schema::table('yandex_payments', function (Blueprint $table) {
            $table->boolean('paid')->default(false);
            $table->dropColumn('source');
        });
    }
}
