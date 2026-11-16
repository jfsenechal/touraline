<?php

declare(strict_types=1);

namespace App\Filament\Resources\Albums\Schemas;

use App\Models\Album;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class AlbumInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Nom')
                    ->columnSpanFull(),
                TextEntry::make('description')
                    ->label('Description')
                    ->placeholder('Aucune description')
                    ->columnSpanFull(),
                ImageEntry::make('cover')
                    ->label('Photo de couverture')
                    ->state(fn (Album $record): ?string => $record->cover ?? $record->photos[0] ?? null)
                    ->disk('public')
                    ->visibility('public')
                    ->imageHeight(240)
                    ->columnSpanFull(),
                ImageEntry::make('photos')
                    ->label('Photos')
                    ->disk('public')
                    ->visibility('public')
                    ->imageHeight(120)
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
