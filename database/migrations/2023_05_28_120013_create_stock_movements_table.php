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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('product_variation_id')->constrained('product_variations')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('from_inventory_id')->constrained('inventories')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('to_inventory_id')->nullable();
            $table->string('shipment_name');

            $table->timestamp('delivery_date');
            $table->timestamp('shipped_date')->nullable();
            $table->timestamp('received_date')->nullable();
            $table->integer('expected')->nullable();
            $table->integer('received')->nullable();
            $table->integer('num_of_packages');
            $table->string('status')->default('open');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('to_inventory_id')->references('id')->on('inventories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_movements');
    }
};
