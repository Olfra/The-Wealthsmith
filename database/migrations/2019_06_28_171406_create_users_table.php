<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 90);
            $table->string('email', 90)->unique();
            $table->string('account_no');
            $table->string('image');
            $table->string('provider');
            $table->string('provider_id');
            $table->string('password')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('dob')->nullable();
            $table->string('address')->nullable();
            $table->string('bvn')->nullable();
            $table->string('type_of_id')->nullable();
            $table->string('url_of_id')->nullable();
            $table->string('type_of_add')->nullable();
            $table->string('url_of_add')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
