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
        Schema::create('request_product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cargo_request_id')->constrained();
            $table->foreignId('product_variation_id')->constrained();
            $table->unsignedMediumInteger('requested_from_inventory');
            $table->unsignedMediumInteger('requested_from_manager')->nullable();
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
        Schema::dropIfExists('request_product_variations');
    }
};
