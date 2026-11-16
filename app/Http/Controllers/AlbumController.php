<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Contracts\View\View;

final class AlbumController extends Controller
{
    public function show(Album $album): View
    {
        return view('albums.show', [
            'album' => $album,
        ]);
    }
}
