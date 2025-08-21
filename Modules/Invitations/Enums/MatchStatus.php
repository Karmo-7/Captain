<?php

namespace Modules\Invitations\Enums;

enum MatchStatus: string
{
    case PENDING = 'pending';
    case REJECTED = 'rejected';
    case APPROVED = 'approved';
    case PLAYED = 'played';
}
