<?php

namespace App\Enums;

enum CheckStatusEnum: string
{
    case OPEN = 'Open';
    case CLOSED = 'Closed';
    case PAID = 'Paid';
    case CANCELED = 'Canceled';
    case MERGED = 'merged';

    public static function getLabel(self $status): string
    {
        return match ($status) {
            self::OPEN => 'Aberto',
            self::CLOSED => 'Fechado',
            self::PAID => 'Pago',
            self::CANCELED => 'Cancelado',
            self::MERGED => 'Unida',
        };
    }

    public static function getColor(self $status): string
    {
        return match ($status) {
            self::OPEN => 'green',
            self::CLOSED => 'red',
            self::PAID => 'gray',
            self::CANCELED => 'orange',
            self::MERGED => 'purple',
        };
    }
}
