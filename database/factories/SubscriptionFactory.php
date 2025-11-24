<?php

namespace Database\Factories;

use App\Models\Anggota;
use App\Models\Layanan;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-1 year', 'now');
        $endDate = fake()->optional(0.7)->dateTimeBetween($startDate, '+1 year');

        return [
            'anggota_id' => Anggota::factory(),
            'layanan_id' => Layanan::factory(),
            'status' => fake()->randomElement(['pending', 'active', 'expired', 'canceled']),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'notes' => fake()->optional(0.5)->sentence(),
        ];
    }
}
