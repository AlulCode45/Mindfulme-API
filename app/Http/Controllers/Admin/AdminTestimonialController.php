<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonials;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminTestimonialController extends Controller
{
    /**
     * Display a listing of all testimonials (including pending ones).
     */
    public function index(Request $request)
    {
        $query = Testimonials::with('user');

        // Filter by approval status if provided
        if ($request->has('status')) {
            $query->where('approval_status', $request->status);
        }

        $testimonials = $query->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $testimonials,
            'message' => 'Admin testimonials retrieved successfully'
        ]);
    }

    /**
     * Update the approval status of a testimonial.
     */
    public function updateApproval(Request $request, Testimonials $testimonial)
    {
        $request->validate([
            'approval_status' => 'required|in:approved,rejected,pending',
        ]);

        $testimonial->update([
            'approval_status' => $request->approval_status,
        ]);

        return response()->json([
            'data' => $testimonial,
            'message' => 'Testimonial approval status updated successfully'
        ]);
    }

    /**
     * Get testimonials statistics.
     */
    public function getStats()
    {
        $total = Testimonials::count();
        $approved = Testimonials::where('approval_status', 'approved')->count();
        $pending = Testimonials::where('approval_status', 'pending')->count();
        $rejected = Testimonials::where('approval_status', 'rejected')->count();

        return response()->json([
            'data' => [
                'total' => $total,
                'approved' => $approved,
                'pending' => $pending,
                'rejected' => $rejected,
            ],
            'message' => 'Testimonial stats retrieved successfully'
        ]);
    }

    /**
     * Delete a testimonial.
     */
    public function destroy(Testimonials $testimonial)
    {
        $testimonial->delete();

        return response()->json([
            'message' => 'Testimonial deleted successfully'
        ]);
    }
}