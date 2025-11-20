<?php

namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AnggotaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create single anggota with user
        $user = User::factory()->create([
            'name' => 'Anggota',
            'email' => 'anggota@example.com',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('Anggota');
        
        Anggota::factory()->create([
            'user_id' => $user->id,
        ]);

        // Create 10 anggota with users
        Anggota::factory(10)->create()->each(function ($anggota) {
            $anggota->user->assignRole('Anggota');
        });
    }
}
