<?php

namespace App\Http\Controllers;

use App\Models\Bet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BetController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $myBets = Bet::where(function ($query) use ($userId) {
                $query->where('creator_id', $userId)
                      ->orWhere('opponent_id', $userId);
            })
            ->with(['creator', 'opponent', 'referee', 'winner'])
            ->orderBy('created_at', 'desc')
            ->get();

        $incomingBets = Bet::incomingBets($userId)
            ->with(['creator', 'referee'])
            ->orderBy('created_at', 'desc')
            ->get();

        $refereeBets = Bet::refereeBets($userId)
            ->with(['creator', 'opponent'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Add ongoing bets (accepted bets where user is creator or opponent)
        $ongoingBets = Bet::where('status', 'accepted')
            ->where(function ($query) use ($userId) {
                $query->where('creator_id', $userId)
                      ->orWhere('opponent_id', $userId);
            })
            ->with(['creator', 'opponent', 'referee'])
            ->orderBy('accepted_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'my_bets' => $myBets,
            'incoming_bets' => $incomingBets,
            'referee_bets' => $refereeBets,
            'ongoing_bets' => $ongoingBets
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'opponent_id' => 'required|exists:users,id|different:referee_id',
            'referee_id' => 'required|exists:users,id|different:opponent_id',
            'points_amount' => 'required|integer|min:1',
        ], [
            'opponent_id.required' => 'Please select an opponent.',
            'opponent_id.different' => 'Opponent and referee must be different users.',
            'referee_id.required' => 'Please select a referee.',
            'referee_id.different' => 'Referee and opponent must be different users.',
            'points_amount.required' => 'Points amount is required.',
            'points_amount.min' => 'Points amount must be at least 1.',
        ]);

        $user = Auth::user();

        // Check if user has enough points
        if ($user->points < $request->points_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient points. You only have ' . $user->points . ' points.'
            ], 400);
        }

        // Check if users are not the same as creator
        if ($request->opponent_id == $user->id || $request->referee_id == $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot bet against yourself or be your own referee.'
            ], 400);
        }

        // Check if opponent has enough points
        $opponent = User::find($request->opponent_id);
        if ($opponent->points < $request->points_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Opponent does not have enough points for this bet.'
            ], 400);
        }

        try {
            $bet = Bet::create([
                'creator_id' => $user->id,
                'opponent_id' => $request->opponent_id,
                'referee_id' => $request->referee_id,
                'points_amount' => $request->points_amount,
                'status' => 'pending'
            ]);

            $bet->load(['opponent', 'referee']);

            return response()->json([
                'success' => true,
                'message' => 'Bet created successfully',
                'bet' => $bet
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create bet',
                'error' => 'Something went wrong. Please try again.'
            ], 500);
        }
    }

    public function accept(Bet $bet)
    {
        $user = Auth::user();

        if ($bet->opponent_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to accept this bet.'
            ], 403);
        }

        if ($bet->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This bet is no longer pending.'
            ], 400);
        }

        // Check if opponent still has enough points
        if ($user->points < $bet->points_amount) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have enough points to accept this bet.'
            ], 400);
        }

        try {
            $bet->update([
                'status' => 'accepted',
                'accepted_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bet accepted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept bet'
            ], 500);
        }
    }

    public function reject(Bet $bet)
    {
        $user = Auth::user();

        if ($bet->opponent_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to reject this bet.'
            ], 403);
        }

        if ($bet->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This bet is no longer pending.'
            ], 400);
        }

        try {
            $bet->update(['status' => 'rejected']);

            return response()->json([
                'success' => true,
                'message' => 'Bet rejected successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject bet'
            ], 500);
        }
    }

    public function cancel(Bet $bet)
    {
        $user = Auth::user();

        if ($bet->creator_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to cancel this bet.'
            ], 403);
        }

        if ($bet->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending bets can be cancelled.'
            ], 400);
        }

        try {
            $bet->update(['status' => 'cancelled']);

            return response()->json([
                'success' => true,
                'message' => 'Bet cancelled successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel bet'
            ], 500);
        }
    }

    public function declareWinner(Request $request, Bet $bet)
    {
        $request->validate([
            'winner_id' => 'required|exists:users,id',
        ]);

        $user = Auth::user();

        if ($bet->referee_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to declare a winner for this bet.'
            ], 403);
        }

        if ($bet->status !== 'accepted') {
            return response()->json([
                'success' => false,
                'message' => 'This bet is not ready for winner declaration.'
            ], 400);
        }

        // Validate that winner is either creator or opponent
        if ($request->winner_id !== $bet->creator_id && $request->winner_id !== $bet->opponent_id) {
            return response()->json([
                'success' => false,
                'message' => 'Winner must be either the creator or opponent of the bet.'
            ], 400);
        }

        try {
            DB::transaction(function () use ($bet, $request) {
                // Update bet status
                $bet->update([
                    'status' => 'completed',
                    'winner_id' => $request->winner_id,
                    'completed_at' => now()
                ]);

                // Transfer points
                $winner = User::find($request->winner_id);
                $loser = $request->winner_id === $bet->creator_id 
                    ? User::find($bet->opponent_id) 
                    : User::find($bet->creator_id);

                // Winner gets the bet amount, loser loses the bet amount
                $winner->increment('points', $bet->points_amount);
                $loser->decrement('points', $bet->points_amount);
            });

            return response()->json([
                'success' => true,
                'message' => 'Winner declared successfully and points transferred'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to declare winner'
            ], 500);
        }
    }
}