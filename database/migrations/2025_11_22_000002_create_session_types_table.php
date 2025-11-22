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
        Schema::create('session_types', function (Blueprint $table) {
            $table->uuid('session_type_id')->primary();
            $table->string('name');
            $table->text('description');
            $table->integer('duration_minutes'); // Duration in minutes
            $table->decimal('price', 10, 2); // Base price
            $table->enum('consultation_type', ['individual', 'couples', 'family', 'group']);
            $table->string('color')->nullable(); // For UI display
            $table->boolean('is_active')->default(true);
            $table->integer('max_participants')->default(1); // For group sessions
            $table->text('requirements')->nullable(); // Special requirements
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_types');
    }
};