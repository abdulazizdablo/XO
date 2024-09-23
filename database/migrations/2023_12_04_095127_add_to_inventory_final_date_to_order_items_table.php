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
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('to_inventory')->after('quantity')->nullable();
            $table->foreign('to_inventory')
                  ->references('id')
                  ->on('inventories')
                  ->onDelete('cascade');
            $table->dateTime('final_date')->after('to_inventory')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            Schema::dropIfExists('to_inventory');
            Schema::dropIfExists('final_date');
        });
    }
};
