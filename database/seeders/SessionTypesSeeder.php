<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SessionType;
use Illuminate\Support\Facades\DB;

class SessionTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing session types
        DB::table('session_types')->delete();

        $sessionTypes = [
            [
                'name' => 'Individual Counseling',
                'description' => 'One-on-one counseling session with a psychologist for personal mental health support.',
                'duration_minutes' => 60,
                'price' => 250000.00,
                'consultation_type' => 'individual',
                'color' => '#3B82F6',
                'max_participants' => 1,
                'requirements' => 'No specific requirements. Suitable for all individuals seeking mental health support.'
            ],
            [
                'name' => 'Couples Counseling',
                'description' => 'Counseling session for couples to improve relationship dynamics and communication.',
                'duration_minutes' => 90,
                'price' => 450000.00,
                'consultation_type' => 'couples',
                'color' => '#EC4899',
                'max_participants' => 2,
                'requirements' => 'Both partners should attend the session together.'
            ],
            [
                'name' => 'Family Counseling',
                'description' => 'Family therapy session to address family dynamics, conflicts, and improve communication.',
                'duration_minutes' => 120,
                'price' => 600000.00,
                'consultation_type' => 'family',
                'color' => '#10B981',
                'max_participants' => 8,
                'requirements' => 'At least 2 family members should attend. Children under 12 should have parental consent.'
            ],
            [
                'name' => 'Group Therapy',
                'description' => 'Group counseling session with multiple participants sharing similar experiences and challenges.',
                'duration_minutes' => 90,
                'price' => 150000.00,
                'consultation_type' => 'group',
                'color' => '#F59E0B',
                'max_participants' => 12,
                'requirements' => 'Participants must be willing to share experiences in a group setting.'
            ],
            [
                'name' => 'Express Individual Session',
                'description' => 'Quick 30-minute session for immediate concerns and brief check-ins.',
                'duration_minutes' => 30,
                'price' => 150000.00,
                'consultation_type' => 'individual',
                'color' => '#8B5CF6',
                'max_participants' => 1,
                'requirements' => 'Suitable for follow-up sessions or quick consultations.'
            ],
            [
                'name' => 'Intensive Individual Session',
                'description' => 'Extended individual session for deep-dive therapy and complex issues.',
                'duration_minutes' => 120,
                'price' => 500000.00,
                'consultation_type' => 'individual',
                'color' => '#EF4444',
                'max_participants' => 1,
                'requirements' => 'Recommended for complex cases requiring extended discussion time.'
            ],
            [
                'name' => 'Career Counseling',
                'description' => 'Specialized session focusing on career development, work stress, and professional challenges.',
                'duration_minutes' => 60,
                'price' => 300000.00,
                'consultation_type' => 'individual',
                'color' => '#06B6D4',
                'max_participants' => 1,
                'requirements' => 'Bring any relevant career documents or questions you want to discuss.'
            ],
            [
                'name' => 'Adolescent Counseling',
                'description' => 'Specialized counseling for teenagers and adolescents dealing with youth-specific challenges.',
                'duration_minutes' => 45,
                'price' => 200000.00,
                'consultation_type' => 'individual',
                'color' => '#84CC16',
                'max_participants' => 1,
                'requirements' => 'For ages 13-17. Parental consent may be required for certain topics.'
            ]
        ];

        foreach ($sessionTypes as $type) {
            SessionType::create([
                'session_type_id' => (string) \Illuminate\Support\Str::uuid(),
                'name' => $type['name'],
                'description' => $type['description'],
                'duration_minutes' => $type['duration_minutes'],
                'price' => $type['price'],
                'consultation_type' => $type['consultation_type'],
                'color' => $type['color'],
                'max_participants' => $type['max_participants'],
                'requirements' => $type['requirements'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $this->command->info('Session Types seeded successfully!');
    }
}