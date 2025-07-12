<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use App\Events\ChatMessageSent;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index($userId)
    {
        // dd($userId);
        $otherUser =User::findOrFail($userId);

        $messages = Message::where(function ($query) use ($userId) {
            $query->where('sender_id', Auth::id())
                  ->where('receiver_id', $userId);
        })->orWhere(function ($query) use ($userId) {
            $query->where('sender_id', $userId)
                  ->where('receiver_id', Auth::id());
        })->with(['sender', 'receiver'])->get();

        $users =User::where('id', '!=', Auth::id())->get();

        return view('chat', compact('users', 'messages', 'otherUser'));
    }

   public function send(Request $request)
{
    // dd($request->all());
    $request->validate([
        'message' => 'required|string',
        'receiver_id' => 'required|exists:users,id'
    ]);

    $message = Message::create([
        'sender_id' => Auth::id(),
        'receiver_id' => $request->receiver_id,
        'message' => $request->message,
    ]);

    // Broadcast the message to others (real-time)
    broadcast(new ChatMessageSent($message->load('sender')))->toOthers();

    return response()->json(['status' => 'Message Sent!']);
}
}
