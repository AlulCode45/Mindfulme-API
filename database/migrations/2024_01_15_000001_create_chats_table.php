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
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->string('complaint_id');  // Foreign key to complaints table (UUID)
            $table->string('sender_id');     // Foreign key to users table (UUID)
            $table->enum('sender_type', ['user', 'admin', 'psychologist']);
            $table->text('message_text');
            $table->enum('message_type', ['text', 'image', 'file']);
            $table->string('file_url')->nullable();
            $table->string('file_name')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            // Indexes
            $table->index('complaint_id');
            $table->index('sender_id');
            $table->index('sender_type');
            $table->index('is_read');
            $table->index(['complaint_id', 'created_at']);
            $table->index(['sender_id', 'is_read']);
        });

        // Add foreign key constraints if the tables exist
        // Note: Comment out if complaints or users tables are in different database
        /*
        Schema::table('chats', function (Blueprint $table) {
            $table->foreign('complaint_id')->references('uuid')->on('complaints')->onDelete('cascade');
            $table->foreign('sender_id')->references('uuid')->on('users')->onDelete('cascade');
        });
        */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};