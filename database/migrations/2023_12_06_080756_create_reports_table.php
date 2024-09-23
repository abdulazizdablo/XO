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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained('employees');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->foreignId('order_id')->nullable()->constrained('orders');
            $table->foreignId('reply_by_id')->nullable()->constrained('employees');
            $table->text('content');
            $table->text('reply')->nullable();
            $table->enum('status',['open','solved']);
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
        Schema::dropIfExists('reports');
    }
};
