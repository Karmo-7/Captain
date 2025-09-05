<?php

namespace Modules\Invitations\Listeners;

use Modules\Notifications\Entities\Notification;

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

        // Determine title and type based on status
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
                $title = 'Invitation declined';
                $type = Notification::TYPE['INVITE_REJECTED'];
                break;
            default:
                $title = 'Invitation Update';
                $type = Notification::TYPE['INVITE_RECEIVED'];
                break;
        }

        Notification::create([
            'title' => $title,
            'description' => "From {$senderName} to {$receiverName}",
            'user_id' => $inv->receiver_id,
            'notifiable_type' => $inv::class,
            'notifiable_id' => $inv->id,
            'type' => $type,
        ]);
    }
}
