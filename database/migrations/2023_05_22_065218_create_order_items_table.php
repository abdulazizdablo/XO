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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('product_variation_id')->constrained('product_variations')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('return_order_id')->nullable();

            $table->string('quantity');
            $table->integer('original_price');
            $table->integer('price');
            $table->text('reason')->nullable();
            $table->foreign('return_order_id')->references('id')->on('orders');
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
        Schema::dropIfExists('order_items');
    }
};
