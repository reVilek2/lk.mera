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
        Schema::create('documents', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('client_id');
            $table->unsignedInteger('manager_id');
            $table->unsignedBigInteger('amount');
            $table->boolean('signed')->default(false);
            $table->boolean('paid')->default(false);
            $table->timestamps();

            $table->index(['client_id', 'manager_id']);
            $table->foreign('client_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->foreign('manager_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        Schema::create('documents_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('document_id');
            $table->unsignedInteger('user_id');
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
        Schema::dropIfExists('documents');
    }
}
