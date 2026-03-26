<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ContentType;
use App\Models\User;
use Illuminate\Database\Seeder;

class BlogArticleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@kompeta.test')->firstOrFail();

        $news = ContentType::query()->where('slug', 'news')->firstOrFail();
        $tips = ContentType::query()->where('slug', 'tips')->firstOrFail();
        $careerInsight = ContentType::query()->where('slug', 'career-insight')->firstOrFail();
        $successStory = ContentType::query()->where('slug', 'success-story')->firstOrFail();

        $articles = [
            [
                'slug' => 'artikel-perdana-kompeta',
                'content_type_id' => $news->id,
                'title' => 'Artikel Perdana Kompeta',
                'body' => 'Kompeta resmi meluncurkan platform kolaborasi sekolah, siswa, dan mitra dunia kerja.',
                'view_count' => 120,
                'thumbnail_path' => 'seed/template-logo.jpeg',
                'is_published' => true,
                'published_at' => now()->subDays(10),
            ],
            [
                'slug' => 'tips-menyusun-cv-siswa-smk-sma',
                'content_type_id' => $tips->id,
                'title' => 'Tips Menyusun CV untuk Siswa SMA/SMK',
                'body' => 'Panduan ringkas menyusun CV yang jelas, relevan, dan siap dilampirkan pada lamaran kerja.',
                'view_count' => 85,
                'thumbnail_path' => 'seed/template-logo.jpeg',
                'is_published' => true,
                'published_at' => now()->subDays(7),
            ],
            [
                'slug' => 'mengenal-jenis-kontrak-kerja-untuk-pelajar',
                'content_type_id' => $careerInsight->id,
                'title' => 'Mengenal Jenis Kontrak Kerja untuk Pelajar',
                'body' => 'Penjelasan singkat perbedaan kontrak magang, paruh waktu, dan freelance.',
                'view_count' => 62,
                'thumbnail_path' => 'seed/template-logo.jpeg',
                'is_published' => true,
                'published_at' => now()->subDays(5),
            ],
            [
                'slug' => 'kisah-sukses-alumni-yang-diterima-di-mitra',
                'content_type_id' => $successStory->id,
                'title' => 'Kisah Sukses Alumni yang Diterima di Mitra',
                'body' => 'Cerita alumni yang berhasil lolos seleksi mitra setelah menyusun portofolio dengan baik.',
                'view_count' => 43,
                'thumbnail_path' => 'seed/template-logo.jpeg',
                'is_published' => true,
                'published_at' => now()->subDays(2),
            ],
            [
                'slug' => 'draft-artikel-internal-admin',
                'content_type_id' => $news->id,
                'title' => 'Draft Artikel Internal Admin',
                'body' => 'Artikel ini disiapkan sebagai contoh data draft.',
                'view_count' => 0,
                'thumbnail_path' => 'seed/template-logo.jpeg',
                'is_published' => false,
                'published_at' => null,
            ],
        ];

        foreach ($articles as $item) {
            Article::query()->updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'content_type_id' => $item['content_type_id'],
                    'title' => $item['title'],
                    'body' => $item['body'],
                    'view_count' => $item['view_count'],
                    'thumbnail_path' => $item['thumbnail_path'],
                    'is_published' => $item['is_published'],
                    'published_at' => $item['published_at'],
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                ]
            );
        }
    }
}
