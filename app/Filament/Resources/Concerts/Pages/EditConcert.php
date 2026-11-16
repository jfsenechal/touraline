<?php

declare(strict_types=1);

namespace App\Filament\Resources\Concerts\Pages;

use App\Filament\Resources\Concerts\ConcertResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

final class EditConcert extends EditRecord
{
    protected static string $resource = ConcertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return self::getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
