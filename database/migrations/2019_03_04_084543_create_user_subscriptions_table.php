<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('unique_id');
            $table->integer('channel_id');
            $table->integer('user_id');
            $table->string('title');
            $table->text('description');
            $table->string('subscription_type')->comment="month,year,days";
            $table->string('plan');
            $table->float('amount');
            $table->integer('total_subscription');
            $table->integer('status');
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
        Schema::drop('user_subscriptions');
    }
}
