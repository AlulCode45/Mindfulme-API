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
            $table->enum('role', ['superadmin', 'user', 'volunteer'])->default('user')->after('email');
            $table->text('address')->nullable()->after('phone');
            $table->text('motivation')->nullable()->after('address');
            $table->enum('volunteer_status', ['pending', 'approved', 'rejected'])->default('pending')->after('motivation');
            $table->text('volunteer_notes')->nullable()->after('volunteer_status');

            // Add indexes
            $table->index(['role', 'volunteer_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'volunteer_status']);
            $table->dropColumn(['role', 'address', 'motivation', 'volunteer_status', 'volunteer_notes']);
        });
    }
};