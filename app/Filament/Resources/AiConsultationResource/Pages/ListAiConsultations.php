<?php

namespace App\Filament\Resources\AiConsultationResource\Pages;

use App\Filament\Resources\AiConsultationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAiConsultations extends ListRecords
{
    protected static string $resource = AiConsultationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
