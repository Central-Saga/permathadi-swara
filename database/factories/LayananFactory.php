<?php

namespace Database\Factories;

use App\Models\Layanan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Layanan>
 */
class LayananFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->randomElement([
            'Kelas Tari Tradisional',
            'Jasa Website Development',
            'Kelas Musik Gamelan',
            'Konsultasi Desain Grafis',
            'Workshop Fotografi',
            'Kelas Tari Modern',
            'Jasa Branding & Logo',
            'Kelas Vokal',
            'Jasa Video Production',
            'Kelas Teater',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraph(3),
            'price' => $this->faker->randomFloat(2, 100000, 5000000),
            'duration' => $this->faker->randomElement([30, 60, 90, 180, 365]), // Durasi dalam hari: 1, 2, 3, 6 bulan, atau 1 tahun
            'is_active' => $this->faker->boolean(80), // 80% chance aktif
        ];
    }
}
