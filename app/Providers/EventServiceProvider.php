<?php

namespace App\Providers;

use App\Events\FireEvent;
use App\Listeners\FireListener;
use App\Listeners\FireListener2;
use App\Events\ProctorRefreshEvevnt;
use App\Events\StudentRefreshEvevnt;
use App\Events\StudentRrefreshEvevnt;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use App\Listeners\ProctorRefreshListener;
use App\Listeners\StudentRefreshListener;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        FireEvent::class => [
            FireListener2::class,
        ],
        StudentRefreshEvevnt::class => [
            StudentRefreshListener::class,
        ],
        ProctorRefreshEvevnt::class => [
            ProctorRefreshListener::class,
        ],

    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
