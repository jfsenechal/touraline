<?php

declare(strict_types=1);

namespace App\Filament\Resources\Albums\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

final class AlbumForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom')
                    ->maxLength(255)
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label('Description')
                    ->helperText('Courte description affichée sur la carte de l\'album.')
                    ->rows(3)
                    ->maxLength(500)
                    ->columnSpanFull(),
                FileUpload::make('photos')
                    ->label('Photos')
                    ->helperText('Glissez les photos pour changer leur ordre.')
                    ->image()
                    ->multiple()
                    ->reorderable()
                    ->appendFiles()
                    ->panelLayout('grid')
                    ->disk('public')
                    ->directory('albums')
                    ->visibility('public')
                    ->maxSize(5120)
                    ->required()
                    ->columnSpanFull(),
                Select::make('cover')
                    ->label('Photo de couverture')
                    ->helperText('Sans choix, la première photo sert de couverture. Enregistrez l\'album pour pouvoir choisir parmi les photos qui viennent d\'être ajoutées.')
                    ->options(fn (Get $get): array => self::coverOptions($get('photos')))
                    ->allowHtml()
                    ->native(false)
                    ->visible(fn (Get $get): bool => self::coverOptions($get('photos')) !== [])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * Thumbnail options for the photos already saved on disk, keyed by path.
     *
     * Photos still being uploaded have no final path yet, so they are left out.
     *
     * @return array<string, string>
     */
    private static function coverOptions(mixed $photos): array
    {
        $options = [];

        foreach (array_values(array_filter((array) $photos, is_string(...))) as $index => $photo) {
            $options[$photo] = sprintf(
                '<span style="display:flex;align-items:center;gap:.75rem"><img src="%s" alt="" style="width:2.5rem;height:2.5rem;object-fit:cover;border-radius:.375rem" />Photo %d</span>',
                e(Storage::disk('public')->url($photo)),
                $index + 1,
            );
        }

        return $options;
    }
}
