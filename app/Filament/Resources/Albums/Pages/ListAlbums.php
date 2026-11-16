<?php

declare(strict_types=1);

namespace App\Filament\Resources\Albums\Pages;

use App\Filament\Resources\Albums\AlbumResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

final class ListAlbums extends ListRecords
{
    protected static string $resource = AlbumResource::class;

    protected static ?string $title = 'Liste des albums';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter un album')
                ->icon(Heroicon::Plus),
        ];
    }
}
