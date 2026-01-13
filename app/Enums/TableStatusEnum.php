<?php

namespace App\Enums;

enum TableStatusEnum: string
{
    case CLOSED='closed';
    case FREE='free';
    case OCCUPIED='occupied';
    case RESERVED='reserved';
    case RELEASING='releasing';

    public static function getLabel(self $status): string
    {
        return match ($status) {
            self::CLOSED => 'Fechada',
            self::FREE => 'Livre',
            self::OCCUPIED => 'Ocupada',
            self::RESERVED => 'Reservada',
            self::RELEASING => 'Liberando',
        };
    }

    public static function getColor(self $status): string
    {
        return match ($status) {
            self::CLOSED => 'red',
            self::FREE => 'gray',
            self::OCCUPIED => 'green',
            self::RESERVED => 'purple',
            self::RELEASING => 'teal',
        };
    }
}
