<?php

namespace Database\Factories;

use App\Models\Galeri;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Galeri>
 */
class GaleriFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titles = [
            'Pertunjukan Gamelan di Pura',
            'Kelas Tari Tradisional',
            'Workshop Musik Gamelan',
            'Pementasan Tari Kecak',
            'Festival Budaya Bali',
            'Latihan Rutin Sanggar',
            'Acara Adat Bali',
            'Konser Gamelan',
            'Pertunjukan Tari Legong',
            'Workshop Seni Tradisional',
        ];

        return [
            'title' => $this->faker->randomElement($titles),
            'description' => $this->faker->paragraph(3),
            'is_published' => $this->faker->boolean(80), // 80% chance published
            'published_at' => $this->faker->boolean(80) ? $this->faker->dateTimeBetween('-1 year', 'now') : null,
        ];
    }
}
