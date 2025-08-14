<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'top_players' => 'array'
    ];

    public function rankings()
    {
        return $this->hasMany(SeasonRanking::class);
    }

    public function topThree()
    {
        return $this->rankings()->orderBy('rank')->limit(3)->with('user');
    }

    public static function getCurrentSeason()
    {
        return self::where('is_current', true)->first();
    }

    public static function getOrCreateCurrentSeason()
    {
        $currentSeason = self::getCurrentSeason();
        
        if (!$currentSeason) {
            // Create first season
            $currentSeason = self::create([
                'season_number' => 1,
                'start_date' => now(),
                'is_current' => true
            ]);
        }
        
        return $currentSeason;
    }

    public function endSeason()
    {
        // Get top 3 players
        $topPlayers = User::orderBy('stars', 'desc')->limit(3)->get();
        
        // Store top players data
        $this->update([
            'end_date' => now(),
            'is_current' => false,
            'top_players' => $topPlayers->map(function ($user, $index) {
                return [
                    'rank' => $index + 1,
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'stars' => $user->stars,
                    'profile_image' => $user->profile_image,
                    'is_premium' => $user->is_premium
                ];
            })->toArray()
        ]);

        // Create season rankings
        foreach ($topPlayers as $index => $user) {
            SeasonRanking::create([
                'season_id' => $this->id,
                'user_id' => $user->id,
                'rank' => $index + 1,
                'final_stars' => $user->stars
            ]);
        }

        return $this;
    }

    public function startNewSeason()
    {
        // End current season if exists
        $currentSeason = self::getCurrentSeason();
        if ($currentSeason) {
            $currentSeason->endSeason();
        }

        // Reset all user stars to 100
        User::query()->update(['stars' => 100]);

        // Create new season
        $newSeasonNumber = self::max('season_number') + 1;
        return self::create([
            'season_number' => $newSeasonNumber,
            'start_date' => now(),
            'is_current' => true
        ]);
    }
}

class SeasonRanking extends Model
{
    use HasFactory;

    protected $fillable = [
        'season_id',
        'user_id', 
        'rank',
        'final_stars'
    ];

    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}