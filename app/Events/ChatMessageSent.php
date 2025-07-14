<?php
namespace App\Events;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use App\Models\Message;

class ChatMessageSent implements ShouldBroadcast
{
    use SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new PresenceChannel('chat.presence');
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            // 'sender_id' => $this->message->sender_id,
            // 'receiver_id' => $this->message->receiver_id,
            'message' => $this->message->message,
            'sender_name' => $this->message->sender->name,
            'receiver_name' => $this->message->receiver->name,
            'created_at' => $this->message->created_at->toDateTimeString(),
        ];
    }
}
