<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AnggotaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $anggota = User::factory()->create([
            'name' => 'Anggota',
            'email' => 'anggota@example.com',
            'password' => Hash::make('password'),
        ]);
        $anggota->assignRole('Anggota');

        $anggota = User::factory(10)->create();
        $anggota->assignRole('Anggota');
    }
}
