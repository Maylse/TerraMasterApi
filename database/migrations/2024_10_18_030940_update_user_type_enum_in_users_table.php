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
        Schema::table('users', function (Blueprint $table) {
            // Make sure to specify all values needed
            $table->enum('user_type', ['finder', 'expert', 'surveyor'])->default('finder')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // If rolling back, set to a previous state
            $table->enum('user_type', ['finder'])->default('finder')->change();
        });
    }
};
