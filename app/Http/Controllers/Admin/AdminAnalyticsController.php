<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Appointments;
use App\Models\Complaints;
use App\Models\Review;
use App\Models\Articles;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminAnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get dashboard statistics for admin
     */
    public function getDashboardStats(): JsonResponse
    {
        try {
            // Check if user is admin
            if (auth()->user()->role !== 'superadmin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Admin access required.',
                ], 403);
            }

            // Calculate stats
            $totalUsers = User::count();
            $totalVolunteers = User::where('role', 'volunteer')->count();
            $regularUsers = User::where('role', 'user')->count();
            $activeComplaints = Complaints::whereIn('status', ['new', 'in-progress'])->count();
            $totalSessions = Appointments::count();
            $completedSessions = Appointments::where('status', 'completed')->count();
            $totalReviews = Review::verified()->count();
            $averageRating = Review::verified()->avg('rating') ?? 0;

            // Calculate growth rates (compared to previous month)
            $lastMonthStart = now()->subMonth()->startOfMonth();
            $lastMonthEnd = now()->subMonth()->endOfMonth();
            $currentMonthStart = now()->startOfMonth();

            $lastMonthUsers = User::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
            $currentMonthUsers = User::whereBetween('created_at', [$currentMonthStart, now()])->count();
            $userGrowth = $lastMonthUsers > 0
                ? (($currentMonthUsers - $lastMonthUsers) / $lastMonthUsers) * 100
                : ($currentMonthUsers > 0 ? 100 : 0);

            $lastMonthSessions = Appointments::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
            $currentMonthSessions = Appointments::whereBetween('created_at', [$currentMonthStart, now()])->count();
            $sessionGrowth = $lastMonthSessions > 0
                ? (($currentMonthSessions - $lastMonthSessions) / $lastMonthSessions) * 100
                : ($currentMonthSessions > 0 ? 100 : 0);

            $stats = [
                'users' => [
                    'total' => $totalUsers,
                    'volunteers' => $totalVolunteers,
                    'regular_users' => $regularUsers,
                    'growth_rate' => round($userGrowth, 1)
                ],
                'sessions' => [
                    'total' => $totalSessions,
                    'completed' => $completedSessions,
                    'completion_rate' => $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 100, 1) : 0,
                    'growth_rate' => round($sessionGrowth, 1)
                ],
                'complaints' => [
                    'total' => Complaints::count(),
                    'active' => $activeComplaints,
                    'resolved' => Complaints::where('status', 'resolved')->count(),
                    'new_this_month' => Complaints::whereBetween('created_at', [$currentMonthStart, now()])->count()
                ],
                'reviews' => [
                    'total' => $totalReviews,
                    'average_rating' => round($averageRating, 1),
                    'rating_distribution' => $this->getRatingDistribution()
                ],
                'revenue' => $this->getRevenueStats()
            ];

            return ResponseHelper::success($stats, 'Dashboard statistics retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get recent activity for admin dashboard
     */
    public function getRecentActivity(): JsonResponse
    {
        try {
            $recentComplaints = Complaints::with('user:uuid,name')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(['complaint_id', 'user_id', 'title', 'category', 'status', 'created_at']);

            $recentSessions = Appointments::with(['user:uuid,name', 'psychologist:uuid,name'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(['appointment_id', 'user_id', 'psychologist_id', 'status', 'created_at']);

            $recentUsers = User::orderBy('created_at', 'desc')
                ->limit(10)
                ->get(['uuid', 'name', 'email', 'created_at']);

            $recentReviews = Review::with(['user:uuid,name', 'psychologist:uuid,name'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(['review_id', 'user_id', 'psychologist_id', 'rating', 'created_at']);

            return ResponseHelper::success([
                'complaints' => $recentComplaints,
                'sessions' => $recentSessions,
                'users' => $recentUsers,
                'reviews' => $recentReviews
            ], 'Recent activity retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get user growth data over time
     */
    public function getUserGrowthData(): JsonResponse
    {
        try {
            $months = 12; // Last 12 months
            $userGrowthData = [];

            for ($i = $months - 1; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $startOfMonth = $date->copy()->startOfMonth();
                $endOfMonth = $date->copy()->endOfMonth();

                $totalUsers = User::where('created_at', '<=', $endOfMonth)->count();
                $newUsers = User::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

                $userGrowthData[] = [
                    'month' => $date->format('M Y'),
                    'total_users' => $totalUsers,
                    'new_users' => $newUsers
                ];
            }

            return ResponseHelper::success($userGrowthData, 'User growth data retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get session statistics data
     */
    public function getSessionStatsData(): JsonResponse
    {
        try {
            $months = 6; // Last 6 months
            $sessionStatsData = [];

            for ($i = $months - 1; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $startOfMonth = $date->copy()->startOfMonth();
                $endOfMonth = $date->copy()->endOfMonth();

                $totalSessions = Appointments::whereBetween('start_time', [$startOfMonth, $endOfMonth])->count();
                $completedSessions = Appointments::whereBetween('start_time', [$startOfMonth, $endOfMonth])
                    ->where('status', 'completed')->count();

                $sessionStatsData[] = [
                    'month' => $date->format('M Y'),
                    'total_sessions' => $totalSessions,
                    'completed_sessions' => $completedSessions,
                    'completion_rate' => $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 100, 1) : 0
                ];
            }

            return ResponseHelper::success($sessionStatsData, 'Session statistics data retrieved successfully');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get rating distribution
     */
    private function getRatingDistribution(): array
    {
        $distribution = Review::verified()
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->get();

        $result = [];
        for ($i = 5; $i >= 1; $i--) {
            $result[$i] = $distribution->where('rating', $i)->first()?->count ?? 0;
        }

        return $result;
    }

    /**
     * Get revenue statistics
     */
    private function getRevenueStats(): array
    {
        $totalRevenue = Appointments::where('status', 'completed')
            ->sum('price');

        $thisMonthRevenue = Appointments::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('price');

        $lastMonthRevenue = Appointments::where('status', 'completed')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('price');

        $revenueGrowth = $lastMonthRevenue > 0
            ? (($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100
            : 0;

        return [
            'total' => $totalRevenue,
            'this_month' => $thisMonthRevenue,
            'last_month' => $lastMonthRevenue,
            'growth_rate' => round($revenueGrowth, 1)
        ];
    }
}
