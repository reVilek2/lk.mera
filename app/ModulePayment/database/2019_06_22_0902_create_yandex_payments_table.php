<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYandexPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('yandex_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('idempotency_key')->unique();
            $table->bigInteger('amount')->default(0);
            $table->boolean('paid')->default(false);

            $table->string('payment_id');
            $table->string('payment_type');
            $table->string('payment_method_type')->nullable();
            $table->text('payment_meta')->nullable();
            $table->string('status')->default('pending');
            $table->text('description')->nullable();

            $table->unsignedInteger('user_id');
            $table->unsignedInteger('transaction_id');

            $table->index(['user_id', 'transaction_id']);
            $table->foreign('user_id')
                ->references('id')
                ->on('users');
            $table->foreign('transaction_id')
                ->references('id')
                ->on('billing_transactions');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yandex_payments');
    }
}
