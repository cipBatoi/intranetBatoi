<?php

namespace Intranet\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Jenssegers\Date\Date;

class UpdateLastLoggedAt
{

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Login  $event
     * @return void
     */
    public function handle(Logout $event)
    {
        $event->user->last_logged = Date::now()->toDateTimeString();
        $event->user->save(['timestamps' => false]);
    }

}
