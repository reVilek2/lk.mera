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
            $table->unsignedInteger('owner_id');
            $table->boolean('group')->default(0);
            $table->string('group_name')->nullable();
            $table->boolean('deleted')->default(0);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();

            $table->index(['owner_id']);
        });

        Schema::create('chats_members', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('chat_id');
            $table->unsignedInteger('user_id');
            $table->boolean('leave')->default(0);
            $table->timestamp('leave_at')->nullable();
            $table->timestamps();

            $table->index(['user_id']);
            $table->foreign('chat_id')
                ->references('id')
                ->on('chats')
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
        Schema::dropIfExists('chats_members');
    }
}
