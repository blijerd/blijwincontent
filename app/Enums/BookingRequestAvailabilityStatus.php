<?php

namespace App\Enums;

enum BookingRequestAvailabilityStatus: string
{
    case Unknown = 'unknown';
    case Available = 'available';
    case Unavailable = 'unavailable';
    case AlternativeRequested = 'alternative_requested';
}
