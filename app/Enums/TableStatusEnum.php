<?php

namespace App\Enums;

enum TableStatusEnum: string
{
    case CLOSE='close';
    case FREE='free';
    case OCCUPIED='occupied';
    case RESERVED='reserved';
}
