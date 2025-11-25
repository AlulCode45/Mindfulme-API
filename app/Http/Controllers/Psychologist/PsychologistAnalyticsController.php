<?php

namespace App\Http\Controllers\Psychologist;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Appointments;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PsychologistAnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:psychologist|superadmin');
    }

    /**
     * Get psychologist dashboard stats
     */
    public function getStats(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();

            $query = Appointments::where('psychologist_id', $user->uuid);

            // Apply date filters if provided
            if ($request->filled('start_date')) {
                $query->where('start_time', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->where('start_time', '<=', $request->end_date . ' 23:59:59');
            }

            $appointments = $query->get();

            $stats = [
                'totalPatients' => $appointments->pluck('user_id')->unique()->count(),
                'totalSessions' => $appointments->count(),
                'completedSessions' => $appointments->where('status', 'completed')->count(),
                'scheduledSessions' => $appointments->where('status', 'scheduled')->count(),
                'averageRating' => 4.8, // TODO: Calculate from ratings table
                'totalRevenue' => $appointments->sum('price'),
                'monthlyGrowth' => 15.5, // TODO: Calculate from previous month
                'todaySessions' => $appointments->whereDate('start_time', today())->count(),
                'upcomingSessionsThisWeek' => $appointments
                    ->whereBetween('start_time', [now()->startOfWeek(), now()->endOfWeek()])
                    ->where('status', 'scheduled')
                    ->count(),
            ];

            return ResponseHelper::success($stats, 'Psychologist stats retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get session analytics
     */
    public function getSessionAnalytics(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();

            $query = Appointments::where('psychologist_id', $user->uuid);

            // Apply date filters if provided
            if ($request->filled('start_date')) {
                $query->where('start_time', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->where('start_time', '<=', $request->end_date . ' 23:59:59');
            }

            $appointments = $query->with(['sessionType', 'user'])->get();

            // Session analytics
            $sessionsByStatus = $appointments->groupBy('status')->map->count();
            $sessionsByType = $appointments->groupBy('session_type_id')->map->count();

            // Revenue by month (last 12 months)
            $revenueByMonth = $appointments
                ->where('status', 'completed')
                ->groupBy(function ($appointment) {
                    return Carbon::parse($appointment->start_time)->format('Y-m');
                })
                ->map(function ($group) {
                    return [
                        'month' => Carbon::parse($group->first()->start_time)->format('M Y'),
                        'revenue' => $group->sum('price'),
                        'sessions' => $group->count()
                    ];
                })
                ->sortBy('month')
                ->values();

            // Session trend (last 30 days)
            $sessionTrend = [];
            for ($i = 29; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $dayAppointments = $appointments->whereDate('start_time', $date);

                $sessionTrend[] = [
                    'date' => $date,
                    'sessions' => $dayAppointments->count(),
                    'completed' => $dayAppointments->where('status', 'completed')->count()
                ];
            }

            $analytics = [
                'totalSessions' => $appointments->count(),
                'completedSessions' => $appointments->where('status', 'completed')->count(),
                'canceledSessions' => $appointments->where('status', 'canceled')->count(),
                'averageSessionDuration' => 60, // TODO: Calculate from actual session data
                'sessionsByType' => [
                    'individual' => $sessionsByType->get(1, 0), // Assuming type_id 1 is individual
                    'couples' => $sessionsByType->get(2, 0),
                    'family' => $sessionsByType->get(3, 0),
                    'group' => $sessionsByType->get(4, 0),
                ],
                'sessionsByStatus' => [
                    'scheduled' => $sessionsByStatus->get('scheduled', 0),
                    'completed' => $sessionsByStatus->get('completed', 0),
                    'canceled' => $sessionsByStatus->get('canceled', 0),
                ],
                'revenueByMonth' => $revenueByMonth,
                'sessionTrend' => $sessionTrend,
            ];

            return ResponseHelper::success($analytics, 'Session analytics retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get patient analytics
     */
    public function getPatientAnalytics(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();

            $appointments = Appointments::where('psychologist_id', $user->uuid)
                ->with('user')
                ->get();

            $patients = $appointments->pluck('user')->unique('uuid');
            $totalPatients = $patients->count();

            // New patients this month
            $thisMonthPatients = $patients
                ->where('created_at', '>=', now()->startOfMonth())
                ->count();

            // Returning patients (patients with more than one session)
            $patientSessionCounts = $appointments->groupBy('user_id')->map->count();
            $returningPatients = $patientSessionCounts->filter(function ($count) {
                return $count > 1;
            })->count();

            // Patient categories (from complaints)
            $patientCategories = [];
            foreach ($appointments as $appointment) {
                if ($appointment->complaint) {
                    $category = $appointment->complaint->category ?? 'general';
                    $patientCategories[$category] = ($patientCategories[$category] ?? 0) + 1;
                }
            }

            $analytics = [
                'totalPatients' => $totalPatients,
                'newPatientsThisMonth' => $thisMonthPatients,
                'returningPatients' => $returningPatients,
                'patientSatisfaction' => 4.7, // TODO: Calculate from feedback
                'patientsByCategory' => $patientCategories,
                'patientDemographics' => [
                    'ageGroups' => [], // TODO: Calculate from user details
                    'gender' => [], // TODO: Calculate from user details
                    'location' => [], // TODO: Calculate from user details
                ],
            ];

            return ResponseHelper::success($analytics, 'Patient analytics retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get psychologist's patients list
     */
    public function getPatients(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();

            $appointments = Appointments::where('psychologist_id', $user->uuid)
                ->with(['user', 'complaint', 'sessionType'])
                ->orderBy('created_at', 'desc')
                ->get();

            $patients = $appointments->groupBy('user_id')->map(function ($patientAppointments, $userId) {
                $firstAppointment = $patientAppointments->first();
                $user = $firstAppointment->user;

                return [
                    'user_id' => $userId,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->detail?->phone,
                    'avatar' => $user->detail?->photo,
                    'total_sessions' => $patientAppointments->count(),
                    'completed_sessions' => $patientAppointments->where('status', 'completed')->count(),
                    'last_session' => $patientAppointments->max('start_time'),
                    'next_session' => $patientAppointments
                        ->where('status', 'scheduled')
                        ->where('start_time', '>', now())
                        ->min('start_time'),
                    'complaints' => $patientAppointments->pluck('complaint')->unique('complaint_id')->values(),
                    'session_types' => $patientAppointments->pluck('sessionType')->unique('session_type_id')->values(),
                    'status' => $patientAppointments->where('status', 'scheduled')->count() > 0 ? 'active' : 'inactive'
                ];
            })->values();

            // Apply filters
            if ($request->filled('status')) {
                $patients = $patients->where('status', $request->status);
            }

            if ($request->filled('search')) {
                $searchTerm = strtolower($request->search);
                $patients = $patients->filter(function ($patient) use ($searchTerm) {
                    return str_contains(strtolower($patient['name']), $searchTerm) ||
                           str_contains(strtolower($patient['email']), $searchTerm);
                });
            }

            return ResponseHelper::success($patients, 'Patients retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
}