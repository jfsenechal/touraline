<?php

declare(strict_types=1);

namespace App\Filament\Resources\Albums\Tables;

use App\Models\Album;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class AlbumsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('position')
            ->reorderable('position')
            ->columns([
                TextColumn::make('position')
                    ->label('#')
                    ->sortable()
                    ->width('1%'),
                ImageColumn::make('photos')
                    ->label('Photos')
                    ->disk('public')
                    ->visibility('public')
                    ->stacked()
                    ->limit(3)
                    ->limitedRemainingText(),
                TextColumn::make('name')
                    ->label('Nom')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(60)
                    ->toggleable(),
                TextColumn::make('photos_count')
                    ->label('Nombre de photos')
                    ->state(fn (Album $record): int => count($record->photos))
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
