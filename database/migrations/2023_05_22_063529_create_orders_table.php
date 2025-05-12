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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('address_id')->nullable()->constrained('addresses')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('inventory_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('packaging_id')->nullable();
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();

            $table->string('invoice_number')->nullable();
            $table->dateTime('packed_date')->nullable();
            $table->dateTime('delivery_date')->nullable();
            $table->dateTime('shipping_date')->nullable();
            $table->dateTime('receiving_date')->nullable();
            $table->double('total_price');
            $table->integer('total_quantity');
            $table->string('type')->default('xo-delivery');
            $table->boolean('paid')->default(0);
            $table->boolean('closed')->default(0);
            $table->string('status')->default('processing');
            $table->string('payment_method');
            $table->boolean('need_packaging')->default(0);
            $table->double('shipping_fee');
            $table->string('qr_code');

            $table->foreign('inventory_id')->references('id')->on('inventories');
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->foreign('coupon_id')->references('id')->on('coupons');
            $table->foreign('branch_id')->references('id')->on('branches');
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
        Schema::dropIfExists('orders');
    }
};
