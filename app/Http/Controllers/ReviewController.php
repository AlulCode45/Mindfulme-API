<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Appointments;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get reviews for a specific psychologist
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'psychologist_id' => 'required|exists:users,uuid',
            'rating' => 'nullable|integer|min:1|max:5',
            'verified_only' => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:50',
            'page' => 'nullable|integer|min:1'
        ]);

        try {
            $query = Review::with(['user:uuid,name', 'appointment:appointment_id,session_type_id'])
                ->where('psychologist_id', $validated['psychologist_id']);

            // Filter by rating
            if (isset($validated['rating'])) {
                $query->where('rating', $validated['rating']);
            }

            // Filter verified reviews only
            if (filter_var($validated['verified_only'] ?? true, FILTER_VALIDATE_BOOLEAN)) {
                $query->verified();
            }

            $reviews = $query->orderBy('created_at', 'desc')
                ->paginate($validated['per_page'] ?? 10);

            // Transform reviews
            $reviews->getCollection()->transform(function ($review) {
                return [
                    'review_id' => $review->review_id,
                    'rating' => $review->rating,
                    'review_text' => $review->review_text,
                    'is_anonymous' => $review->is_anonymous,
                    'display_user' => $review->display_user,
                    'response' => $review->response,
                    'response_date' => $review->response_date,
                    'created_at' => $review->created_at->format('Y-m-d H:i:s'),
                    'user' => $review->is_anonymous ? null : [
                        'uuid' => $review->user->uuid,
                        'name' => $review->user->name
                    ]
                ];
            });

            return ResponseHelper::success($reviews, 'Reviews retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Create a new review
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'appointment_id' => 'required|exists:appointments,appointment_id',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'nullable|string|max:1000',
            'is_anonymous' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $appointment = Appointments::findOrFail($validated['appointment_id']);
            $user = auth()->user();

            // Verify the appointment belongs to the user
            if ($appointment->user_id !== $user->uuid) {
                return ResponseHelper::error('You can only review your own appointments', 403);
            }

            // Verify the appointment is completed
            if ($appointment->status !== 'completed') {
                return ResponseHelper::error('You can only review completed appointments', 400);
            }

            // Check if review already exists
            $existingReview = Review::where('appointment_id', $appointment->appointment_id)
                ->where('user_id', $user->uuid)
                ->first();

            if ($existingReview) {
                return ResponseHelper::error('You have already reviewed this appointment', 400);
            }

            // Create the review
            $review = Review::create([
                'psychologist_id' => $appointment->psychologist_id,
                'user_id' => $user->uuid,
                'appointment_id' => $appointment->appointment_id,
                'rating' => $validated['rating'],
                'review_text' => $validated['review_text'],
                'is_anonymous' => $validated['is_anonymous'] ?? false
            ]);

            DB::commit();

            // Load relationships for response
            $review->load(['user:uuid,name', 'appointment:appointment_id,session_type_id']);

            return ResponseHelper::success($review, 'Review created successfully', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get a specific review
     */
    public function show(string $reviewId): JsonResponse
    {
        try {
            $review = Review::with([
                'user:uuid,name',
                'psychologist:uuid,name',
                'appointment:appointment_id,session_type_id,start_time'
            ])->findOrFail($reviewId);

            $user = auth()->user();

            // Check if user has access to this review
            if (!$user->hasRole('superadmin') &&
                $review->user_id !== $user->uuid &&
                $review->psychologist_id !== $user->uuid) {
                return ResponseHelper::error('Unauthorized', 403);
            }

            return ResponseHelper::success($review, 'Review retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 404);
        }
    }

    /**
     * Update a review
     */
    public function update(Request $request, string $reviewId): JsonResponse
    {
        $validated = $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'review_text' => 'sometimes|string|max:1000',
            'is_anonymous' => 'sometimes|boolean',
            'response' => 'sometimes|string|max:1000'
        ]);

        try {
            $review = Review::findOrFail($reviewId);
            $user = auth()->user();

            // Check permissions
            if (!$review->canBeModifiedBy($user)) {
                return ResponseHelper::error('Unauthorized', 403);
            }

            // Only users can update their own review content
            if ($review->user_id === $user->uuid) {
                if (isset($validated['response'])) {
                    return ResponseHelper::error('Users cannot add responses to reviews', 403);
                }
                $review->update([
                    'rating' => $validated['rating'] ?? $review->rating,
                    'review_text' => $validated['review_text'] ?? $review->review_text,
                    'is_anonymous' => $validated['is_anonymous'] ?? $review->is_anonymous
                ]);
            }

            // Only psychologists can add responses
            if ($review->psychologist_id === $user->uuid) {
                if (isset($validated['rating']) || isset($validated['review_text']) || isset($validated['is_anonymous'])) {
                    return ResponseHelper::error('Psychologists can only add responses to reviews', 403);
                }
                $review->update([
                    'response' => $validated['response'] ?? $review->response,
                    'response_date' => isset($validated['response']) ? now() : $review->response_date
                ]);
            }

            // Admins can update anything
            if ($user->hasRole('superadmin')) {
                $review->update($validated);
            }

            $review->load(['user:uuid,name', 'psychologist:uuid,name', 'appointment']);

            return ResponseHelper::success($review, 'Review updated successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 404);
        }
    }

    /**
     * Delete a review
     */
    public function destroy(string $reviewId): JsonResponse
    {
        try {
            $review = Review::findOrFail($reviewId);
            $user = auth()->user();

            // Check permissions
            if (!$review->canBeModifiedBy($user)) {
                return ResponseHelper::error('Unauthorized', 403);
            }

            // Only users and admins can delete reviews
            if ($review->psychologist_id === $user->uuid && !$user->hasRole('superadmin')) {
                return ResponseHelper::error('Psychologists cannot delete reviews', 403);
            }

            $review->delete();

            return ResponseHelper::success(null, 'Review deleted successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 404);
        }
    }

    /**
     * Get review statistics for a psychologist
     */
    public function getPsychologistStats(string $psychologistId): JsonResponse
    {
        try {
            $stats = Review::where('psychologist_id', $psychologistId)
                ->verified()
                ->selectRaw('
                    COUNT(*) as total_reviews,
                    AVG(rating) as average_rating,
                    COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star_count,
                    COUNT(CASE WHEN rating = 4 THEN 1 END) as four_star_count,
                    COUNT(CASE WHEN rating = 3 THEN 1 END) as three_star_count,
                    COUNT(CASE WHEN rating = 2 THEN 1 END) as two_star_count,
                    COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star_count
                ')
                ->first();

            return ResponseHelper::success($stats, 'Review statistics retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get reviews written by the authenticated user
     */
    public function getUserReviews(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => 'nullable|integer|min:1|max:50',
            'page' => 'nullable|integer|min:1'
        ]);

        try {
            $reviews = Review::with([
                'psychologist:uuid,name',
                'appointment:appointment_id,session_type_id,start_time'
            ])
                ->where('user_id', auth()->user()->uuid)
                ->orderBy('created_at', 'desc')
                ->paginate($validated['per_page'] ?? 10);

            return ResponseHelper::success($reviews, 'User reviews retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
}