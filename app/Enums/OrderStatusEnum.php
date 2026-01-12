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
            self::PENDING => 'Aguardando',
            self::IN_PRODUCTION => 'Em Produção',
            self::IN_TRANSIT => 'Em Trânsito',
            self::COMPLETED => 'Entregue',
            self::CANCELED => 'Cancelado',
            self::DELAYED => 'Atrasado',
        };
    }

    public static function colorsButton(self $status): array|string
    {
        $map = [
            self::PENDING->value =>  'bg-yellow-500 hover:bg-yellow-600 text-white',
            self::IN_PRODUCTION->value => 'bg-blue-500 hover:bg-blue-600 text-white',
            self::IN_TRANSIT->value => 'bg-purple-500 hover:bg-purple-600 text-white',
            self::COMPLETED->value => 'bg-green-500 hover:bg-green-600 text-white',
            self::CANCELED->value => 'bg-red-500 hover:bg-red-600 text-white',
            self::DELAYED->value => 'bg-yellow-500 hover:bg-yellow-600 text-white',
        ];

        if ($status instanceof self) {
            return $map[$status->value] ?? '';
        }

        return $map;
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
