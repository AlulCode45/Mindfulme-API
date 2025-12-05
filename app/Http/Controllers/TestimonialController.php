<?php

namespace App\Http\Controllers;

use App\Models\Testimonials;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TestimonialController extends Controller
{
    /**
     * Display a listing of the testimonials.
     */
    public function index()
    {
        $testimonials = Testimonials::with('user')
            ->where('approval_status', 'approved')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $testimonials,
            'message' => 'Testimonials retrieved successfully'
        ]);
    }

    /**
     * Store a newly created testimonial.
     */
    public function store(Request $request)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:1000',
            'anonymous' => 'boolean',
        ]);

        $user = Auth::user();

        $testimonial = Testimonials::create([
            'user_id' => $user->uuid, // Use UUID instead of ID
            'rating' => $request->rating,
            'title' => $request->title,
            'content' => $request->content,
            'user_name' => $request->user_name ?? ($request->anonymous ? 'Anonymous' : $user->name),
            'anonymous' => $request->anonymous ?? false,
            'approval_status' => 'pending',
        ]);

        return response()->json([
            'data' => $testimonial,
            'message' => 'Testimonial submitted successfully and is pending approval'
        ], 201);
    }

    /**
     * Display the specified testimonial.
     */
    public function show(Testimonials $testimonial)
    {
        $testimonial->load('user');

        return response()->json([
            'data' => $testimonial,
            'message' => 'Testimonial retrieved successfully'
        ]);
    }

    /**
     * Update the specified testimonial.
     */
    public function update(Request $request, Testimonials $testimonial)
    {
        // Only allow user to update their own testimonials
        if ($testimonial->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'rating' => 'integer|min:1|max:5',
            'title' => 'string|max:255',
            'content' => 'string|max:1000',
            'anonymous' => 'boolean',
        ]);

        $testimonial->update($request->all());

        return response()->json([
            'data' => $testimonial,
            'message' => 'Testimonial updated successfully'
        ]);
    }

    /**
     * Remove the specified testimonial.
     */
    public function destroy(Testimonials $testimonial)
    {
        // Only allow user to delete their own testimonials
        if ($testimonial->user_id !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $testimonial->delete();

        return response()->json([
            'message' => 'Testimonial deleted successfully'
        ]);
    }

    /**
     * Get user's testimonials.
     */
    public function userTestimonials()
    {
        $testimonials = Testimonials::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $testimonials,
            'message' => 'User testimonials retrieved successfully'
        ]);
    }

    /**
     * Get user testimonials by UUID.
     */
    public function getUserTestimonials($uuid)
    {
        try {
            $testimonials = Testimonials::with('user')
                ->whereHas('user', function ($query) use ($uuid) {
                    $query->where('uuid', $uuid);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $testimonials,
                'message' => 'User testimonials retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user testimonials',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display all testimonials (for admin).
     */
    public function indexAll()
    {
        $testimonials = Testimonials::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $testimonials,
            'message' => 'All testimonials retrieved successfully'
        ]);
    }
}