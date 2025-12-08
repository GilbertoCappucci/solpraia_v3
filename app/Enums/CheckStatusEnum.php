<?php

namespace App\Enums;

enum CheckStatusEnum: string
{
    case OPEN = 'Open';
    case CLOSED = 'Closed';
    case PAID = 'Paid';
    case CANCELED = 'Canceled';
}
