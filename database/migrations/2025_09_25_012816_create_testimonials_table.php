<?php

use App\Enums\TestimonialApprovalStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->uuid('testimonial_id')->primary();
            $table->foreignUuid('user_id')->constrained('users', 'uuid')->onDelete('cascade');
            $table->text('content');
            $table->enum('status', TestimonialApprovalStatus::cases())->default(TestimonialApprovalStatus::PENDING);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
