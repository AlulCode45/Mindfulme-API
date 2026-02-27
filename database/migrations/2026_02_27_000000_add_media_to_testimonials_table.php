<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->string('media_url')->nullable()->after('anonymous');
            $table->string('media_type')->nullable()->after('media_url'); // 'image' or 'video'
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropColumn(['media_url', 'media_type']);
        });
    }
};
