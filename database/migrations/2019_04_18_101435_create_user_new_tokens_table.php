<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserNewTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_tokens', function (Blueprint $table) {
            $table->increments('id');
           $table->integer('user_id')->unsigned();
            $table->boolean('redeem')->default(false);
            $table->float('token');
            $table->timestamps();
        });
         Schema::table('user_tokens', function($table) {
       $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
   });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_new_tokens');
    }
}