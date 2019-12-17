<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
           
           
            $table->float('total_price');
            $table->boolean('payment_status')->default(false);
            $table->dateTime('order_date');
            $table->dateTime('payment_date');
            $table->enum('current_status',array('P','IP','C','D'));
            $table->boolean('status')->default(false);
             
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
        Schema::drop('orders');
    }
}
