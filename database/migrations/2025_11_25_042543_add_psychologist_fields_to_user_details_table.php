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
        Schema::table('user_details', function (Blueprint $table) {
            $table->string('license_number')->nullable()->after('bio');
            $table->text('education')->nullable()->after('license_number');
            $table->text('specialization')->nullable()->after('education');
            $table->integer('experience_years')->nullable()->after('specialization');
            $table->string('clinic_name')->nullable()->after('experience_years');
            $table->text('clinic_address')->nullable()->after('clinic_name');
            $table->decimal('consultation_fee', 10, 2)->nullable()->after('clinic_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_details', function (Blueprint $table) {
            $table->dropColumn([
                'license_number',
                'education',
                'specialization',
                'experience_years',
                'clinic_name',
                'clinic_address',
                'consultation_fee'
            ]);
        });
    }
};
