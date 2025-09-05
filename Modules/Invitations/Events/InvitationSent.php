<?php

namespace Modules\Invitations\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Invitations\Entities\Team_Usesrinv;

class InvitationSent
{
    use Dispatchable, SerializesModels;

    public Team_Usesrinv $invitation;

    public function __construct(Team_Usesrinv $invitation)
    {
        // نحمّل العلاقات المطلوبة قبل إرسال الحدث
        $this->invitation = $invitation->load(['sender', 'receiver']);
    }
}
