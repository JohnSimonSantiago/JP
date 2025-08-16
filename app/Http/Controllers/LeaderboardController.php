<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaderboardController extends Controller
{
    public function index()
    {
        try {
            // Get current season users ordered by stars
            $users = User::orderBy('stars', 'desc')
                ->orderBy('level', 'desc')
                ->orderBy('name', 'asc')
                ->select(['id', 'name', 'level', 'stars', 'is_premium', 'profile_image']) // FIXED: Use stars
                ->get();

            // Get current season info
            $currentSeason = Season::getOrCreateCurrentSeason();
                        
            // Get all seasons with their top players
            $seasons = Season::getPastSeasons(); // FIXED: Use static method

            return response()->json([
                'success' => true,
                'users' => $users,
                'current_season' => [
                    'season_number' => $currentSeason->season_number,
                    'start_date' => $currentSeason->start_date,
                    'is_current' => $currentSeason->is_current
                ],
                'past_seasons' => $seasons->map(function ($season) {
                    return [
                        'season_number' => $season->season_number,
                        'start_date' => $season->start_date,
                        'end_date' => $season->end_date,
                        'top_players' => $season->top_players
                    ];
                })
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to fetch leaderboard: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch leaderboard'
            ], 500);
        }
    }

    public function newSeason(Request $request)
    {
        try {
            // Check if user is authenticated
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            // Check if user has admin role
            $user = Auth::user();
            if ($user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only administrators can start new seasons'
                ], 403);
            }

            // Start new season
            $newSeason = Season::startNewSeason();

            return response()->json([
                'success' => true,
                'message' => "Season {$newSeason->season_number} has started! All stars have been reset.",
                'new_season' => [
                    'season_number' => $newSeason->season_number,
                    'start_date' => $newSeason->start_date,
                    'is_current' => $newSeason->is_current
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to start new season: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to start new season'
            ], 500);
        }
    }

    public function getCurrentSeason()
    {
        try {
            $currentSeason = Season::getOrCreateCurrentSeason(); // FIXED: Use static method
                        
            return response()->json([
                'success' => true,
                'current_season' => [
                    'season_number' => $currentSeason->season_number,
                    'start_date' => $currentSeason->start_date,
                    'is_current' => $currentSeason->is_current
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to fetch current season: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch current season'
            ], 500);
        }
    }

    /**
     * Get admin dashboard statistics (admin only)
     */
    public function getAdminStats(Request $request)
    {
        try {
            // Check if user is authenticated
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            // Check if user has admin role
            $user = Auth::user();
            if ($user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin access required'
                ], 403);
            }

            $currentSeason = Season::getOrCreateCurrentSeason();
            
            // Get various statistics
            $totalUsers = User::count();
            $premiumUsers = User::where('is_premium', true)->count();
            $totalStars = User::sum('stars');
            $averageStars = User::avg('stars');
            $topUser = User::orderBy('stars', 'desc')->first();

            return response()->json([
                'success' => true,
                'stats' => [
                    'current_season' => $currentSeason->season_number,
                    'total_users' => $totalUsers,
                    'premium_users' => $premiumUsers,
                    'total_stars' => $totalStars,
                    'average_stars' => round($averageStars, 2),
                    'top_user' => $topUser ? [
                        'name' => $topUser->name,
                        'stars' => $topUser->stars,
                        'level' => $topUser->level
                    ] : null
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch admin statistics'
            ], 500);
        }
    }
}