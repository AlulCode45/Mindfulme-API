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
        Schema::table('complaints', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
            $table->enum('priority', ['low', 'normal', 'urgent'])->default('normal')->after('status');
            $table->enum('classification', ['hukum', 'psikologi'])->nullable()->after('priority');
            $table->text('admin_notes')->nullable()->after('classification');
            $table->date('scheduled_date')->nullable()->after('admin_notes');
            $table->time('scheduled_time')->nullable()->after('scheduled_date');
            $table->string('assigned_to')->nullable()->after('scheduled_time');
            $table->integer('response_count')->default(0)->after('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'priority',
                'classification',
                'admin_notes',
                'scheduled_date',
                'scheduled_time',
                'assigned_to',
                'response_count'
            ]);
        });
    }
};
