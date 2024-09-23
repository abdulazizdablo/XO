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
        Schema::create('stock_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variation_id')->constrained('product_variations')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('inventory_id')->constrained('inventories')->onDelete('cascade')->onUpdate('cascade');

            $table->string('name');
            $table->integer('min_stock_level');
            $table->integer('max_stock_level');
            $table->integer('current_stock_level');
            $table->dateTime('target_date');
            $table->integer('sold_quantity');
            $table->string('status');
            $table->softDeletes();
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
        Schema::dropIfExists('stock_levels');
    }
};
