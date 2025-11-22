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
        Schema::create('psychologist_availabilities', function (Blueprint $table) {
            $table->uuid('availability_id')->primary();
            $table->foreignUuid('psychologist_id')->constrained('users', 'uuid')->onDelete('cascade');
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_available')->default(true);
            $table->json('break_periods')->nullable(); // Array of break periods
            $table->date('effective_from')->nullable(); // When this availability starts
            $table->date('effective_to')->nullable();   // When this availability ends
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['psychologist_id', 'day_of_week', 'start_time', 'end_time'], 'unique_psychologist_availability');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('psychologist_availabilities');
    }
};