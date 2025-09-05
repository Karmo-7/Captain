<?php

namespace Modules\Invitations\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Invitations\Events\InvitationSent;
use Modules\Invitations\Events\InvitationAccepted;
use Modules\Invitations\Events\OwnerInvitationAccepted;
use Modules\Invitations\Events\OwnerInvitationDeclined;
use Modules\Invitations\Events\OwnerInvitationSent;
use Modules\Invitations\Listeners\SendInvitationNotification;
use Modules\Invitations\Listeners\SendTeamOwnerInvitationNotification;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        InvitationSent::class => [
            SendInvitationNotification::class,
        ],
        InvitationAccepted::class => [
            SendInvitationNotification::class,
        ],
         \Modules\Invitations\Events\InvitationDeclined::class => [
        \Modules\Invitations\Listeners\SendInvitationNotification::class,
    ],


        OwnerInvitationSent::class => [
            SendTeamOwnerInvitationNotification::class,
        ],
        OwnerInvitationAccepted::class => [
            SendTeamOwnerInvitationNotification::class,
        ],
       OwnerInvitationDeclined::class => [
            SendTeamOwnerInvitationNotification::class,
        ],
    ];


    public function boot()
    {
        parent::boot();
    }
}
