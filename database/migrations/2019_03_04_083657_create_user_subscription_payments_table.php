<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserSubscriptionPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_subscription_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_subscription_id');
            $table->integer('channel_id');
            $table->integer('user_id');
            $table->string('payment_id');
            $table->string('subscription_plan');
            $table->float('subscription_amount');
            $table->float('paid_amount');
            $table->float('admin_subscribe_amount');
            $table->float('user_subscribe_amount');
            $table->string('payment_mode');
            $table->dateTime('expiry_date');
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
        Schema::drop('user_subscription_payments');
    }
}
