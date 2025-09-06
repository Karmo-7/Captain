<?php

namespace Modules\Invitations\Listeners;

use Illuminate\Support\Facades\Log;
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

        // تحديد اسم المرسل
        $sender = $inv->actualSender();
        $senderName = $inv->is_team
            ? ($sender->name ?? 'Unknown Team')
            : ($sender->email ?? 'Unknown Owner');

        /**
         * ✅ تحديد المستلمين:
         * - إذا كانت دعوة فريق → إشعار للكابتن فقط
         * - إذا كانت دعوة لاعب → إشعار للمالك مباشرة
         */
        $recipients = [];

        if ($inv->is_team) {
             $receiver = $inv->owner ?? null;
            $recipients = $receiver ? [$receiver] : [];


        } else {
        $team = $inv->team ?? null;

            if ($team && $team->captain) {
                $recipients = [$team->captain];  // ✅ نرسل فقط للكابتن
            }
        }

        // إذا لم نجد أي مستلم → لا نرسل إشعار
        if (empty($recipients)) {
            Log::warning('No recipients found for owner invitation', [
                'invitation_id' => $inv->id,
                'team_id' => $inv->team_id
            ]);
            return;
        }

        // تحديد عنوان الإشعار
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

        // تحديد نوع الإشعار
        $type = match ($inv->status) {
            'pending'  => Notification::TYPE['INVITE_RECEIVED'],
            'accepted' => Notification::TYPE['INVITE_ACCEPTED'],
            'declined' => Notification::TYPE['INVITE_REJECTED'],
            default    => Notification::TYPE['INVITE_RECEIVED'],
        };

        // ✅ إنشاء الإشعار
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
