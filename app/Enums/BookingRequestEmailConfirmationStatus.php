<?php

namespace App\Enums;

enum BookingRequestEmailConfirmationStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case NotRequired = 'not_required';
}
