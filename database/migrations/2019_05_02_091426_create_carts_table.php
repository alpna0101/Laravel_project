<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {
                $table->increments('id');
                 $table->integer('product_id');
                 $table->integer('order_id');
               
                $table->float('price');

                $table->integer('quantity')->default(1);
                $table->boolean('status')->default(false);
                $table->timestamps();

                 
        });
         Schema::table('carts', function($table) {
      $table->foreign('order_id')->references('id')->on('orders');
                $table->foreign('product_id')->references('id')->on('products');
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('carts');
    }
}
