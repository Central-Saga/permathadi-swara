<?php

namespace Database\Seeders;

use App\Models\Galeri;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GaleriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $galeriData = [
            [
                'title' => 'Pertunjukan Gamelan di Pura',
                'description' => 'Dokumentasi pertunjukan gamelan tradisional Bali yang diselenggarakan di pura. Menampilkan berbagai gending klasik yang dimainkan oleh anggota sanggar.',
                'is_published' => true,
                'published_at' => now()->subMonths(2),
            ],
            [
                'title' => 'Kelas Tari Tradisional',
                'description' => 'Momen pembelajaran tari tradisional Bali di sanggar. Para siswa belajar berbagai gerakan dasar dan filosofi di balik setiap gerakan tari.',
                'is_published' => true,
                'published_at' => now()->subMonths(1),
            ],
            [
                'title' => 'Workshop Musik Gamelan',
                'description' => 'Workshop intensif untuk mempelajari teknik bermain gamelan. Workshop ini diikuti oleh berbagai kalangan dari pemula hingga yang sudah berpengalaman.',
                'is_published' => true,
                'published_at' => now()->subWeeks(3),
            ],
            [
                'title' => 'Pementasan Tari Kecak',
                'description' => 'Pementasan tari kecak yang spektakuler menceritakan epos Ramayana. Pertunjukan ini merupakan salah satu highlight dari kegiatan sanggar.',
                'is_published' => true,
                'published_at' => now()->subWeeks(2),
            ],
            [
                'title' => 'Festival Budaya Bali',
                'description' => 'Partisipasi sanggar dalam festival budaya Bali tahunan. Menampilkan berbagai pertunjukan seni tradisional yang memukau penonton.',
                'is_published' => true,
                'published_at' => now()->subWeeks(1),
            ],
            [
                'title' => 'Latihan Rutin Sanggar',
                'description' => 'Kegiatan latihan rutin anggota sanggar. Latihan ini dilakukan secara berkala untuk menjaga kualitas pertunjukan dan meningkatkan kemampuan anggota.',
                'is_published' => true,
                'published_at' => now()->subDays(5),
            ],
        ];

        // Array gambar dummy yang tersedia (hanya file yang benar-benar ada)
        $dummyImages = [
            '1.jpg',
            '2.jpg',
            '3.jpg',
            '4.jpg',
            '5.jpg',
            '6.jpg',
            '7.jpg',
            '8.jpg',
        ];

        foreach ($galeriData as $index => $data) {
            // Cek apakah galeri sudah ada berdasarkan title
            $galeri = Galeri::firstOrCreate(
                ['title' => $data['title']],
                $data
            );

            // Update jika sudah ada
            if ($galeri->wasRecentlyCreated === false) {
                $galeri->update($data);
            }

            // Hapus media lama jika ada (untuk re-seed dengan gambar baru)
            if ($galeri->hasMedia('galeri_images')) {
                $galeri->clearMediaCollection('galeri_images');
            }

            // Attach beberapa gambar dummy untuk setiap galeri
            try {
                // Setiap galeri akan memiliki 2-3 gambar
                $imageCount = rand(2, 3);
                for ($i = 0; $i < $imageCount; $i++) {
                    // Pilih gambar secara round-robin
                    $imageIndex = ($index * $imageCount + $i) % count($dummyImages);
                    $imageFileName = $dummyImages[$imageIndex];
                    $imagePath = public_path('images/dummy/' . $imageFileName);

                    if (file_exists($imagePath)) {
                        $galeri->addMedia($imagePath)
                            ->preservingOriginal()
                            ->usingName($data['title'] . ' - ' . ($i + 1))
                            ->usingFileName($imageFileName)
                            ->toMediaCollection('galeri_images');
                    }
                }
            } catch (\Exception $e) {
                // Jika gagal, skip attachment
                \Log::error("Failed to attach image for galeri: {$data['title']} - {$e->getMessage()}");
            }
        }
    }
}
