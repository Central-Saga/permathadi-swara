<?php

namespace Database\Seeders;

use App\Models\Layanan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LayananSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $layananData = [
            [
                'name' => 'Kelas Tari Tradisional',
                'slug' => 'kelas-tari-tradisional',
                'description' => 'Pelajari berbagai tarian tradisional Indonesia dengan instruktur berpengalaman. Program ini mencakup tari Bali, Jawa, Sunda, dan daerah lainnya.',
                'price' => 500000,
                'is_active' => true,
            ],
            [
                'name' => 'Jasa Website Development',
                'slug' => 'jasa-website-development',
                'description' => 'Layanan pembuatan website profesional untuk bisnis Anda. Mulai dari company profile hingga e-commerce dengan teknologi terbaru.',
                'price' => 5000000,
                'is_active' => true,
            ],
            [
                'name' => 'Kelas Musik Gamelan',
                'slug' => 'kelas-musik-gamelan',
                'description' => 'Belajar memainkan alat musik gamelan tradisional. Cocok untuk semua usia dan tingkat keahlian.',
                'price' => 400000,
                'is_active' => true,
            ],
            [
                'name' => 'Konsultasi Desain Grafis',
                'slug' => 'konsultasi-desain-grafis',
                'description' => 'Konsultasi dan jasa desain grafis untuk kebutuhan branding, marketing, dan komunikasi visual bisnis Anda.',
                'price' => 1500000,
                'is_active' => true,
            ],
            [
                'name' => 'Workshop Fotografi',
                'slug' => 'workshop-fotografi',
                'description' => 'Workshop fotografi untuk pemula hingga advanced. Pelajari teknik komposisi, lighting, dan editing foto profesional.',
                'price' => 800000,
                'is_active' => true,
            ],
            [
                'name' => 'Kelas Tari Modern',
                'slug' => 'kelas-tari-modern',
                'description' => 'Kelas tari modern dan kontemporer untuk mengembangkan kreativitas dan ekspresi melalui gerakan.',
                'price' => 450000,
                'is_active' => true,
            ],
            [
                'name' => 'Jasa Branding & Logo',
                'slug' => 'jasa-branding-logo',
                'description' => 'Layanan pembuatan logo dan identitas visual brand yang profesional dan memorable untuk bisnis Anda.',
                'price' => 3000000,
                'is_active' => true,
            ],
            [
                'name' => 'Kelas Vokal',
                'slug' => 'kelas-vokal',
                'description' => 'Kelas vokal untuk mengembangkan kemampuan bernyanyi dengan teknik yang benar dan sehat.',
                'price' => 600000,
                'is_active' => true,
            ],
        ];

        foreach ($layananData as $data) {
            $layanan = Layanan::create($data);

            // Attach placeholder image via Spatie Media Library
            try {
                // Menggunakan placeholder image dari placeholder.com
                $imageUrl = 'https://via.placeholder.com/800x600/FF6B35/FFFFFF?text=' . urlencode($data['name']);
                
                $layanan->addMediaFromUrl($imageUrl)
                    ->toMediaCollection('layanan_cover');
            } catch (\Exception $e) {
                // Jika gagal download, skip attachment
                \Log::warning("Failed to attach image for layanan: {$data['name']} - {$e->getMessage()}");
            }
        }
    }
}
