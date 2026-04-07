<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TradeController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();
            
            $trades = Trade::with(['sender', 'receiver'])
                ->where(function ($query) use ($user) {
                    $query->where('sender_id', $user->id)
                          ->orWhere('receiver_id', $user->id);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'trades' => $trades,
                'user' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch trades',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'receiver_id' => 'required|integer|exists:users,id',
                'send_amount' => 'required|integer|min:0',
                'receive_amount' => 'required|integer|min:0'
            ], [
                'receiver_id.exists' => 'User not found',
                'send_amount.min' => 'Send amount cannot be negative',
                'receive_amount.min' => 'Receive amount cannot be negative'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $sender = Auth::user();
            $receiver = User::findOrFail($request->receiver_id);

            if ($sender->id === $receiver->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot trade with yourself'
                ], 400);
            }

            $sendAmount = $request->send_amount;
            $receiveAmount = $request->receive_amount;

            if ($sendAmount == 0 && $receiveAmount == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Trade must include points to send or receive'
                ], 400);
            }

            if ($sendAmount > 0 && $sender->points < $sendAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient points to send'
                ], 400);
            }

            if ($receiveAmount > 0 && $receiver->points < $receiveAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Receiver does not have enough points'
                ], 400);
            }

            $existingTrade = Trade::where('sender_id', $sender->id)
                ->where('receiver_id', $receiver->id)
                ->where('status', 'pending')
                ->first();

            if ($existingTrade) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have a pending trade with this user'
                ], 400);
            }

            $trade = Trade::create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'send_amount' => $sendAmount,
                'receive_amount' => $receiveAmount,
                'status' => 'pending'
            ]);

            $trade->load(['sender', 'receiver']);

            PushNotificationService::send(
                $receiver->push_token,
                'New Trade Offer',
                $sender->name . ' wants to trade with you!'
            );

            return response()->json([
                'success' => true,
                'message' => 'Trade offer created successfully',
                'trade' => $trade
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create trade offer',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:accepted,rejected'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $trade = Trade::with(['sender', 'receiver'])->findOrFail($id);

            if ($trade->receiver_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only respond to trades sent to you'
                ], 403);
            }

            if ($trade->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'This trade has already been processed'
                ], 400);
            }

            DB::beginTransaction();

            try {
                if ($request->status === 'accepted') {
                    $sender = $trade->sender;
                    $receiver = $trade->receiver;
                    
                    if ($trade->send_amount > 0 && $sender->points < $trade->send_amount) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Sender no longer has sufficient points'
                        ], 400);
                    }
                    
                    if ($trade->receive_amount > 0 && $receiver->points < $trade->receive_amount) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'You no longer have sufficient points'
                        ], 400);
                    }

                    if ($trade->send_amount > 0) {
                        $sender->decrement('points', $trade->send_amount);
                    }
                    if ($trade->receive_amount > 0) {
                        $sender->increment('points', $trade->receive_amount);
                    }
                    
                    if ($trade->send_amount > 0) {
                        $receiver->increment('points', $trade->send_amount);
                    }
                    if ($trade->receive_amount > 0) {
                        $receiver->decrement('points', $trade->receive_amount);
                    }
                }

                $trade->update(['status' => $request->status]);

                $statusLabel = $request->status === 'accepted' ? 'accepted' : 'rejected';

                PushNotificationService::send(
                    $trade->sender->push_token,
                    'Trade ' . ucfirst($statusLabel),
                    $user->name . ' has ' . $statusLabel . ' your trade offer.'
                );

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => $request->status === 'accepted' 
                        ? 'Trade accepted successfully' 
                        : 'Trade rejected successfully',
                    'trade' => $trade
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process trade',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $trade = Trade::findOrFail($id);

            if ($trade->sender_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only cancel trades you sent'
                ], 403);
            }

            if ($trade->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only cancel pending trades'
                ], 400);
            }

            $trade->delete();

            return response()->json([
                'success' => true,
                'message' => 'Trade cancelled successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel trade',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getStats()
    {
        try {
            $user = Auth::user();

            $stats = [
                'total_sent' => Trade::where('sender_id', $user->id)->count(),
                'total_received' => Trade::where('receiver_id', $user->id)->count(),
                'pending_sent' => Trade::where('sender_id', $user->id)->pending()->count(),
                'pending_received' => Trade::where('receiver_id', $user->id)->pending()->count(),
                'accepted_sent' => Trade::where('sender_id', $user->id)->accepted()->count(),
                'accepted_received' => Trade::where('receiver_id', $user->id)->accepted()->count(),
                'points_sent' => Trade::where('sender_id', $user->id)->accepted()->sum('points_amount'),
                'points_received' => Trade::where('receiver_id', $user->id)->accepted()->sum('points_amount'),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch trade statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}