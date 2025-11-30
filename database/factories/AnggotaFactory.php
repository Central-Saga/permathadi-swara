<?php

namespace Database\Factories;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Anggota>
 */
class AnggotaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'telepon' => fake()->phoneNumber(),
            'alamat' => fake()->address(),
            'tanggal_lahir' => fake()->date('Y-m-d', '-18 years'),
            'tanggal_registrasi' => fake()->date('Y-m-d', '-1 year'),
            'status' => fake()->randomElement(['Aktif', 'Non Aktif']),
            'catatan' => fake()->optional()->sentence(),
        ];
    }
}
