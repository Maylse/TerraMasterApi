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
        Schema::create('consultation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finder_id')->constrained('users')->onDelete('cascade'); // Finder who made the request
            $table->foreignId('expert_id')->constrained('users')->onDelete('cascade'); // Expert to whom the request is sent
            $table->text('message')->nullable(); // Optional message from the finder
            $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending'); // Request status
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultation_requests');
    }
};
