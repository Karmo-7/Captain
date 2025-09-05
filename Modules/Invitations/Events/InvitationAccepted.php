<?php

namespace Modules\Invitations\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Invitations\Entities\Team_Usesrinv;

class InvitationAccepted
{
    use Dispatchable, SerializesModels;

    public Team_Usesrinv $invitation;

    public function __construct(Team_Usesrinv $invitation)
    {
        $this->invitation = $invitation->load(['sender', 'receiver']);

        
    }
}
