<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->double('paid_by_user')->after('total_price')->nullable();
            $table->double('covered_by_gift_card')->after('paid_by_user')->nullable();
            $table->double('discounted_by_coupon')->after('covered_by_gift_card')->nullable();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            Schema::dropIfExists('paid_by_user');
            Schema::dropIfExists('covered_by_gift_card');
            Schema::dropIfExists('discounted_by_coupon');
            
        });
    }
};
