<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case PENDING = 'pending';
    case IN_PRODUCTION = 'in_production';
    case IN_TRANSIT = 'in_transit';
    case COMPLETED = 'completed';
    case CANCELED = 'canceled';
    case DELAYED = 'delayed';

    public static function getLabel(self $status): string
    {
        return match ($status) {
            self::PENDING => 'Pending',
            self::IN_PRODUCTION => 'In Production',
            self::IN_TRANSIT => 'In Transit',
            self::COMPLETED => 'Completed',
            self::CANCELED => 'Canceled',
            self::DELAYED => 'Delayed',
        };
    }
}
