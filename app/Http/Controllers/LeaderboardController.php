<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Season;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    public function index()
    {
        try {
            // Get current season users ordered by stars
            $users = User::orderBy('stars', 'desc')
                ->orderBy('level', 'desc')
                ->orderBy('name', 'asc')
                ->get();

            // Get current season info
            $currentSeason = Season::getOrCreateCurrentSeason();
            
            // Get all seasons with their top players
            $seasons = Season::where('is_current', false)
                ->orderBy('season_number', 'desc')
                ->get();

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
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch leaderboard'
            ], 500);
        }
    }

    public function newSeason(Request $request)
    {
        try {
            // Only allow admins or specific users to start new season
            // You can add authorization logic here
            
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
            return response()->json([
                'success' => false,
                'message' => 'Failed to start new season'
            ], 500);
        }
    }

    public function getCurrentSeason()
    {
        try {
            $currentSeason = Season::getOrCreateCurrentSeason();
            
            return response()->json([
                'success' => true,
                'current_season' => [
                    'season_number' => $currentSeason->season_number,
                    'start_date' => $currentSeason->start_date,
                    'is_current' => $currentSeason->is_current
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch current season'
            ], 500);
        }
    }
}