<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\ChatMessageSent;

class ChatController extends Controller
{
    public function chatPage()
    {
        $users = User::where('id', '!=', Auth::id())->get();
        return view('chat', compact('users'));
    }

    public function getMessages($userId)
    {
        $messages = Message::where(function ($query) use ($userId) {
            $query->where('sender_id', Auth::id())
                  ->where('receiver_id', $userId);
        })->orWhere(function ($query) use ($userId) {
            $query->where('sender_id', $userId)
                  ->where('receiver_id', Auth::id());
        })->with(['sender'])->get();

        $otherUser = User::find($userId);

        return response()->json([
            'messages' => $messages,
            'user' => $otherUser,
        ]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string'
        ]);

        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
        ]);

        broadcast(new ChatMessageSent($message->load('sender')))->toOthers();

        return response()->json(['status' => 'sent']);
    }
    
}
