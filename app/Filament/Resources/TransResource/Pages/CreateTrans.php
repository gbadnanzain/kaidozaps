<?php

namespace App\Filament\Resources\TransResource\Pages;

use App\Filament\Resources\TransResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTrans extends CreateRecord
{
    protected static string $resource = TransResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
