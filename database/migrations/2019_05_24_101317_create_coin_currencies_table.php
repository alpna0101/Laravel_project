<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoinCurrenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_currencies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('symbol');
            $table->string('slug');
            $table->string('circulating_supply');
            $table->string('total_supply');
            $table->string('max_supply');
            $table->string('num_market_pairs');
            $table->integer('cmc_rank');
            $table->string('usd_price');
            $table->string('usd_volume_24h');
            $table->string('usd_volume_percent_change_1h');
            $table->string('usd_percent_change_24h');
            $table->string('percent_change_7d');
            $table->string('market_cap');
            $table->string('last_updated');
              
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
        Schema::drop('coin_currencies');
    }
}
