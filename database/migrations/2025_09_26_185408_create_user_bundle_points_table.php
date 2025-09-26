<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_bundle_points', function (Blueprint $table) {
            $table->uuid('user_bundle_id')->primary();
            $table->foreignUuid('user_id')->constrained('users', 'uuid')->onDelete('cascade');
            $table->foreignUuid('bundle_package_id')->constrained('bundle_packages', 'bundle_id')->onDelete('cascade');
            $table->integer('bundle_points')->default(0);
            $table->integer('current_points')->default(0);
            $table->date('purchase_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_bundle_points');
    }
};
