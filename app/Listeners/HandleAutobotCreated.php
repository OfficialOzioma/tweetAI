<?php

namespace App\Listeners;

use App\Events\AutobotCreated;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleAutobotCreated
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AutobotCreated $event): void
    {
        // Access the Autobot object via $event->autobot
        $numberOfAutobots = $event->count;

        // Perform actions, e.g., send notification or log to the database
        Log::info('Listener: Number of Autobots created: ' . $numberOfAutobots);
    }
}
