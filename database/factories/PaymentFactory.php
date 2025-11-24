<?php

namespace Database\Factories;

use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['pending', 'paid', 'failed']);
        $paidAt = $status === 'paid' ? fake()->dateTimeBetween('-1 year', 'now') : null;

        return [
            'subscription_id' => Subscription::factory(),
            'amount' => fake()->randomFloat(2, 50000, 1000000),
            'method' => fake()->randomElement(['cash', 'transfer', 'qris', 'other']),
            'status' => $status,
            'paid_at' => $paidAt,
            'proof_url' => fake()->optional(0.6)->url(),
        ];
    }
}
