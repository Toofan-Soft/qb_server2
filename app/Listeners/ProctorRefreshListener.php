<?php

namespace App\Listeners;

use Pusher\Pusher;
use App\Events\ProctorRefreshEvevnt;

class ProctorRefreshListener
{
    /**
     * Create the event listener.
     */
    protected $pusher;

    public function __construct()
    {
        $this->pusher = new Pusher(
            config('broadcasting.connections.pusher.key'),
            config('broadcasting.connections.pusher.secret'),
            config('broadcasting.connections.pusher.app_id'),
               ['cluster' => 'ap2','useTLS' => true]
           );
    }

    /**
     * Handle the event.
     */
    public function handle(ProctorRefreshEvevnt $event): void
    {
        $this->pusher->trigger('qb_server', 'proctor.refresh.' . $event->uid, $event->data);
        
        // $uid = auth()->user()->id;
        // $this->pusher->trigger('qb_server', 'student.refresh.' . $uid, $event->data);

        // $this->pusher->trigger('qb_server', 'student.refreshed.' . $token, $event->data);
        // $this->pusher->trigger('qb_server', 'student.refreshed', $event->data);
    }
}
