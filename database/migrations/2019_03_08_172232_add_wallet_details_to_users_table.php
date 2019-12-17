<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWalletDetailsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('wallet_address');
            $table->string('coin_payment_pay_name');
            $table->string('mac_address');
            $table->string('gold_access_app_username');
            $table->string('gold_access_app_password');
            $table->string('media_box_voucher_code');
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
            $table->dropColumn('wallet_address');
            $table->dropColumn('coin_payment_pay_name');
            $table->dropColumn('mac_address');
            $table->dropColumn('gold_access_app_username');
            $table->dropColumn('gold_access_app_password');
            $table->dropColumn('media_box_voucher_code');
        });
    }
}
