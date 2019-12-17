<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeenumfieldProducttoken extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
          Schema::table('products', function ($table) {
            $table->dropColumn('type');
        });
     Schema::table('products', function(Blueprint $table)
    {
       $table->enum('type',array('product','service','seller_token'))->after('generated_by');
        $table->string('token')->after('price');
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
