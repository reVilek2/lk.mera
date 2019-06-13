<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_transaction_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code');
            $table->string('name');
        });
        Schema::create('billing_transaction_status', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code');
            $table->string('name');
        });
        Schema::create('billing_operation_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code');
            $table->string('name');
        });
        Schema::create('billing_account_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code');
            $table->string('name');
            $table->string('type');
        });

        Schema::create('billing_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->index();
            $table->unsignedInteger('acc_type_id')->index();
            $table->bigInteger('balance')->default(0);
            $table->timestamps();

            $table->index(['acc_type_id', 'user_id']);

            $table->foreign('acc_type_id')
                ->references('id')
                ->on('billing_account_type');

            $table->foreign('user_id')
                ->references('id')
                ->on('users');
        });

        Schema::create('billing_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->index();
            $table->unsignedInteger('receiver_acc_id')->index();
            $table->unsignedInteger('sender_acc_id')->index();
            $table->unsignedBigInteger('amount')->default(0);
            $table->unsignedInteger('status_id')->index();
            $table->unsignedInteger('type_id')->index();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['receiver_acc_id', 'user_id']);
            $table->index(['sender_acc_id', 'user_id']);

            $table->foreign('receiver_acc_id')
                ->references('id')
                ->on('billing_accounts');

            $table->foreign('sender_acc_id')
                ->references('id')
                ->on('billing_accounts');

            $table->foreign('user_id')
                ->references('id')
                ->on('users');

            $table->foreign('status_id')
                ->references('id')
                ->on('billing_transaction_status');

            $table->foreign('type_id')
                ->references('id')
                ->on('billing_transaction_type');
        });

        Schema::create('billing_operations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('account_id')->index();
            $table->unsignedInteger('transaction_id')->index();
            $table->unsignedInteger('type_id')->index();
            $table->unsignedBigInteger('amount')->default(0);
            $table->timestamps();

            $table->index(['account_id', 'transaction_id']);

            $table->foreign('account_id')
                ->references('id')
                ->on('billing_accounts');

            $table->foreign('transaction_id')
                ->references('id')
                ->on('billing_transactions');

            $table->foreign('type_id')
                ->references('id')
                ->on('billing_operation_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('billing_operations');
        Schema::dropIfExists('billing_transactions');
        Schema::dropIfExists('billing_accounts');
        Schema::dropIfExists('billing_account_type');
        Schema::dropIfExists('billing_operation_type');
        Schema::dropIfExists('billing_transaction_status');
        Schema::dropIfExists('billing_transaction_type');
    }
}
