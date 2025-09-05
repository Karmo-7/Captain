<?php

namespace Modules\Invitations\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Invitations\Entities\Team_Usesrinv;

class InvitationDeclined
{
    use Dispatchable, SerializesModels;

    /**
     * The invitation instance.
     *
     * @var \Modules\Invitations\Entities\Team_Usesrinv
     */
    public Team_Usesrinv $invitation;

    /**
     * Create a new event instance.
     *
     * @param \Modules\Invitations\Entities\Team_Usesrinv $invitation
     * @return void
     */
    public function __construct(Team_Usesrinv $invitation)
    {
        // Load sender and receiver relationships for notifications
        $this->invitation = $invitation->load(['sender', 'receiver']);
    }
}
