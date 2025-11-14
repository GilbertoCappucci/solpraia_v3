<?php

namespace App\Enums;

enum CheckStatusEnum: string
{
    case OPEN = 'Open';
    case CLOSING = 'Closing';
    case CLOSED = 'Closed';
    case PAID = 'Paid';
}
