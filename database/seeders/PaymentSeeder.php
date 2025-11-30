<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subscriptions = Subscription::with('layanan')->get();

        if ($subscriptions->isEmpty()) {
            $this->command->warn('Subscriptions is empty. Please seed subscriptions first.');
            return;
        }

        // Create payments for some subscriptions
        // Amount harus sesuai dengan harga layanan
        foreach ($subscriptions->random(min(15, $subscriptions->count())) as $subscription) {
            $layanan = $subscription->layanan;
            $amount = $layanan->price ?? fake()->randomFloat(2, 50000, 1000000);
            $status = fake()->randomElement(['pending', 'paid', 'failed']);
            $paidAt = $status === 'paid' ? fake()->dateTimeBetween($subscription->created_at, 'now') : null;

            Payment::factory()->create([
                'subscription_id' => $subscription->id,
                'amount' => $amount,
                'status' => $status,
                'paid_at' => $paidAt,
            ]);
        }
    }
}
