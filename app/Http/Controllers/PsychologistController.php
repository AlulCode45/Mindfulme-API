<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\Appointments;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PsychologistController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
    }

    /**
     * Get all psychologists with filtering and search capabilities
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'min_rating' => 'nullable|numeric|min:0|max:5',
            'max_fee' => 'nullable|numeric|min:0',
            'sort_by' => 'nullable|in:name,experience,fee,rating,session_count',
            'sort_order' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:50',
            'page' => 'nullable|integer|min:1'
        ]);

        try {
            $query = User::role('psychologist')
                ->with('detail')
                ->select('users.uuid', 'users.name', 'users.email', 'users.created_at');

            // Apply search filters
            if (!empty($validated['search'])) {
                $searchTerm = '%' . $validated['search'] . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('users.name', 'LIKE', $searchTerm)
                      ->orWhereHas('detail', function ($subQ) use ($searchTerm) {
                          $subQ->where('specialization', 'LIKE', $searchTerm)
                               ->orWhere('bio', 'LIKE', $searchTerm);
                      });
                });
            }

            // Filter by specialization
            if (!empty($validated['specialization'])) {
                $query->whereHas('detail', function ($q) use ($validated) {
                    $q->where('specialization', 'LIKE', '%' . $validated['specialization'] . '%');
                });
            }

            // Filter by consultation fee
            if (!empty($validated['max_fee'])) {
                $query->whereHas('detail', function ($q) use ($validated) {
                    $q->where('consultation_fee', '<=', $validated['max_fee']);
                });
            }

            // Add rating and session count through subqueries (placeholder for now)
            $query->addSelect([
                'session_count' => Appointments::selectRaw('COUNT(*)')
                    ->whereColumn('psychologist_id', 'users.uuid')
                    ->whereIn('status', ['completed', 'scheduled'])
                    ->limit(1),
                'completed_sessions' => Appointments::selectRaw('COUNT(*)')
                    ->whereColumn('psychologist_id', 'users.uuid')
                    ->where('status', 'completed')
                    ->limit(1),
            ]);

            // Apply sorting
            $sortBy = $validated['sort_by'] ?? 'name';
            $sortOrder = $validated['sort_order'] ?? 'asc';

            switch ($sortBy) {
                case 'experience':
                    $query->orderBy('detail.experience_years', $sortOrder);
                    break;
                case 'fee':
                    $query->orderBy('detail.consultation_fee', $sortOrder);
                    break;
                case 'session_count':
                    $query->orderBy('session_count', $sortOrder);
                    break;
                case 'rating':
                    // For now, sort by completed sessions as a proxy for rating
                    $query->orderBy('completed_sessions', $sortOrder);
                    break;
                default:
                    $query->orderBy('users.name', $sortOrder);
                    break;
            }

            // Join with user detail for sorting
            $query->leftJoin('user_details', 'users.uuid', '=', 'user_details.user_id');

            $psychologists = $query->paginate($validated['per_page'] ?? 12);

            // Transform the results
            $psychologists->getCollection()->transform(function ($psychologist) {
                return [
                    'uuid' => $psychologist->uuid,
                    'name' => $psychologist->name,
                    'email' => $psychologist->email,
                    'photo' => $psychologist->detail?->photo,
                    'specialization' => $psychologist->detail?->specialization,
                    'education' => $psychologist->detail?->education,
                    'experience_years' => $psychologist->detail?->experience_years,
                    'consultation_fee' => $psychologist->detail?->consultation_fee,
                    'license_number' => $psychologist->detail?->license_number,
                    'bio' => $psychologist->detail?->bio,
                    'clinic_name' => $psychologist->detail?->clinic_name,
                    'clinic_address' => $psychologist->detail?->clinic_address,
                    'session_count' => $psychologist->session_count ?? 0,
                    'completed_sessions' => $psychologist->completed_sessions ?? 0,
                    'rating' => $this->calculatePsychologistRating($psychologist->uuid),
                    'availability_status' => $this->getPsychologistAvailabilityStatus($psychologist->uuid),
                    'member_since' => $psychologist->created_at->format('Y-m-d')
                ];
            });

            return ResponseHelper::success($psychologists, 'Psychologists retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get detailed information about a specific psychologist
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            $psychologist = User::role('psychologist')
                ->with('detail')
                ->where('uuid', $uuid)
                ->first();

            if (!$psychologist) {
                return ResponseHelper::error('Psychologist not found', 404);
            }

            // Get detailed statistics
            $sessionStats = $this->getPsychologistSessionStats($uuid);
            $reviews = $this->getPsychologistReviews($uuid);
            $availability = $this->getPsychologistUpcomingAvailability($uuid);

            $psychologistData = [
                'uuid' => $psychologist->uuid,
                'name' => $psychologist->name,
                'email' => $psychologist->email,
                'photo' => $psychologist->detail?->photo,
                'specialization' => $psychologist->detail?->specialization,
                'education' => $psychologist->detail?->education,
                'experience_years' => $psychologist->detail?->experience_years,
                'consultation_fee' => $psychologist->detail?->consultation_fee,
                'license_number' => $psychologist->detail?->license_number,
                'bio' => $psychologist->detail?->bio,
                'clinic_name' => $psychologist->detail?->clinic_name,
                'clinic_address' => $psychologist->detail?->clinic_address,
                'phone' => $psychologist->detail?->phone,
                'date_of_birth' => $psychologist->detail?->date_of_birth,
                'member_since' => $psychologist->created_at->format('Y-m-d'),
                'statistics' => $sessionStats,
                'reviews' => $reviews,
                'availability' => $availability
            ];

            return ResponseHelper::success($psychologistData, 'Psychologist details retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Calculate psychologist rating based on actual reviews
     */
    private function calculatePsychologistRating(string $psychologistId): float
    {
        $ratingData = Review::where('psychologist_id', $psychologistId)
            ->verified()
            ->avg('rating');

        return $ratingData ? (float) number_format($ratingData, 1) : 0.0;
    }

    /**
     * Get psychologist availability status
     */
    private function getPsychologistAvailabilityStatus(string $psychologistId): string
    {
        $hasUpcomingAvailability = DB::table('psychologist_availabilities')
            ->where('psychologist_id', $psychologistId)
            ->where('is_available', true)
            ->where(function ($query) {
                $query->whereNull('effective_to')
                      ->orWhere('effective_to', '>=', now());
            })
            ->exists();

        return $hasUpcomingAvailability ? 'available' : 'unavailable';
    }

    /**
     * Get detailed session statistics for a psychologist
     */
    private function getPsychologistSessionStats(string $psychologistId): array
    {
        $stats = Appointments::where('psychologist_id', $psychologistId)
            ->selectRaw('
                COUNT(*) as total_sessions,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_sessions,
                SUM(CASE WHEN status = "scheduled" THEN 1 ELSE 0 END) as scheduled_sessions,
                SUM(CASE WHEN status = "canceled" THEN 1 ELSE 0 END) as canceled_sessions,
                AVG(price) as average_session_fee
            ')
            ->first();

        return [
            'total_sessions' => (int) $stats->total_sessions,
            'completed_sessions' => (int) $stats->completed_sessions,
            'scheduled_sessions' => (int) $stats->scheduled_sessions,
            'canceled_sessions' => (int) $stats->canceled_sessions,
            'completion_rate' => $stats->total_sessions > 0
                ? round(($stats->completed_sessions / $stats->total_sessions) * 100, 2)
                : 0,
            'average_session_fee' => $stats->average_session_fee ? (float) $stats->average_session_fee : 0
        ];
    }

    /**
     * Get psychologist reviews
     */
    private function getPsychologistReviews(string $psychologistId): array
    {
        $reviews = Review::where('psychologist_id', $psychologistId)
            ->verified()
            ->with(['user:uuid,name'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $stats = Review::where('psychologist_id', $psychologistId)
            ->verified()
            ->selectRaw('
                COUNT(*) as total_reviews,
                AVG(rating) as average_rating
            ')
            ->first();

        return [
            'average_rating' => $stats->average_rating ? (float) number_format($stats->average_rating, 1) : 0.0,
            'total_reviews' => $stats->total_reviews ?? 0,
            'recent_reviews' => $reviews->map(function ($review) {
                return [
                    'review_id' => $review->review_id,
                    'rating' => $review->rating,
                    'review_text' => $review->review_text,
                    'is_anonymous' => $review->is_anonymous,
                    'client_name' => $review->display_user,
                    'date' => $review->created_at->format('Y-m-d'),
                    'response' => $review->response
                ];
            })->toArray()
        ];
    }

    /**
     * Get psychologist upcoming availability slots
     */
    private function getPsychologistUpcomingAvailability(string $psychologistId): array
    {
        $availabilities = DB::table('psychologist_availabilities')
            ->where('psychologist_id', $psychologistId)
            ->where('is_available', true)
            ->where(function ($query) {
                $query->whereNull('effective_to')
                      ->orWhere('effective_to', '>=', now());
            })
            ->get([
                'day_of_week',
                'start_time',
                'end_time',
                'notes'
            ]);

        return $availabilities->toArray();
    }
}