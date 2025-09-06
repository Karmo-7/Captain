<?php

namespace Modules\Invitations\Listeners;

use App\Models\User;
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

        $leagueName = $inv->league->name ?? 'Unknown League';

        // Determine sender display name
        $sender = $inv->actualSender();
        $senderName = $inv->is_team
            ? ($sender->name ?? 'Unknown Team')
            : ($sender->email ?? 'Unknown Owner');

        /**
         * ✅ Determine recipient based on invitation type
         * - If it's a player invitation, notify only that player.
         * - If it's a team invitation, notify only the captain.
         */
        if ($inv->is_team) {
            // Team invitation → notify captain only
            $team = $inv->team ?? null;
            $recipients = $team && $team->captain ? [$team->captain] : [];
        } else {
            // Player invitation → notify only that player
            $receiver = $inv->receiver ?? null;
            $recipients = $receiver ? [$receiver] : [];
        }

        // Determine notification title
        $title = $inv->is_team
            ? match ($inv->status) {
                'pending'  => "Team Sent You an Invitation ($leagueName)",
                'accepted' => "Team Invitation Accepted ($leagueName)",
                'declined' => "Team Invitation Declined ($leagueName)",
                default    => "Team Invitation Update ($leagueName)",
            }
            : match ($inv->status) {
                'pending'  => "Owner Sent You an Invitation ($leagueName)",
                'accepted' => "Owner Invitation Accepted ($leagueName)",
                'declined' => "Owner Invitation Declined ($leagueName)",
                default    => "Owner Invitation Update ($leagueName)",
            };

        // Determine notification type
        $type = match ($inv->status) {
            'pending'  => Notification::TYPE['INVITE_RECEIVED'],
            'accepted' => Notification::TYPE['INVITE_ACCEPTED'],
            'declined' => Notification::TYPE['INVITE_REJECTED'],
            default    => Notification::TYPE['INVITE_RECEIVED'],
        };

        // ✅ Send notification only to selected recipients
        foreach ($recipients as $receiver) {
            Notification::create([
                'title'           => $title,
                'description'     => "From {$senderName} in {$leagueName}",
                'user_id'         => $receiver->id,
                'type'            => $type,
                'notifiable_type' => get_class($inv),
                'notifiable_id'   => $inv->id,
            ]);
        }
    }
}
