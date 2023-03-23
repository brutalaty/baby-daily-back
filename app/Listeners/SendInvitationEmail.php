<?php

namespace App\Listeners;

use App\Events\InvitationCreated;
use App\Mail\FamilyInvitation;
// use Illuminate\Contracts\Queue\ShouldQueue;
// use Illuminate\Queue\InteractsWithQueue;

use Illuminate\Support\Facades\Mail;

class SendInvitationEmail
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
    public function handle(InvitationCreated $event): void
    {
        Mail::send(new FamilyInvitation($event->sender, $event->invitation));
    }
}
