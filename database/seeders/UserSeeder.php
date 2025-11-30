<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Anggota;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('Super Admin');
        $user->removeRole('Anggota');

        $user = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('Admin');
        $user->removeRole('Anggota');

        // Create 1 anggota dummy lengkap dengan data anggota
        $anggotaUser = User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => Hash::make('password'),
        ]);
        $anggotaUser->assignRole('Anggota');

        Anggota::factory()->create([
            'user_id' => $anggotaUser->id,
            'telepon' => '081234567890',
            'alamat' => 'Jl. Sudirman No. 123, Jakarta Pusat, DKI Jakarta 10220',
            'tanggal_lahir' => '1990-05-15',
            'tanggal_registrasi' => now()->subMonths(6)->format('Y-m-d'),
            'status' => 'Aktif',
            'catatan' => 'Anggota aktif yang telah terdaftar selama 6 bulan. Sangat antusias mengikuti program latihan.',
        ]);
    }
}
