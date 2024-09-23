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
        Schema::create('shipment_product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cargo_shipment_id')->constrained('cargo_shipments')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('product_variation_id')->constrained();
            $table->mediumInteger('quantity');
            $table->timestamp('ship_date');
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
        Schema::dropIfExists('shipment_product_variations');
    }
};
