<?php

namespace App\Filament\Resources\TransResource\Pages;

use App\Filament\Resources\TransResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTrans extends EditRecord
{
    protected static string $resource = TransResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
