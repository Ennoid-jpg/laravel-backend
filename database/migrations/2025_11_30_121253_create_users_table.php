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
        Schema::create('users', function (Blueprint $table) {
            $table->integer('id_user')->primary();
            $table->string('FirstName', 50);
            $table->string('LastName', 50);
            $table->string('username', 50)->unique();
            $table->bigInteger('ContactNumber', false, true);
            $table->string('Password', 70);
            $table->enum('role', ['user', 'employee', 'admin'])->default('user');
            $table->string('profile_picture', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
