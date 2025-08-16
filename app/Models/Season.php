<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Season extends Model
{
    use HasFactory;

    protected $fillable = [
        'season_number',
        'start_date',
        'end_date',
        'is_current',
        'top_players'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime', 
        'is_current' => 'boolean',
        'top_players' => 'array'
    ];

    /**
     * Get or create the current season - STATIC METHOD
     */
    public static function getOrCreateCurrentSeason()
    {
        $currentSeason = self::where('is_current', true)->first();
        
        if (!$currentSeason) {
            // Create the first season
            $currentSeason = self::create([
                'season_number' => 1,
                'start_date' => Carbon::now(),
                'is_current' => true,
                'top_players' => []
            ]);
        }
        
        return $currentSeason;
    }

    /**
     * Start a new season - STATIC METHOD
     */
    public static function startNewSeason()
    {
        \DB::beginTransaction();
        
        try {
            // Get current season
            $currentSeason = self::where('is_current', true)->first();
            
            if ($currentSeason) {
                // Get top 3 players before resetting
                $topPlayers = \App\Models\User::orderBy('stars', 'desc')
                    ->orderBy('level', 'desc')
                    ->orderBy('created_at', 'asc')
                    ->take(3)
                    ->get()
                    ->map(function ($user, $index) {
                        return [
                            'user_id' => $user->id,
                            'name' => $user->name,
                            'stars' => $user->stars,
                            'level' => $user->level,
                            'rank' => $index + 1,
                            'is_premium' => $user->is_premium ?? false
                        ];
                    })->toArray();

                // End current season
                $currentSeason->update([
                    'end_date' => Carbon::now(),
                    'is_current' => false,
                    'top_players' => $topPlayers
                ]);

                // Reset all user stars to 100
                \App\Models\User::query()->update([
                    'stars' => 100,
                    'points' => 100 // Also reset points for compatibility
                ]);
            }

            // Create new season
            $newSeasonNumber = $currentSeason ? $currentSeason->season_number + 1 : 1;
            
            $newSeason = self::create([
                'season_number' => $newSeasonNumber,
                'start_date' => Carbon::now(),
                'is_current' => true,
                'top_players' => []
            ]);

            \DB::commit();
            
            return $newSeason;
            
        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Failed to start new season: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get all past seasons
     */
    public static function getPastSeasons()
    {
        return self::where('is_current', false)
            ->orderBy('season_number', 'desc')
            ->get();
    }

    /**
     * Get season statistics
     */
    public function getStats()
    {
        if (!$this->top_players) {
            return [
                'total_players' => 0,
                'winner' => null,
                'average_stars' => 0
            ];
        }

        return [
            'total_players' => count($this->top_players),
            'winner' => $this->top_players[0] ?? null,
            'average_stars' => collect($this->top_players)->avg('stars')
        ];
    }

    /**
     * Check if this season has ended
     */
    public function hasEnded()
    {
        return !$this->is_current && $this->end_date !== null;
    }

    /**
     * Get duration of the season
     */
    public function getDuration()
    {
        if (!$this->end_date) {
            return $this->start_date->diffForHumans();
        }
        
        return $this->start_date->diffInDays($this->end_date) . ' days';
    }
}