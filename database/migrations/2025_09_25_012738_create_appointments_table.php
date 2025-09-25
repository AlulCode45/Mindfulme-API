<?php

use App\Enums\AppointmentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->uuid('appointment_id')->primary();
            $table->foreignUuid('complaint_id')->constrained('complaints', 'complaint_id')->onDelete('cascade');
            $table->foreignUuid('psychologist_id')->constrained('users', 'uuid')->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained('users', 'uuid')->onDelete('cascade');
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->enum('status', AppointmentStatus::cases())->default(AppointmentStatus::SCHEDULED);
            $table->string('meeting_link')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
