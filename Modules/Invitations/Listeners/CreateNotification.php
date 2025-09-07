<?php

namespace Modules\Invitations\Listeners;

use Modules\Notifications\Entities\Notification;
use Modules\Team\Entities\Team;

class CreateNotification
{
    public function handle($event)
    {
        $inv = $event->invitation;

        // Safely get sender and receiver models
        $sender = $inv->sender()->first();
        $receiver = $inv->receiver()->first();

        // Get sender and receiver names based on type
        $senderName = $sender
            ? ($inv->is_team ? ($sender->name ?? 'Unknown Team') : ($sender->email ?? 'Unknown User'))
            : 'Unknown';

        $receiverName = $receiver
            ? ($inv->is_team ? ($receiver->email ?? 'Unknown User') : ($receiver->name ?? 'Unknown Team'))
            : 'Unknown';

        // Determine notification title & type based on status
        switch ($inv->status) {
            case 'pending':
                $title = 'New Invitation';
                $type = Notification::TYPE['INVITE_RECEIVED'];
                break;
            case 'accepted':
                $title = 'Invitation Accepted';
                $type = Notification::TYPE['INVITE_ACCEPTED'];
                break;
            case 'declined':
                $title = 'Invitation Declined';
                $type = Notification::TYPE['INVITE_REJECTED'];
                break;
            default:
                $title = 'Invitation Update';
                $type = Notification::TYPE['INVITE_RECEIVED'];
                break;
        }

        /**
         * ✅ Determine who should receive the notification
         * If the invitation is sent by a team → receiver is owner
         * If the invitation is sent by an owner → receiver is a team → send to team's captain
         */
        if ($inv->is_team) {
            // Team sent the invitation → receiver is the owner
            $userId= $inv->receiver_id;

        } else {
            // Owner sent the invitation → receiver is the team captain
            $team = Team::find($inv->receiver_id);
            $userId = $team ? $team->captin_id : null;
        }

        if (!$userId) {
            dd('ggggggg');
            return; // Safety check: no valid user to send notification
        }

        // ✅ Create notification
        Notification::create([
            'title' => $title,
            'description' => "From {$senderName} to {$receiverName}",
            'user_id' => $userId,
            'notifiable_type' => $inv::class,
            'notifiable_id' => $inv->id,
            'type' => $type,
        ]);
    }
}
