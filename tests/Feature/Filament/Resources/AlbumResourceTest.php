<?php

declare(strict_types=1);

use App\Filament\Resources\Albums\Pages\CreateAlbum;
use App\Filament\Resources\Albums\Pages\EditAlbum;
use App\Filament\Resources\Albums\Pages\ListAlbums;
use App\Models\Album;
use Filament\Actions\DeleteAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    Storage::fake('public');
});

it('can render the index page', function () {
    livewire(ListAlbums::class)
        ->assertOk();
});

it('can render the create page', function () {
    livewire(CreateAlbum::class)
        ->assertOk();
});

it('can render the edit page', function () {
    $album = Album::factory()->create();

    livewire(EditAlbum::class, [
        'record' => $album->id,
    ])
        ->assertOk()
        ->assertSchemaStateSet([
            'name' => $album->name,
            'description' => $album->description,
        ]);
});

it('can render column', function (string $column) {
    livewire(ListAlbums::class)
        ->assertCanRenderTableColumn($column);
})->with(['position', 'photos', 'name', 'description', 'photos_count', 'created_at']);

it('lists albums by position', function () {
    $albums = collect([3, 1, 2])->map(fn (int $position) => Album::factory()->create(['position' => $position]));

    livewire(ListAlbums::class)
        ->loadTable()
        ->assertCanSeeTableRecords($albums->sortBy('position'), inOrder: true);
});

it('puts a new album at the end of the list', function () {
    Album::factory()->create(['position' => 5]);

    expect(Album::factory()->create()->position)->toBe(6);
});

it('can reorder albums', function () {
    [$first, $second, $third] = Album::factory(3)->create();

    livewire(ListAlbums::class)
        ->call('reorderTable', [(string) $third->id, (string) $first->id, (string) $second->id]);

    expect(Album::query()->ordered()->pluck('id')->all())
        ->toBe([$third->id, $first->id, $second->id]);
});

it('can search column', function (string $column) {
    $records = Album::factory(5)->create();

    $value = $records->first()->{$column};

    livewire(ListAlbums::class)
        ->loadTable()
        ->searchTable($value)
        ->assertCanSeeTableRecords($records->where($column, $value))
        ->assertCanNotSeeTableRecords($records->where($column, '!=', $value));
})->with(['name']);

it('can create an album with several photos', function () {
    $album = Album::factory()->make();

    livewire(CreateAlbum::class)
        ->fillForm([
            'name' => $album->name,
            'description' => $album->description,
            'photos' => [
                UploadedFile::fake()->image('one.jpg'),
                UploadedFile::fake()->image('two.jpg'),
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    $created = Album::query()->sole();

    expect($created->name)->toBe($album->name)
        ->and($created->description)->toBe($album->description)
        ->and($created->photos)->toHaveCount(2)
        ->each->toStartWith('albums/');

    Storage::disk('public')->assertExists($created->photos);
});

it('can update an album', function () {
    $photo = UploadedFile::fake()->image('one.jpg')->store('albums', 'public');
    $album = Album::factory()->create(['photos' => [$photo]]);
    $newData = Album::factory()->make();

    livewire(EditAlbum::class, [
        'record' => $album->id,
    ])
        ->fillForm([
            'name' => $newData->name,
            'description' => $newData->description,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas(Album::class, [
        'id' => $album->id,
        'name' => $newData->name,
        'description' => $newData->description,
    ]);

    expect($album->refresh()->photos)->toBe([$photo]);
    Storage::disk('public')->assertExists($photo);
});

it('can pick the cover among the saved photos', function () {
    $photos = [
        UploadedFile::fake()->image('one.jpg')->store('albums', 'public'),
        UploadedFile::fake()->image('two.jpg')->store('albums', 'public'),
    ];
    $album = Album::factory()->create(['photos' => $photos]);

    livewire(EditAlbum::class, [
        'record' => $album->id,
    ])
        ->assertFormFieldVisible('cover')
        ->fillForm(['cover' => $photos[1]])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertNotified();

    expect($album->refresh())
        ->cover->toBe($photos[1])
        ->cover_url->toBe(Storage::disk('public')->url($photos[1]));
});

it('rejects a cover that is not one of the album photos', function () {
    $photo = UploadedFile::fake()->image('one.jpg')->store('albums', 'public');
    $album = Album::factory()->create(['photos' => [$photo]]);

    livewire(EditAlbum::class, [
        'record' => $album->id,
    ])
        ->fillForm(['cover' => 'albums/other.jpg'])
        ->call('save')
        ->assertHasFormErrors(['cover']);

    expect($album->refresh()->cover)->toBeNull();
});

it('hides the cover field until photos are saved', function () {
    livewire(CreateAlbum::class)
        ->assertFormFieldHidden('cover');
});

it('uses the first photo as cover when none is chosen', function () {
    $album = Album::factory()->create(['photos' => ['albums/first.jpg', 'albums/second.jpg']]);

    expect($album->cover_url)->toBe(Storage::disk('public')->url('albums/first.jpg'));
});

it('resets the cover when its photo is removed from the album', function () {
    $album = Album::factory()->create([
        'photos' => ['albums/first.jpg', 'albums/second.jpg'],
        'cover' => 'albums/second.jpg',
    ]);

    $album->update(['photos' => ['albums/first.jpg']]);

    expect($album->refresh()->cover)->toBeNull();
});

it('deletes the photo files when the album is deleted', function () {
    $paths = [
        UploadedFile::fake()->image('one.jpg')->store('albums', 'public'),
        UploadedFile::fake()->image('two.jpg')->store('albums', 'public'),
    ];
    $album = Album::factory()->create(['photos' => $paths]);

    livewire(EditAlbum::class, [
        'record' => $album->id,
    ])
        ->callAction(DeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    assertDatabaseMissing($album);
    Storage::disk('public')->assertMissing($paths);
});

it('deletes only the photo files removed from the album', function () {
    $keptPath = UploadedFile::fake()->image('kept.jpg')->store('albums', 'public');
    $removedPath = UploadedFile::fake()->image('removed.jpg')->store('albums', 'public');
    $album = Album::factory()->create(['photos' => [$keptPath, $removedPath]]);

    $album->update(['photos' => [$keptPath]]);

    Storage::disk('public')->assertExists($keptPath);
    Storage::disk('public')->assertMissing($removedPath);
});

it('validates the form data', function (array $data, array $errors) {
    livewire(CreateAlbum::class)
        ->fillForm([
            'name' => 'Concert de Noël',
            'photos' => [UploadedFile::fake()->image('one.jpg')],
            ...$data,
        ])
        ->call('create')
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    '`name` is required' => [['name' => null], ['name' => 'required']],
    '`name` is max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
    '`description` is max 500 characters' => [['description' => Str::random(501)], ['description' => 'max']],
    '`photos` is required' => [['photos' => []], ['photos' => 'required']],
]);
