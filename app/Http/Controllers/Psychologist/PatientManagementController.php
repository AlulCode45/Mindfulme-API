<?php

namespace App\Http\Controllers\Psychologist;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Appointments;
use App\Models\User;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PatientManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:psychologist|superadmin');
    }

    /**
     * Get all patients for the psychologist
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();

            $query = Appointments::where('psychologist_id', $user->uuid)
                ->with(['user', 'complaint', 'sessionType'])
                ->orderBy('created_at', 'desc');

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('date_from')) {
                $query->where('start_time', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->where('start_time', '<=', $request->date_to . ' 23:59:59');
            }

            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->whereHas('user', function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('email', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Paginate results
            $perPage = $request->get('per_page', 15);
            $page = $request->get('page', 1);
            $appointments = $query->paginate($perPage, ['*'], 'page', $page);

            // Group by patient
            $patients = $appointments->getCollection()->groupBy('user_id')->map(function ($patientAppointments, $userId) {
                $firstAppointment = $patientAppointments->first();
                $user = $firstAppointment->user;

                return [
                    'user_id' => $userId,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->detail?->phone,
                    'avatar' => $user->detail?->photo,
                    'date_of_birth' => $user->detail?->date_of_birth,
                    'address' => $user->detail?->address,
                    'total_sessions' => $patientAppointments->count(),
                    'completed_sessions' => $patientAppointments->where('status', 'completed')->count(),
                    'canceled_sessions' => $patientAppointments->where('status', 'canceled')->count(),
                    'scheduled_sessions' => $patientAppointments->where('status', 'scheduled')->count(),
                    'first_session' => $patientAppointments->min('start_time'),
                    'last_session' => $patientAppointments->max('start_time'),
                    'next_session' => $patientAppointments
                        ->where('status', 'scheduled')
                        ->where('start_time', '>', now())
                        ->min('start_time'),
                    'complaints' => $patientAppointments->pluck('complaint')->unique('complaint_id')->values(),
                    'recent_sessions' => $patientAppointments->take(5)->values(),
                    'status' => $this->getPatientStatus($patientAppointments),
                    'progress' => $this->calculatePatientProgress($patientAppointments)
                ];
            })->values();

            return ResponseHelper::success([
                'data' => $patients,
                'pagination' => [
                    'current_page' => $appointments->currentPage(),
                    'last_page' => $appointments->lastPage(),
                    'per_page' => $appointments->perPage(),
                    'total' => $patients->count(),
                ]
            ], 'Patients retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get specific patient details
     */
    public function show(string $userId): JsonResponse
    {
        try {
            $psychologist = auth()->user();

            // Verify the patient belongs to this psychologist
            $hasAccess = Appointments::where('psychologist_id', $psychologist->uuid)
                ->where('user_id', $userId)
                ->exists();

            if (!$hasAccess) {
                return ResponseHelper::error('Patient not found or access denied', 404);
            }

            $patient = User::with(['detail', 'appointments' => function ($query) use ($psychologist) {
                $query->where('psychologist_id', $psychologist->uuid)
                      ->with(['sessionType', 'complaint'])
                      ->orderBy('start_time', 'desc');
            }])->findOrFail($userId);

            $appointments = $patient->appointments;

            $patientData = [
                'user_id' => $patient->uuid,
                'name' => $patient->name,
                'email' => $patient->email,
                'phone' => $patient->detail?->phone,
                'avatar' => $patient->detail?->photo,
                'date_of_birth' => $patient->detail?->date_of_birth,
                'address' => $patient->detail?->address,
                'bio' => $patient->detail?->bio,
                'total_sessions' => $appointments->count(),
                'completed_sessions' => $appointments->where('status', 'completed')->count(),
                'canceled_sessions' => $appointments->where('status', 'canceled')->count(),
                'scheduled_sessions' => $appointments->where('status', 'scheduled')->count(),
                'first_session' => $appointments->min('start_time'),
                'last_session' => $appointments->max('start_time'),
                'next_session' => $appointments
                    ->where('status', 'scheduled')
                    ->where('start_time', '>', now())
                    ->min('start_time'),
                'complaints' => $appointments->pluck('complaint')->unique('complaint_id')->values(),
                'sessions' => $appointments,
                'status' => $this->getPatientStatus($appointments),
                'progress' => $this->calculatePatientProgress($appointments),
                'created_at' => $patient->created_at,
                'email_verified_at' => $patient->email_verified_at
            ];

            return ResponseHelper::success($patientData, 'Patient details retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Add notes to patient session
     */
    public function addSessionNotes(Request $request, string $sessionId): JsonResponse
    {
        $validated = $request->validate([
            'psychologist_notes' => 'required|string|max:2000',
        ]);

        try {
            $psychologist = auth()->user();

            $appointment = Appointments::where('psychologist_id', $psychologist->uuid)
                ->where('appointment_id', $sessionId)
                ->firstOrFail();

            $appointment->update([
                'psychologist_notes' => $validated['psychologist_notes'],
                'updated_at' => now()
            ]);

            return ResponseHelper::success($appointment, 'Session notes added successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get patient session history
     */
    public function getSessionHistory(string $userId, Request $request): JsonResponse
    {
        try {
            $psychologist = auth()->user();

            // Verify access
            $hasAccess = Appointments::where('psychologist_id', $psychologist->uuid)
                ->where('user_id', $userId)
                ->exists();

            if (!$hasAccess) {
                return ResponseHelper::error('Access denied', 403);
            }

            $query = Appointments::where('psychologist_id', $psychologist->uuid)
                ->where('user_id', $userId)
                ->with(['sessionType', 'complaint'])
                ->orderBy('start_time', 'desc');

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('date_from')) {
                $query->where('start_time', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->where('start_time', '<=', $request->date_to . ' 23:59:59');
            }

            $sessions = $query->paginate($request->get('per_page', 15));

            return ResponseHelper::success($sessions, 'Session history retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Determine patient status based on their sessions
     */
    private function getPatientStatus($appointments): string
    {
        $hasUpcoming = $appointments->where('status', 'scheduled')
            ->where('start_time', '>', now())
            ->isNotEmpty();

        $hasRecent = $appointments->where('start_time', '>=', now()->subDays(30))
            ->isNotEmpty();

        if ($hasUpcoming) {
            return 'active';
        } elseif ($hasRecent) {
            return 'recent';
        } else {
            return 'inactive';
        }
    }

    /**
     * Calculate patient progress based on completed sessions
     */
    private function calculatePatientProgress($appointments): array
    {
        $totalSessions = $appointments->count();
        $completedSessions = $appointments->where('status', 'completed')->count();
        $recentSessions = $appointments->where('start_time', '>=', now()->subDays(30))->count();

        return [
            'completion_rate' => $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 100, 1) : 0,
            'engagement_level' => $recentSessions >= 4 ? 'high' : ($recentSessions >= 2 ? 'medium' : 'low'),
            'trend' => $recentSessions > $appointments->where('start_time', '>=', now()->subDays(60))
                                ->where('start_time', '<', now()->subDays(30))->count() ? 'improving' : 'stable'
        ];
    }
}