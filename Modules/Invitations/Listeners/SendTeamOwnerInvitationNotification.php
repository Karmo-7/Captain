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

    // Determine recipients based on sender type
    if ($inv->is_team) {
        // إذا الفريق أرسل الدعوة → كل أصحاب role 'owner'
        $recipients = User::role('stadium_owner')->get();
    } else {
        // إذا المالك أرسل الدعوة → كل أصحاب role 'player'
        $recipients = User::role('player')->get();
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
        'pending'  => \Modules\Notifications\Entities\Notification::TYPE['INVITE_RECEIVED'],
        'accepted' => \Modules\Notifications\Entities\Notification::TYPE['INVITE_ACCEPTED'],
        'declined' => \Modules\Notifications\Entities\Notification::TYPE['INVITE_REJECTED'],
        default    => \Modules\Notifications\Entities\Notification::TYPE['INVITE_RECEIVED'],
    };

    // Create notification for each recipient
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
