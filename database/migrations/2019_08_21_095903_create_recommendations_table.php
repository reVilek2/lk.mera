<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecommendationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recommendations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('manager_id')->index();
            $table->string('title');
            $table->text('text');
            $table->string('email');
            $table->timestamps();
        });

        Schema::create('recommendation_receivers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('recommendation_id')->index();
            $table->unsignedBigInteger('client_id')->index();
            $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending');
            $table->timestamps();

            $table->foreign('recommendation_id')
                ->references('id')->on('recommendations')
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
        Schema::dropIfExists('recommendation_receivers');
        Schema::dropIfExists('recommendations');
    }
}
