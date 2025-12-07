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
            // Make author_id nullable for volunteer submissions
            $table->uuid('author_id')->nullable()->change();

            $table->string('author_name')->nullable()->after('author_id');
            $table->string('author_email')->nullable()->after('author_name');
            $table->string('author_phone')->nullable()->after('author_email');
            $table->string('type')->default('internal')->after('author_phone'); // To distinguish between internal and volunteer submissions
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['author_name', 'author_email', 'author_phone', 'type']);
        });
    }
};