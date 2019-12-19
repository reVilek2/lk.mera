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
            $table->bigIncrements('id');
            $table->string('idempotency_key')->unique();
            $table->bigInteger('amount')->default(0);
            $table->boolean('paid')->default(false);

            $table->string('payment_id')->nullable();
            $table->string('payment_type')->nullable();
            $table->string('payment_method_type')->nullable();
            $table->text('payment_method_meta')->nullable();
            $table->string('status')->default('pending');
            $table->text('description')->nullable();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('transaction_id');

            $table->index(['user_id', 'transaction_id']);
            $table->foreign('user_id')
                ->references('id')
                ->on('users');
            $table->foreign('transaction_id')
                ->references('id')
                ->on('billing_transactions');

            $table->timestamps();
        });

        Schema::create('payment_cards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('card_id')->unique();
            $table->string('year')->nullable();
            $table->string('month')->nullable();
            $table->string('type')->nullable();
            $table->string('first')->nullable();
            $table->string('last')->nullable();
            $table->string('pan')->nullable();
            $table->boolean('card_default')->default(false);
            $table->unsignedBigInteger('user_id');

            $table->index(['user_id']);
            $table->foreign('user_id')
                ->references('id')
                ->on('users');

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
        Schema::dropIfExists('payment_cards');
    }
}
