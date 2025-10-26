<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    // ✅ Send message
    public function sendMessage(Request $request)
    {
        $user = JWTAuth::user();

        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $message = Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully.',
            'data' => $message
        ]);
    }

    // ✅ Get conversation between two users
    public function getMessages($receiver_id)
    {
        $user = JWTAuth::user();

        $messages = Message::where(function ($query) use ($user, $receiver_id) {
                $query->where('sender_id', $user->id)
                      ->where('receiver_id', $receiver_id);
            })
            ->orWhere(function ($query) use ($user, $receiver_id) {
                $query->where('sender_id', $receiver_id)
                      ->where('receiver_id', $user->id);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }
}
