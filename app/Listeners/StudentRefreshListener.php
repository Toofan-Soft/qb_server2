<?php

namespace App\Listeners;

use Pusher\Pusher;
use App\Events\StudentRefreshEvevnt;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class StudentRefreshListener
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
    public function handle(StudentRefreshEvevnt $event): void
    {
        $this->pusher->trigger('qb_server', 'student.refreshed', $event->studentInfo );
    }
}
