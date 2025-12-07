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
            // Latihan - Per Datang
            [
                'name' => 'Latihan Gong Kebyar (Per Datang)',
                'slug' => 'latihan-gong-kebyar-per-datang',
                'description' => 'Latihan gong kebyar per pertemuan. Cocok untuk yang ingin belajar secara fleksibel.',
                'price' => 10000,
                'duration' => 1, // 1 hari
                'is_active' => true,
            ],
            [
                'name' => 'Latihan Gong Kebyar (Per Bulan)',
                'slug' => 'latihan-gong-kebyar-per-bulan',
                'description' => 'Paket latihan gong kebyar 10x pertemuan per bulan. Hemat dan lebih terjadwal.',
                'price' => 100000,
                'duration' => 30, // 1 bulan
                'is_active' => true,
            ],
            [
                'name' => 'Latihan Mekendang Tunggal (Per Datang)',
                'slug' => 'latihan-mekendang-tunggal-per-datang',
                'description' => 'Latihan mekendang tunggal per pertemuan. Fokus pada teknik kendang tunggal.',
                'price' => 15000,
                'duration' => 1, // 1 hari
                'is_active' => true,
            ],
            [
                'name' => 'Latihan Mekendang Tunggal (Per Bulan)',
                'slug' => 'latihan-mekendang-tunggal-per-bulan',
                'description' => 'Paket latihan mekendang tunggal 10x pertemuan per bulan. Program intensif untuk penguasaan teknik.',
                'price' => 150000,
                'duration' => 30, // 1 bulan
                'is_active' => true,
            ],
            [
                'name' => 'Latihan Gong Suling (Per Datang)',
                'slug' => 'latihan-gong-suling-per-datang',
                'description' => 'Latihan gong suling per pertemuan. Belajar memainkan gong suling dengan teknik yang benar.',
                'price' => 10000,
                'duration' => 1, // 1 hari
                'is_active' => true,
            ],
            [
                'name' => 'Latihan Gong Suling (Per Bulan)',
                'slug' => 'latihan-gong-suling-per-bulan',
                'description' => 'Paket latihan gong suling 10x pertemuan per bulan. Program terstruktur untuk pemula hingga mahir.',
                'price' => 100000,
                'duration' => 30, // 1 bulan
                'is_active' => true,
            ],
            [
                'name' => 'Latihan Gender (Per Datang)',
                'slug' => 'latihan-gender-per-datang',
                'description' => 'Latihan gender per pertemuan. Pelajari teknik memainkan gender wayang dengan benar.',
                'price' => 15000,
                'duration' => 1, // 1 hari
                'is_active' => true,
            ],
            // Event - Sewa Gamelan
            [
                'name' => 'Sewa Gamelan Gong Kebyar (Per Hari)',
                'slug' => 'sewa-gamelan-gong-kebyar-per-hari',
                'description' => 'Sewa 1 set gamelan gong kebyar per hari. Hanya alat musik, tanpa penabuh.',
                'price' => 500000,
                'duration' => 1, // 1 hari
                'is_active' => true,
            ],
            [
                'name' => 'Sewa Gamelan Baleganjur (Per Hari)',
                'slug' => 'sewa-gamelan-baleganjur-per-hari',
                'description' => 'Sewa 1 set gamelan baleganjur per hari. Hanya alat musik, tanpa penabuh.',
                'price' => 250000,
                'duration' => 1, // 1 hari
                'is_active' => true,
            ],
            [
                'name' => 'Sewa Gamelan Gong Kebyar + Penabuh',
                'slug' => 'sewa-gamelan-gong-kebyar-penabuh',
                'description' => 'Sewa 1 set gamelan gong kebyar lengkap dengan penabuh profesional. Siap untuk acara Anda.',
                'price' => 2500000,
                'duration' => 1, // 1 hari
                'is_active' => true,
            ],
            [
                'name' => 'Sewa Gamelan Baleganjur + Penabuh',
                'slug' => 'sewa-gamelan-baleganjur-penabuh',
                'description' => 'Sewa 1 set gamelan baleganjur lengkap dengan penabuh profesional. Cocok untuk acara keagamaan dan upacara.',
                'price' => 1300000,
                'duration' => 1, // 1 hari
                'is_active' => true,
            ],
            [
                'name' => 'Sewa Gamelan Gong Suling + Penabuh',
                'slug' => 'sewa-gamelan-gong-suling-penabuh',
                'description' => 'Sewa 1 set gamelan gong suling lengkap dengan penabuh profesional. Suara yang lembut dan merdu.',
                'price' => 1000000,
                'duration' => 1, // 1 hari
                'is_active' => true,
            ],
            [
                'name' => 'Sewa Jasa Gender Wayang/Pawiwahan + Penabuh',
                'slug' => 'sewa-jasa-gender-wayang-penabuh',
                'description' => 'Sewa jasa gender wayang/pawiwahan lengkap dengan penabuh profesional. Khusus untuk acara pernikahan dan upacara adat.',
                'price' => 500000,
                'duration' => 1, // 1 hari
                'is_active' => true,
            ],
            [
                'name' => 'Kenaikan Tingkat Siswa (Per Semester)',
                'slug' => 'kenaikan-tingkat-siswa-per-semester',
                'description' => 'Biaya kenaikan tingkat siswa per semester. Untuk siswa yang telah menyelesaikan level sebelumnya.',
                'price' => 300000,
                'duration' => 180, // 6 bulan (1 semester)
                'is_active' => true,
            ],
        ];

        // Array gambar dummy yang tersedia (hanya file yang benar-benar ada)
        $dummyImages = [
            'ABD07813.jpg',
            'ABD07970.jpg',
            'ABD08518.jpg',
        ];

        foreach ($layananData as $index => $data) {
            // Cek apakah layanan sudah ada, jika ada update, jika tidak create
            $layanan = Layanan::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );

            // Hapus media lama jika ada (untuk re-seed dengan gambar baru)
            if ($layanan->hasMedia('layanan_cover')) {
                $layanan->clearMediaCollection('layanan_cover');
            }

            // Attach gambar dummy via Spatie Media Library dengan distribusi round-robin
            try {
                // Pilih gambar secara round-robin
                $imageIndex = $index % count($dummyImages);
                $imageFileName = $dummyImages[$imageIndex];
                $imagePath = public_path('images/dummy/' . $imageFileName);

                if (file_exists($imagePath)) {
                    $media = $layanan->addMedia($imagePath)
                        ->usingName($data['name'])
                        ->usingFileName($imageFileName)
                        ->toMediaCollection('layanan_cover');

                    // Verifikasi media sudah ter-attach
                    if ($media) {
                        \Log::info("Successfully attached image for layanan: {$data['name']} - {$imageFileName}");
                    } else {
                        \Log::warning("Failed to attach image for layanan: {$data['name']} - Media object is null");
                    }
                } else {
                    \Log::warning("Image file not found for layanan: {$data['name']} - {$imagePath}");
                }
            } catch (\Exception $e) {
                // Jika gagal, skip attachment
                \Log::error("Failed to attach image for layanan: {$data['name']} - {$e->getMessage()}", [
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
    }
}
