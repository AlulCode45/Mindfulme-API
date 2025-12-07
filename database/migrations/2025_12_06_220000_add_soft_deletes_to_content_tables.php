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
        // Add deleted_at column to articles
        Schema::table('articles', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add deleted_at column to videos
        Schema::table('videos', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add deleted_at column to content_categories
        Schema::table('content_categories', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add deleted_at column to content_tags
        Schema::table('content_tags', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove deleted_at column from articles
        Schema::table('articles', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Remove deleted_at column from videos
        Schema::table('videos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Remove deleted_at column from content_categories
        Schema::table('content_categories', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Remove deleted_at column from content_tags
        Schema::table('content_tags', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};