<?php

namespace App\Listeners;

use Pusher\Pusher;
use App\Events\FireEvent;
use GuzzleHttp\Psr7\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class FireListener2
{
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

    public function handle(FireEvent $event):void
    {
         $this->pusher->trigger('qb_server', 'college.updated', $event->college );

    }
}
