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
        Schema::create('reviews', function (Blueprint $table) {
            $table->uuid('review_id')->primary();
            $table->uuid('psychologist_id');
            $table->uuid('user_id');
            $table->uuid('appointment_id');
            $table->integer('rating'); // 1-5 stars
            $table->text('review_text')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('is_verified')->default(true); // Auto-verify from completed appointments
            $table->text('response')->nullable(); // Psychologist's response to review
            $table->dateTime('response_date')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('psychologist_id')->references('uuid')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('uuid')->on('users')->onDelete('cascade');
            $table->foreign('appointment_id')->references('appointment_id')->on('appointments')->onDelete('cascade');

            // Indexes
            $table->index(['psychologist_id', 'rating']);
            $table->index('user_id');
            $table->index('appointment_id');
            $table->index('created_at');

            // Ensure one review per appointment per user
            $table->unique(['appointment_id', 'user_id'], 'unique_appointment_user_review');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
