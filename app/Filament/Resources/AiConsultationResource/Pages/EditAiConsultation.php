<?php

namespace App\Filament\Resources\AiConsultationResource\Pages;

use App\Filament\Resources\AiConsultationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAiConsultation extends EditRecord
{
    protected static string $resource = AiConsultationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
