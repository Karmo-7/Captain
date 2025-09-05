<?php

namespace Modules\Invitations\Listeners;

use Modules\Invitations\Events\InvitationSent;
use Modules\Invitations\Events\InvitationAccepted;
use Modules\Notifications\Services\NotificationService;
use Modules\Notifications\Entities\Notification;

class SendInvitationNotification
{
    public function handle($event)
    {
        $inv = $event->invitation;

        /**
         * 📌 عند إرسال دعوة جديدة
         */
        


    }
}
