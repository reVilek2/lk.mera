<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeUserFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
            $table->string('reset_code', 4)->nullable();
            $table->timestamp('reset_code_created_at')->nullable();
            $table->string('social_type')->nullable();
            $table->string('social_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->change();
            $table->dropColumn('reset_code');
            $table->dropColumn('reset_code_created_at');
            $table->dropColumn('social_type');
            $table->dropColumn('social_id');
        });
    }
}
