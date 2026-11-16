<?php

declare(strict_types=1);

use App\Models\Album;
use App\Models\Concert;

use function Pest\Laravel\get;

it('renders the home page with the concerts', function () {
    $concerts = Concert::factory(2)->create();

    get(route('home'))
        ->assertOk()
        ->assertSee('Nos concerts')
        ->assertSee($concerts[0]->title)
        ->assertSee($concerts[0]->excerpt)
        ->assertSee(route('concerts.show', $concerts[1]));
});

it('shows the concerts in position order', function () {
    Concert::factory()->create(['title' => 'Deuxième', 'position' => 2]);
    Concert::factory()->create(['title' => 'Premier', 'position' => 1]);

    get(route('home'))
        ->assertOk()
        ->assertSeeInOrder(['Premier', 'Deuxième']);
});

it('falls back to the default photos when there are no concerts', function () {
    get(route('home'))
        ->assertOk()
        ->assertSee('assets/img/concert-2.jpg');
});

it('renders a concert page', function () {
    $concert = Concert::factory()->create([
        'description' => '<p>Un <strong>superbe</strong> concert.</p>',
    ]);

    get(route('concerts.show', $concert))
        ->assertOk()
        ->assertSee($concert->title)
        ->assertSee('<strong>superbe</strong>', escape: false);
});

it('strips unsafe html from the description', function () {
    $concert = Concert::factory()->create([
        'description' => '<p>Bonjour</p><script>alert(1)</script>',
    ]);

    get(route('concerts.show', $concert))
        ->assertOk()
        ->assertSee('Bonjour')
        ->assertDontSee('<script>alert(1)</script>', escape: false);
});

it('returns 404 for an unknown concert', function () {
    get('/concerts/999')->assertNotFound();
});

it('renders the albums on the home page under "Albums photos"', function () {
    $album = Album::factory()->create(['photos' => ['albums/cover.jpg', 'albums/second.jpg']]);

    get(route('home'))
        ->assertOk()
        ->assertSeeInOrder(['Albums photos', $album->name, $album->description])
        ->assertSee('2 photos')
        ->assertSee('albums/cover.jpg')
        ->assertSee(route('albums.show', $album))
        ->assertDontSee('assets/img/meme-chante.jpg');
});

it('shows the chosen cover on the album card', function () {
    Album::factory()->create([
        'photos' => ['albums/first.jpg', 'albums/chosen.jpg'],
        'cover' => 'albums/chosen.jpg',
    ]);

    get(route('home'))
        ->assertOk()
        ->assertSee('albums/chosen.jpg')
        ->assertDontSee('albums/first.jpg');
});

it('shows the albums in position order', function () {
    Album::factory()->create(['name' => 'Album deux', 'position' => 2]);
    Album::factory()->create(['name' => 'Album un', 'position' => 1]);

    get(route('home'))
        ->assertOk()
        ->assertSeeInOrder(['Album un', 'Album deux']);
});

it('falls back to the default photo grid when there are no albums', function () {
    get(route('home'))
        ->assertOk()
        ->assertSee('assets/img/meme-chante.jpg');
});

it('renders an album page with its photos in order', function () {
    $album = Album::factory()->create(['photos' => ['albums/first.jpg', 'albums/second.jpg']]);

    get(route('albums.show', $album))
        ->assertOk()
        ->assertSee($album->name)
        ->assertSee($album->description)
        ->assertSeeInOrder(['albums/first.jpg', 'albums/second.jpg']);
});

it('returns 404 for an unknown album', function () {
    get('/albums/999')->assertNotFound();
});
