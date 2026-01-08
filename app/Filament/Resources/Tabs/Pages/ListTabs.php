<?php

namespace App\Filament\Resources\Tabs\Pages;

use App\Filament\Resources\Tabs\TabResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTabs extends ListRecords
{
    protected static string $resource = TabResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
