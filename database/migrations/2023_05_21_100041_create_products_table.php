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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // $table->nullableMorphs('promotionable');
            $table->unsignedBigInteger('discount_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->foreignId('sub_category_id')->constrained('sub_categories')->onDelete('cascade')->onUpdate('cascade');
            $table->string('item_no');
            $table->string('slug');
            $table->boolean('available')->default(0);
            $table->json('name');
            $table->json('description');
            $table->json('material');
            $table->json('composition');
            $table->json('care_instructions');
            $table->json('fit');
            $table->json('style');
            $table->json('season');

            $table->softDeletes();
            $table->foreign('discount_id')->references('id')->on('discounts');
            $table->foreign('group_id')->references('id')->on('groups');
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
        Schema::dropIfExists('products');
    }
};
