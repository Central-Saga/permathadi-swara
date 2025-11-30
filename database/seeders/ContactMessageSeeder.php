<?php

namespace Database\Seeders;

use App\Models\ContactMessage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContactMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $messages = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@email.com',
                'phone' => '081234567890',
                'subject' => 'Pertanyaan tentang Kelas Tari Tradisional',
                'message' => 'Selamat pagi, saya tertarik untuk mengikuti kelas tari tradisional. Bisa tolong informasikan jadwal dan biaya pendaftarannya? Terima kasih.',
                'status' => 'new',
                'created_at' => now()->subDays(2),
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@email.com',
                'phone' => '082345678901',
                'subject' => 'Informasi Workshop Fotografi',
                'message' => 'Halo, saya ingin bertanya tentang workshop fotografi. Apakah ada kelas untuk pemula? Dan berapa biayanya?',
                'status' => 'read',
                'created_at' => now()->subDays(5),
            ],
            [
                'name' => 'Ahmad Fauzi',
                'email' => null,
                'phone' => '083456789012',
                'subject' => 'Konsultasi Jasa Website Development',
                'message' => 'Saya butuh jasa pembuatan website untuk bisnis saya. Bisa tolong kirimkan portfolio dan estimasi biaya?',
                'status' => 'read',
                'created_at' => now()->subDays(7),
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@email.com',
                'phone' => null,
                'subject' => 'Pendaftaran Kelas Vokal',
                'message' => 'Saya ingin mendaftar untuk kelas vokal. Apakah masih ada kuota untuk bulan ini?',
                'status' => 'new',
                'created_at' => now()->subHours(5),
            ],
            [
                'name' => 'Rudi Hartono',
                'email' => 'rudi.hartono@email.com',
                'phone' => '084567890123',
                'subject' => 'Pertanyaan tentang Kelas Musik Gamelan',
                'message' => 'Apakah kelas musik gamelan cocok untuk anak usia 10 tahun? Dan apakah ada kelas khusus untuk anak-anak?',
                'status' => 'archived',
                'created_at' => now()->subDays(15),
            ],
            [
                'name' => 'Maya Sari',
                'email' => 'maya.sari@email.com',
                'phone' => '085678901234',
                'subject' => 'Konsultasi Desain Grafis',
                'message' => 'Saya membutuhkan jasa desain grafis untuk kebutuhan branding perusahaan. Bisa tolong kirimkan informasi lengkapnya?',
                'status' => 'read',
                'created_at' => now()->subDays(3),
            ],
            [
                'name' => 'Indra Gunawan',
                'email' => null,
                'phone' => '086789012345',
                'subject' => 'Informasi Jasa Branding & Logo',
                'message' => 'Saya ingin membuat logo untuk bisnis baru saya. Berapa biaya dan berapa lama proses pengerjaannya?',
                'status' => 'new',
                'created_at' => now()->subHours(2),
            ],
            [
                'name' => 'Lina Wijaya',
                'email' => 'lina.wijaya@email.com',
                'phone' => null,
                'subject' => 'Pertanyaan Kelas Tari Modern',
                'message' => 'Apakah kelas tari modern bisa diikuti oleh pemula yang belum pernah belajar tari sebelumnya?',
                'status' => 'read',
                'created_at' => now()->subDays(4),
            ],
            [
                'name' => 'Agus Prasetyo',
                'email' => 'agus.prasetyo@email.com',
                'phone' => '087890123456',
                'subject' => 'Kerjasama Event',
                'message' => 'Saya mewakili komunitas seni di daerah saya. Apakah sanggar ini terbuka untuk kerjasama event atau pertunjukan?',
                'status' => 'archived',
                'created_at' => now()->subDays(20),
            ],
            [
                'name' => 'Rina Kartika',
                'email' => 'rina.kartika@email.com',
                'phone' => '088901234567',
                'subject' => 'Pendaftaran Kelas Tari Tradisional',
                'message' => 'Saya ingin mendaftar untuk kelas tari tradisional. Bagaimana cara pendaftarannya dan dokumen apa saja yang diperlukan?',
                'status' => 'new',
                'created_at' => now()->subMinutes(30),
            ],
        ];

        foreach ($messages as $message) {
            ContactMessage::create($message);
        }
    }
}
