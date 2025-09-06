<?php

namespace Modules\Invitations\Listeners;

use Modules\Notifications\Entities\Notification;

class SendTeamOwnerInvitationNotification
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $inv = $event->invitation;

        // Get sender and receiver models
        $sender = $inv->actualSender();
        $receiver = $inv->actualReceiver();

        // Get league name
        $leagueName = $inv->league->name ?? 'Unknown League';

        // Determine sender display name: team uses name, owner uses email
        $senderName = null;
        if ($inv->is_team && $sender) {
            $senderName = $sender->name ?? 'Unknown Team';
        } elseif ($sender) {
            $senderName = $sender->email ?? 'Unknown Owner';
        } else {
            $senderName = 'Unknown';
        }

        // Determine receiver display name: same logic
        $receiverName = null;
        if ($inv->is_team && $receiver) {
            $receiverName = $receiver->email ?? 'Unknown Owner';
        } elseif ($receiver) {
            $receiverName = $receiver->name ?? 'Unknown Team';
        } else {
            $receiverName = 'Unknown';
        }

        // Determine notification title based on sender type and status
        if ($inv->is_team) {
            $title = match ($inv->status) {
                'pending'  => 'Team Sent You an Invitation (' . $leagueName . ')',
                'accepted' => 'Team Invitation Accepted (' . $leagueName . ')',
                'declined' => 'Team Invitation Declined (' . $leagueName . ')',
                default    => 'Team Invitation Update (' . $leagueName . ')',
            };
        } else {
            $title = match ($inv->status) {
                'pending'  => 'Owner Sent You an Invitation (' . $leagueName . ')',
                'accepted' => 'Owner Invitation Accepted (' . $leagueName . ')',
                'declined' => 'Owner Invitation Declined (' . $leagueName . ')',
                default    => 'Owner Invitation Update (' . $leagueName . ')',
            };
        }

        // Determine notification type
        $type = match ($inv->status) {
            'pending'  => \Modules\Notifications\Entities\Notification::TYPE['INVITE_RECEIVED'],
            'accepted' => \Modules\Notifications\Entities\Notification::TYPE['INVITE_ACCEPTED'],
            'declined' => \Modules\Notifications\Entities\Notification::TYPE['INVITE_REJECTED'],
            default    => \Modules\Notifications\Entities\Notification::TYPE['INVITE_RECEIVED'],
        };

        // Create notification
        return Notification::create([
            'title'           => $title,
            'description'     => "From {$senderName} to {$receiverName} in {$leagueName}",
            'user_id'         => $receiver->id,
            'type'            => $type,
            'notifiable_type' => get_class($inv),
            'notifiable_id'   => $inv->id,
        ]);
    }
}
