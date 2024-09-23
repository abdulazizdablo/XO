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
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreign('group_id')->references('id')->on('groups');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('variation_id')->nullable();
            $table->foreignId('color_id')->constrained('colors')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('size_id')->constrained('sizes')->onDelete('cascade')->onUpdate('cascade');

            $table->string('sku_code');
            $table->boolean('visible')->default(false);
            $table->foreign('variation_id')->references('id')->on('variations');
            $table->unsignedBigInteger('group_id')->nullable();
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
        Schema::dropIfExists('product_variations');
    }
};
