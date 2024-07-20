<?php

namespace App\Events;

use App\Models\College;
use GuzzleHttp\Psr7\Request;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FireEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $college;

    /**
     * Create a new event instance.
     */
    // public function __construct(College $college)
    public function __construct(string $college)
    {
        $this->college = $college;
    }
    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {

        return [
            new PrivateChannel('qb_server'),
        ];
    }

    public function broadcastWith()
    {
        return [
            'data' => json_decode($this->college)
        ];
    }
}
