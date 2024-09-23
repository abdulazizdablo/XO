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
        Schema::create('cargo_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cargo_request_id')->nullable()->constrained();
            $table->foreignId('from_inventory')->constrained('inventories');
            $table->smallInteger('sender_packages');
            $table->timestamp('ship_date');
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
        Schema::dropIfExists('cargo_shipments');
    }
};
