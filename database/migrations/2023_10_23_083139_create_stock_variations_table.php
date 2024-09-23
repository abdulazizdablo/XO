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
        Schema::create('stock_variations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_variation_id')->unsigned();
            $table->unsignedBigInteger('stock_movement_id')->unsigned();

            $table->foreign('product_variation_id')->references('id')->on('product_variations')->onDelete('cascade');
            $table->foreign('stock_movement_id')->references('id')->on('stock_movements')->onDelete('cascade');
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
        Schema::dropIfExists('stock_variations');
    }
};
