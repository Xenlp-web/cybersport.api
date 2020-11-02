<?php

namespace App\Listeners;

use App\Events\messageToGlobalChatSent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class updateGlobalChatMessages
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
     * @param  messageToGlobalChatSent  $event
     * @return void
     */
    public function handle(messageToGlobalChatSent $event)
    {
        //
    }
}
