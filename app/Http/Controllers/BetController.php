<?php

namespace App\Http\Controllers;

use App\Models\Bet;
use App\Models\User;
use App\Services\PushNotificationService;
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
            'stars_amount' => 'required|integer|min:1',
        ], [
            'opponent_id.required' => 'Please select an opponent.',
            'opponent_id.different' => 'Opponent and referee must be different users.',
            'referee_id.required' => 'Please select a referee.',
            'referee_id.different' => 'Referee and opponent must be different users.',
            'stars_amount.required' => 'Stars amount is required.',
            'stars_amount.min' => 'Stars amount must be at least 1.',
        ]);

        $user = Auth::user();

        if ($user->stars < $request->stars_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stars. You only have ' . $user->stars . ' stars.'
            ], 400);
        }

        if ($request->opponent_id == $user->id || $request->referee_id == $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot bet against yourself or be your own referee.'
            ], 400);
        }

        $opponent = User::find($request->opponent_id);
        if ($opponent->stars < $request->stars_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Opponent does not have enough stars for this bet.'
            ], 400);
        }

        try {
            $bet = Bet::create([
                'creator_id' => $user->id,
                'opponent_id' => $request->opponent_id,
                'referee_id' => $request->referee_id,
                'stars_amount' => $request->stars_amount,
                'status' => 'pending'
            ]);

            $bet->load(['opponent', 'referee']);

            PushNotificationService::send(
                $bet->opponent->push_token,
                'New Bet Challenge',
                $user->name . ' has challenged you to a bet!'
            );

            PushNotificationService::send(
                $bet->referee->push_token,
                'Referee Request',
                $user->name . ' has assigned you as referee for a bet.'
            );

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

        if ($user->stars < $bet->stars_amount) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have enough stars to accept this bet.'
            ], 400);
        }

        try {
            $bet->load('creator');

            $bet->update([
                'status' => 'accepted',
                'accepted_at' => now()
            ]);

            PushNotificationService::send(
                $bet->creator->push_token,
                'Bet Accepted',
                $user->name . ' has accepted your bet!'
            );

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
            $bet->load('creator');

            $bet->update(['status' => 'rejected']);

            PushNotificationService::send(
                $bet->creator->push_token,
                'Bet Rejected',
                $user->name . ' has rejected your bet.'
            );

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

        if ($request->winner_id !== $bet->creator_id && $request->winner_id !== $bet->opponent_id) {
            return response()->json([
                'success' => false,
                'message' => 'Winner must be either the creator or opponent of the bet.'
            ], 400);
        }

        try {
            DB::transaction(function () use ($bet, $request) {
                $bet->update([
                    'status' => 'completed',
                    'winner_id' => $request->winner_id,
                    'completed_at' => now()
                ]);

                $winner = User::find($request->winner_id);
                $loser = $request->winner_id === $bet->creator_id
                    ? User::find($bet->opponent_id)
                    : User::find($bet->creator_id);

                $winner->increment('stars', $bet->stars_amount);
                $loser->decrement('stars', $bet->stars_amount);

                PushNotificationService::send(
                    $winner->push_token,
                    'You Won the Bet! 🏆',
                    'Congratulations! You won ' . $bet->stars_amount . ' stars.'
                );

                PushNotificationService::send(
                    $loser->push_token,
                    'Bet Result',
                    'You lost ' . $bet->stars_amount . ' stars. Better luck next time!'
                );
            });

            return response()->json([
                'success' => true,
                'message' => 'Winner declared successfully and stars transferred'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to declare winner'
            ], 500);
        }
    }
}