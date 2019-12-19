<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_transaction_type', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code');
            $table->string('name');
        });
        Schema::create('billing_transaction_status', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code');
            $table->string('name');
        });
        Schema::create('billing_operation_type', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code');
            $table->string('name');
        });
        Schema::create('billing_account_type', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code');
            $table->string('name');
            $table->string('type');
        });

        Schema::create('billing_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('acc_type_id')->index();
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
            $table->bigIncrements('id');
            $table->unsignedBigInteger('initiator_user_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('receiver_acc_id')->index();
            $table->unsignedBigInteger('sender_acc_id')->index();
            $table->unsignedBigInteger('amount')->default(0);
            $table->string('operation');
            $table->unsignedBigInteger('status_id')->index();
            $table->unsignedBigInteger('type_id')->index();
            $table->text('comment')->nullable();
            $table->text('meta_data')->nullable();
            $table->timestamps();

            $table->index(['receiver_acc_id', 'user_id']);
            $table->index(['sender_acc_id', 'user_id']);

            $table->foreign('receiver_acc_id')
                ->references('id')
                ->on('billing_accounts');

            $table->foreign('sender_acc_id')
                ->references('id')
                ->on('billing_accounts');

            $table->foreign('initiator_user_id')
                ->references('id')
                ->on('users');
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
            $table->bigIncrements('id');
            $table->unsignedBigInteger('account_id')->index();
            $table->unsignedBigInteger('transaction_id')->index();
            $table->unsignedBigInteger('type_id')->index();
            $table->unsignedBigInteger('amount')->default(0);
            $table->bigInteger('balance')->default(0);
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

        Schema::create('documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('manager_id');
            $table->unsignedBigInteger('amount');
            $table->boolean('signed')->default(false);
            $table->boolean('paid')->default(false);
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'manager_id']);
            $table->index(['client_id', 'transaction_id']);
            $table->foreign('transaction_id')
                ->references('id')
                ->on('billing_transactions');
            $table->foreign('client_id')
                ->references('id')
                ->on('users');
            $table->foreign('manager_id')
                ->references('id')
                ->on('users');
        });

        Schema::create('documents_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('document_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('signed')->default(false);
            $table->boolean('paid')->default(false);
            $table->timestamps();

            $table->index(['document_id', 'user_id']);
            $table->foreign('document_id')
                ->references('id')
                ->on('documents')
                ->onDelete('cascade');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
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
        Schema::dropIfExists('documents');
    }
}
