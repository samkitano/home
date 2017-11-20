<?php

namespace App\Listeners;

use App\Events\ConsoleMessageEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ConsoleMessage
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
     * @param  ConsoleOutput  $event
     * @return void
     */
    public function handle(ConsoleMessageEvent $event)
    {
        //
    }
}
