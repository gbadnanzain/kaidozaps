<?php

namespace App\Filament\Resources\TransResource\Pages;

use App\Filament\Resources\TransResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTrans extends ListRecords
{
    protected static string $resource = TransResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
