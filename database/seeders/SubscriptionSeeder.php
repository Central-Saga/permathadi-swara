<?php

namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\Layanan;
use App\Models\Subscription;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing anggota and layanan
        $anggotas = Anggota::all();
        $layanans = Layanan::all();

        if ($anggotas->isEmpty() || $layanans->isEmpty()) {
            $this->command->warn('Anggota or Layanan is empty. Please seed them first.');
            return;
        }

        // Create 20 subscriptions with existing anggota and layanan
        Subscription::factory(20)->create([
            'anggota_id' => fn() => $anggotas->random()->id,
            'layanan_id' => fn() => $layanans->random()->id,
        ]);
    }
}
