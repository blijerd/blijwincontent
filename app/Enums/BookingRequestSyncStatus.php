<?php

namespace App\Enums;

enum BookingRequestSyncStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';
}
