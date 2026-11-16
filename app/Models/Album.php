<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AlbumFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * @property list<string> $photos Paths of the photos on the public disk, in display order.
 * @property ?string $cover Path of the photo chosen as cover, or null to use the first photo.
 */
final class Album extends Model
{
    /** @use HasFactory<AlbumFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        /* New albums go to the end of the list unless a position is given. */
        self::creating(function (Album $album): void {
            if (blank($album->position)) {
                $album->position = (int) self::query()->max('position') + 1;
            }
        });

        /* A cover must be one of the album photos, otherwise the first photo is used. */
        self::saving(function (Album $album): void {
            if (! in_array($album->cover, $album->photos, true)) {
                $album->cover = null;
            }
        });

        /* Remove photos from disk when they are taken out of the album or the album is deleted. */
        self::updated(function (Album $album): void {
            if ($album->wasChanged('photos')) {
                Storage::disk('public')->delete(array_values(array_diff(
                    $album->getOriginal('photos') ?? [],
                    $album->photos,
                )));
            }
        });

        self::deleted(function (Album $album): void {
            Storage::disk('public')->delete($album->photos);
        });
    }

    /**
     * Order albums as arranged in the admin.
     *
     * @param  Builder<Album>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('position')->orderBy('id');
    }

    /**
     * Public URLs of the photos, in display order.
     *
     * @return Attribute<list<string>, never>
     */
    protected function photoUrls(): Attribute
    {
        return Attribute::get(fn (): array => array_map(
            fn (string $photo): string => Storage::disk('public')->url($photo),
            $this->photos,
        ));
    }

    /**
     * Public URL of the chosen cover, else of the first photo, or null when the album is empty.
     *
     * @return Attribute<?string, never>
     */
    protected function coverUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            $cover = $this->cover ?? $this->photos[0] ?? null;

            return $cover === null ? null : Storage::disk('public')->url($cover);
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'photos' => 'array',
            'position' => 'integer',
        ];
    }
}
