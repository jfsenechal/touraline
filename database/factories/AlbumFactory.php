<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Album;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Album>
 */
final class AlbumFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'description' => fake()->sentence(12),
            'photos' => [
                'albums/'.fake()->uuid().'.jpg',
                'albums/'.fake()->uuid().'.jpg',
            ],
            'cover' => null,
        ];
    }
}
