<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Transactiondates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    Schema::table('transactions', function($table) {
        $table->boolean('payment_sent')->default(false);
        $table->dateTime('payment_date');
        $table->dateTime('invoice_date');
        $table->dateTime('varify_date');
       
    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
