<?php

declare(strict_types=1);

use App\Filament\Resources\Concerts\ConcertResource;

use function Pest\Laravel\get;

it('links to the front site from the admin navigation', function () {
    get(ConcertResource::getUrl('index'))
        ->assertOk()
        ->assertSee('Voir le site')
        ->assertSee('href="'.route('home').'"', escape: false);
});
