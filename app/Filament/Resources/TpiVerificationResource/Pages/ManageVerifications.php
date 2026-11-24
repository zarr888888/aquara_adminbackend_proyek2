<?php
namespace App\Filament\Resources\TpiVerificationResource\Pages;
use App\Filament\Resources\TpiVerificationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
class ManageVerifications extends ManageRecords
{
    protected static string $resource = TpiVerificationResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()->label('Catat verifikasi / cabut pin')]; }
}