<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
Use Illuminate\Database\Schema\Blueprint;

class CreateChatsTable extends Migration
{
    /**
     * Run the migrations.
     * 
     * @return void
     */
    public function up()
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('private')->default(true);
            $table->text('data')->nullable();
            $table->boolean('deleted')->default(0);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('chats_users', function (Blueprint $table) {
            $table->unsignedInteger('chat_id');
            $table->unsignedInteger('user_id');
            $table->boolean('leave')->default(0);
            $table->timestamp('leave_at')->nullable();
            $table->timestamps();

            $table->primary(['chat_id', 'user_id']);
            $table->foreign('chat_id')
                ->references('id')
                ->on('chats')
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
        Schema::dropIfExists('chats');
        Schema::dropIfExists('chats_users');
    }
}
