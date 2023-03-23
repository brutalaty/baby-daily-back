<?php

namespace App\Events;

use App\Models\Invitation;
use App\Models\User;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvitationCreated
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public User $sender, public Invitation $invitation)
    {
        //
    }
}
