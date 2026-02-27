<?php

namespace App\Http\Controllers;

use App\Models\Testimonials;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TestimonialController extends Controller
{
    /**
     * Display a listing of approved testimonials (authenticated).
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
     * Public endpoint: approved testimonials (no auth required).
     */
    public function getApproved()
    {
        $testimonials = Testimonials::with('user')
            ->where('approval_status', 'approved')
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $testimonials,
            'message' => 'Approved testimonials retrieved successfully'
        ]);
    }

    /**
     * Store a newly created testimonial (supports optional media upload).
     */
    public function store(Request $request)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:1000',
            'anonymous' => 'boolean',
            'media' => 'nullable|file|max:51200|mimes:jpeg,png,jpg,gif,webp,mp4,mov,avi,mkv,webm',
        ]);

        $user = Auth::user();

        $mediaUrl = null;
        $mediaType = null;

        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $mime = $file->getMimeType();
            $mediaType = str_starts_with($mime, 'video/') ? 'video' : 'image';
            $filename = 'testimonials/' . uniqid('media_') . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public', $filename);
            $mediaUrl = Storage::disk('public')->url($filename);
        }

        $testimonial = Testimonials::create([
            'user_id' => $user->uuid,
            'rating' => $request->rating,
            'title' => $request->title,
            'content' => $request->content,
            'user_name' => $request->user_name ?? ($request->anonymous ? 'Anonymous' : $user->name),
            'anonymous' => $request->boolean('anonymous', false),
            'approval_status' => 'pending',
            'media_url' => $mediaUrl,
            'media_type' => $mediaType,
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
        if ($testimonial->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
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
        if ($testimonial->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $testimonial->delete();

        return response()->json(['message' => 'Testimonial deleted successfully']);
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
                'message' => 'Error fetching testimonials: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all testimonials (admin).
     */
    public function indexAll()
    {
        $testimonials = Testimonials::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $testimonials,
            'message' => 'All testimonials retrieved successfully'
        ]);
    }
}