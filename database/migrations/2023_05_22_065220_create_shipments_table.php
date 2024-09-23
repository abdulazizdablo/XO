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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade')->onUpdate('cascade');
            $table->string('type');
            $table->date('date');
            $table->string('time')->nullable();
            $table->string('city')->nullable();
            $table->string('street')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('lat')->nullable();
            $table->string('long')->nullable();
            $table->string('additional_details')->nullable();
            $table->string('receiver_first_name')->nullable();
            $table->string('receiver_last_name')->nullable();
            $table->string('receiver_phone')->nullable();
            $table->string('receiver_phone2')->nullable();
            $table->boolean('is_delivered')->default(false);
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
        Schema::dropIfExists('shipments');
    }
};
