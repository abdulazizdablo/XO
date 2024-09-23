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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('type');
            $table->string('password')->nullable();
            $table->boolean('valid')->default(0);
            $table->integer('used_redemption')->nullable();
            $table->integer('max_redemption')->nullable();
            $table->string('amount_off')->nullable();
            $table->string('status')->nullable();
            $table->integer('percentage')->nullable();
            $table->dateTime('expired_at')->nullable();
            $table->timestamp('last_recharge')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupons');
    }
};
