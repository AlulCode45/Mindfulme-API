<?php

use App\Enums\AppointmentStatus;
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
        Schema::table('appointments', function (Blueprint $table) {
            // Add session_type_id if it doesn't exist (allow standalone sessions)
            $table->foreignUuid('session_type_id')->nullable()->after('psychologist_id')
                  ->constrained('session_types', 'session_type_id')->onDelete('set null');

            // Make complaint_id nullable for standalone sessions
            $table->foreignUuid('complaint_id')->nullable()->change();

            // Add additional session management fields
            $table->string('session_title')->nullable()->after('user_id'); // Custom title for standalone sessions
            $table->text('session_description')->nullable()->after('session_title');
            $table->decimal('price', 10, 2)->nullable()->after('status'); // Custom pricing
            $table->string('currency', 3)->default('IDR')->after('price');
            $table->text('psychologist_notes')->nullable()->after('meeting_link'); // Notes for psychologist
            $table->text('user_notes')->nullable()->after('psychologist_notes'); // Notes from user
            $table->enum('payment_status', ['pending', 'paid', 'refunded'])->default('pending')->after('user_notes');
            $table->timestamp('payment_paid_at')->nullable()->after('payment_status');
            $table->text('cancellation_reason')->nullable()->after('payment_paid_at');
            $table->timestamp('canceled_at')->nullable()->after('cancellation_reason');
            $table->timestamp('reminded_at')->nullable()->after('canceled_at'); // For reminder system
            $table->json('participants')->nullable()->after('reminded_at'); // For group sessions

            // Add indexes for better performance
            $table->index(['psychologist_id', 'start_time']);
            $table->index(['user_id', 'start_time']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['session_type_id']);
            $table->dropColumn([
                'session_type_id',
                'session_title',
                'session_description',
                'price',
                'currency',
                'psychologist_notes',
                'user_notes',
                'payment_status',
                'payment_paid_at',
                'cancellation_reason',
                'canceled_at',
                'reminded_at',
                'participants'
            ]);

            // Drop indexes
            $table->dropIndex(['psychologist_id', 'start_time']);
            $table->dropIndex(['user_id', 'start_time']);
            $table->dropIndex(['status']);

            // Make complaint_id not nullable again
            $table->foreignUuid('complaint_id')->nullable(false)->change();
        });
    }
};