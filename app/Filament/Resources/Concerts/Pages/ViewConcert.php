<?php

declare(strict_types=1);

namespace App\Filament\Resources\Concerts\Pages;

use App\Filament\Resources\Concerts\ConcertResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

final class ViewConcert extends ViewRecord
{
    protected static string $resource = ConcertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
