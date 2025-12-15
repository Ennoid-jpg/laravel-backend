<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('booked', function (Blueprint $table) {
            $table->integer('id_booked')->primary();
            $table->integer('id_user');
            $table->integer('id_drone');
            $table->decimal('price', 10, 2);
            $table->date('return_date');
            $table->time('return_time');
            $table->string('payment_type', 20);
            $table->timestamp('checkout_date')->useCurrent();
            $table->string('receiver_name', 50);
            $table->enum('Status', ['Pending', 'Returned', 'Returned Damaged', 'Cancelled', 'Accepted'])->default('Pending');
            $table->text('item_names')->nullable();
            $table->integer('quantity')->default(1);
            $table->string('item_quantities', 255)->nullable();
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
            $table->foreign('id_drone')->references('id_drone')->on('drones')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booked');
    }
};
