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
        Schema::create('cargo_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('to_inventory')->constrained('inventories')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('request_status_id')->constrained();
            $table->foreignId('employee_id')->constrained();
            $table->string('status');
            $table->smallInteger('recieved_packages');
            $table->timestamp('request_date');
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
        Schema::dropIfExists('cargo_requests');
    }
};
