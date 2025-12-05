<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

class CounselorController extends Controller
{
    /**
     * Get available counselors
     */
    public function getAvailable()
    {
        try {
            // For now, return mock data. In a real application,
            // you would fetch this from a counselors table with availability status
            $counselors = [
                [
                    'id' => 'dr-sarah',
                    'name' => 'Dr. Sarah Chen',
                    'specialization' => 'Clinical Psychology',
                    'available' => true,
                    'experience' => '5 years'
                ],
                [
                    'id' => 'dr-ahmad',
                    'name' => 'Dr. Ahmad Faisal',
                    'specialization' => 'Cognitive Behavioral Therapy',
                    'available' => true,
                    'experience' => '7 years'
                ],
                [
                    'id' => 'dr-maya',
                    'name' => 'Dr. Maya Putri',
                    'specialization' => 'Child Psychology',
                    'available' => true,
                    'experience' => '4 years'
                ]
            ];

            return ResponseHelper::success($counselors, 'Available counselors retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage());
        }
    }
}