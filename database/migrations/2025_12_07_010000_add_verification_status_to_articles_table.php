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
        Schema::table('articles', function (Blueprint $table) {
            $table->enum('verification_status', ['pending', 'approved', 'rejected'])->default('pending')->after('status');
            $table->text('verification_notes')->nullable()->after('verification_status');
            $table->uuid('verified_by')->nullable()->after('verification_notes');
            $table->timestamp('verified_at')->nullable()->after('verified_by');

            // Add foreign key for verified_by
            $table->foreign('verified_by')->references('uuid')->on('users')->onDelete('set null');

            // Add indexes
            $table->index(['verification_status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropIndex(['verification_status', 'created_at']);
            $table->dropColumn(['verification_status', 'verification_notes', 'verified_by', 'verified_at']);
        });
    }
};