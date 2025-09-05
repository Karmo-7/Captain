<?php

namespace Modules\Invitations\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Invitations\Entities\Team_Ownerinv;

class OwnerInvitationAccepted
{
    use Dispatchable, SerializesModels;

    public Team_Ownerinv $invitation;

    public function __construct(Team_Ownerinv $invitation)
    {
        // نحمل العلاقات المطلوبة قبل إرسال الحدث
        $this->invitation = $invitation->load(['team', 'owner', 'league']);
    }
}
