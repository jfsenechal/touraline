<?php

declare(strict_types=1);

namespace App\Filament\Resources\Concerts\Schemas;

use App\Models\Concert;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class ConcertInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title')
                    ->label('Titre')
                    ->columnSpanFull(),
                TextEntry::make('excerpt')
                    ->label('Extrait')
                    ->columnSpanFull(),
                TextEntry::make('description')
                    ->label('Description')
                    ->state(fn (Concert $record): string => $record->description_html)
                    ->html()
                    ->prose()
                    ->placeholder('Aucune description')
                    ->columnSpanFull(),
                ImageEntry::make('image')
                    ->label('Image')
                    ->disk('public')
                    ->visibility('public')
                    ->imageHeight(240)
                    ->placeholder('Aucune image')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
