<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldPaymentmethod extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::table('payment_methods', function($table) {
          $table->string('symbol');
            $table->string('slug')->default(null);
            $table->string('circulating_supply')->default(null);
            $table->string('total_supply')->default(null);
            $table->string('max_supply')->default(null);
            $table->string('num_market_pairs')->default(null);
            $table->integer('cmc_rank');
            $table->string('usd_price')->default(null);
            $table->string('usd_volume_24h')->default(null);
            $table->string('usd_volume_percent_change_1h')->default(null);
            $table->string('usd_percent_change_24h')->default(null);
            $table->string('percent_change_7d')->default(null);
            $table->string('market_cap')->default(null);
            $table->string('last_updated')->default(null);
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
