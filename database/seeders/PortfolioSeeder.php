<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\Contact;
use App\Models\Project;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class PortfolioSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure storage directories exist
        Storage::disk('public')->makeDirectory('projects');
        Storage::disk('public')->makeDirectory('blogs');

        // Generate placeholder images
        $this->generatePlaceholders();

        // Hero settings
        SiteSetting::set('hero_title', 'Halo, saya Seorang Developer Laravel');
        SiteSetting::set('hero_subtitle', 'Membangun pengalaman web dengan kode yang bersih dan desain yang fungsional.');
        SiteSetting::set('hero_badge', 'Full Stack Developer');

        // About & Hobbies
        SiteSetting::set('about', "Developer Full Stack dengan pengalaman 5+ tahun membangun aplikasi web yang tangguh dan mudah diskalakan. Stack utama saya adalah Laravel + Vue.js, meskipun saya juga bekerja dengan Livewire, Inertia.js, dan TailwindCSS.\n\nSaya bersemangat menulis kode yang bersih, merancang arsitektur yang matang, dan mengotomatiskan segala hal yang bisa diotomatisasi. Saat ini saya bekerja sebagai freelance membantu startup dan perusahaan mewujudkan ide mereka ke dunia digital.");

        SiteSetting::set('hobbies', "🎮 Gaming — Saya penggemar RPG dan game indie. Akhir-akhir ini ketagihan Baldur's Gate 3.\n☕ Kopi — Saya menyeduh kopi sendiri setiap pagi dengan metode V60. Ritualnya adalah bagian dari proses kreatif.\n📚 Membaca — Saya membaca tentang arsitektur software, produktivitas, dan fiksi ilmiah.\n🏃 Lari — Saya berlari 3 kali seminggu. Membantu mengosongkan pikiran dan berpikir lebih jernih.\n🎵 Musik — Saya selalu ngoding sambil mendengarkan musik. Lo-fi, post-rock, dan jazz adalah genre favorit saya.");

        // Side Projects (with placeholder images)
        Project::updateOrCreate(['slug' => 'devtracker'], [
            'title' => 'DevTracker',
            'description' => 'Aplikasi pelacak waktu untuk developer. Mencatat jam kerja per proyek, membuat laporan mingguan, dan terintegrasi dengan GitHub untuk melacak commit secara otomatis.',
            'image' => 'projects/devtracker.svg',
            'url' => 'https://devtracker.example.com',
            'repo_url' => 'https://github.com/user/devtracker',
            'tags' => ['Laravel', 'Vue.js', 'TailwindCSS', 'GitHub API'],
            'type' => 'side_project',
            'featured' => true,
            'sort_order' => 1,
            'published' => true,
        ]);

        Project::updateOrCreate(['slug' => 'snippetvault'], [
            'title' => 'SnippetVault',
            'description' => 'Pengelola snippet kode dengan syntax highlighting, tag, pencarian full-text, dan sinkronisasi antar perangkat. Dibuat untuk developer yang ingin punya koleksi pribadi yang terorganisir.',
            'image' => 'projects/snippetvault.svg',
            'repo_url' => 'https://github.com/user/snippetvault',
            'tags' => ['Laravel', 'Livewire', 'Alpine.js', 'SQLite'],
            'type' => 'side_project',
            'featured' => false,
            'sort_order' => 2,
            'published' => true,
        ]);

        Project::updateOrCreate(['slug' => 'cli-invoice'], [
            'title' => 'CLI Invoice',
            'description' => 'Alat command-line untuk membuat faktur PDF dari file YAML. Cocok untuk freelancer yang ingin menagih dengan cepat tanpa membuka aplikasi apa pun.',
            'image' => 'projects/cli-invoice.svg',
            'repo_url' => 'https://github.com/user/cli-invoice',
            'tags' => ['PHP', 'Laravel Zero', 'DomPDF'],
            'type' => 'side_project',
            'sort_order' => 3,
            'published' => true,
        ]);

        // Portfolio (with placeholder images)
        Project::updateOrCreate(['slug' => 'mediconnect'], [
            'title' => 'MediConnect — Platform Telemedicine',
            'description' => 'Platform telemedicine lengkap dengan video call, manajemen janji temu, rekam medis digital, dan gateway pembayaran. Dibangun untuk klinik dengan 200+ dokter aktif dan 15.000+ pasien terdaftar.',
            'image' => 'projects/mediconnect.svg',
            'url' => 'https://mediconnect.example.com',
            'tags' => ['Laravel', 'Vue.js', 'WebRTC', 'Stripe', 'Redis'],
            'type' => 'portfolio',
            'featured' => true,
            'sort_order' => 1,
            'published' => true,
        ]);

        Project::updateOrCreate(['slug' => 'freshmarket'], [
            'title' => 'FreshMarket — Toko Produk Organik',
            'description' => 'Toko online dengan katalog dinamis, keranjang belanja, langganan mingguan keranjang organik, dan sistem pengiriman dengan pelacakan real-time. Memproses 500+ pesanan per minggu.',
            'image' => 'projects/freshmarket.svg',
            'url' => 'https://freshmarket.example.com',
            'tags' => ['Laravel', 'Inertia.js', 'Vue 3', 'MercadoPago', 'Google Maps API'],
            'type' => 'portfolio',
            'featured' => true,
            'sort_order' => 2,
            'published' => true,
        ]);

        Project::updateOrCreate(['slug' => 'eduplatform'], [
            'title' => 'EduPlatform — LMS Korporat',
            'description' => 'Sistem manajemen pembelajaran untuk perusahaan dengan kursus video, evaluasi, sertifikat otomatis, dan dashboard progres. Digunakan oleh 3 perusahaan dengan total 2.000+ karyawan.',
            'image' => 'projects/eduplatform.svg',
            'url' => 'https://eduplatform.example.com',
            'tags' => ['Laravel', 'Vue.js', 'FFmpeg', 'AWS S3', 'Pusher'],
            'type' => 'portfolio',
            'sort_order' => 3,
            'published' => true,
        ]);

        Project::updateOrCreate(['slug' => 'propmanager'], [
            'title' => 'PropManager — Manajemen Properti',
            'description' => 'CRM properti dengan publikasi otomatis ke portal, manajemen lead, jadwal kunjungan, dan pembuatan kontrak. Terintegrasi dengan portal properti dan marketplace.',
            'image' => 'projects/propmanager.svg',
            'tags' => ['Laravel', 'Livewire', 'TailwindCSS', 'REST APIs'],
            'type' => 'portfolio',
            'sort_order' => 4,
            'published' => true,
        ]);

        // Blogs (with placeholder images)
        Blog::updateOrCreate(['slug' => 'como-estructuro-mis-proyectos-laravel-2025'], [
            'title' => 'Cara Saya Menstruktur Proyek Laravel di 2025',
            'excerpt' => 'Setelah bertahun-tahun bekerja dengan Laravel, saya menemukan struktur yang memungkinkan proyek diskalakan tanpa kehilangan kewarasan. Saya bagikan pendekatan saya dengan Actions, DTOs, dan Service classes.',
            'image' => 'blogs/laravel-structure.svg',
            'content' => "## Masalah dengan struktur bawaan\n\nLaravel sangat fleksibel, tetapi fleksibilitas itu bisa menjadi pedang bermata dua.\n\n## Struktur saya saat ini\n\nSaya menggunakan kombinasi Actions, DTOs, dan Services.",
            'tags' => ['Laravel', 'Arsitektur', 'PHP', 'Best Practices'],
            'published' => true,
            'published_at' => now()->subDays(5),
        ]);

        Blog::updateOrCreate(['slug' => 'vue3-composables-que-uso-siempre'], [
            'title' => 'Vue 3 Composables yang Saya Pakai di Semua Proyek',
            'excerpt' => 'Kumpulan composable yang dapat digunakan ulang dan menghemat berjam-jam kerja: useDebounce, usePagination, useConfirmDialog, dan lainnya.',
            'image' => 'blogs/vue-composables.svg',
            'content' => "## Kenapa composables?\n\nComposables adalah cara paling elegan untuk menggunakan ulang logika di Vue 3.",
            'tags' => ['Vue.js', 'Composables', 'TypeScript', 'Frontend'],
            'published' => true,
            'published_at' => now()->subDays(12),
        ]);

        Blog::updateOrCreate(['slug' => 'deploy-laravel-github-actions-docker'], [
            'title' => 'Deploy Laravel dengan GitHub Actions dan Docker',
            'excerpt' => 'Pipeline CI/CD lengkap saya: test otomatis, build Docker, deploy ke produksi tanpa downtime.',
            'image' => 'blogs/docker-deploy.svg',
            'content' => "## Setup-nya\n\nSaya menggunakan VPS dengan Docker Compose dan GitHub Actions sebagai CI/CD.",
            'tags' => ['DevOps', 'Docker', 'GitHub Actions', 'Laravel', 'CI/CD'],
            'published' => true,
            'published_at' => now()->subDays(20),
        ]);

        Blog::updateOrCreate(['slug' => 'tailwindcss-v4-migracion'], [
            'title' => 'TailwindCSS v4: Yang Berubah dan Cara Migrasi',
            'excerpt' => 'Tailwind v4 membawa perubahan besar dalam konfigurasi. Saya jelaskan perbedaan utamanya dan cara migrasinya.',
            'image' => 'blogs/tailwind-v4.svg',
            'content' => "## Perubahan utama\n\nKonfigurasi CSS-first, engine kompilasi baru, dan variabel CSS native.",
            'tags' => ['TailwindCSS', 'CSS', 'Frontend', 'Migrasi'],
            'published' => true,
            'published_at' => now()->subDays(30),
        ]);

        Blog::updateOrCreate(['slug' => 'autenticacion-laravel-fortify-vue3'], [
            'title' => 'Autentikasi dengan Laravel Fortify + Vue 3',
            'excerpt' => 'Panduan lengkap mengimplementasikan login, registrasi, 2FA, dan pemulihan kata sandi menggunakan Fortify sebagai backend.',
            'image' => 'blogs/fortify-auth.svg',
            'content' => "## Kenapa Fortify?\n\nFortify memberi Anda seluruh logika autentikasi tanpa memaksakan tampilan tertentu.",
            'tags' => ['Laravel', 'Fortify', 'Vue.js', 'Autentikasi', '2FA'],
            'published' => true,
            'published_at' => now()->subDays(45),
        ]);

        // Contacts
        Contact::updateOrCreate(['email' => 'maria.gonzalez@example.com'], [
            'name' => 'Maria Gonzalez',
            'subject' => 'Proyek toko online',
            'message' => 'Halo, saya punya toko pakaian dan ingin meluncurkan toko online saya. Bisakah kita menjadwalkan panggilan?',
            'read' => false,
        ]);

        Contact::updateOrCreate(['email' => 'carlos.mendoza@example.com'], [
            'name' => 'Carlos Mendoza',
            'subject' => 'Konsultasi Laravel',
            'message' => 'Kami tim berisi 4 developer yang sedang bermigrasi dari CodeIgniter ke Laravel. Apakah Anda menawarkan konsultasi per jam?',
            'read' => false,
        ]);

        Contact::updateOrCreate(['email' => 'ana.rodriguez@example.com'], [
            'name' => 'Ana Rodriguez',
            'subject' => 'Kolaborasi open source',
            'message' => 'Saya melihat proyek SnippetVault Anda di GitHub. Apakah Anda tertarik untuk berkolaborasi?',
            'read' => true,
        ]);
    }

    private function generatePlaceholders(): void
    {
        $projects = [
            'projects/devtracker.svg' => ['DevTracker', '#6366f1', '#818cf8'],
            'projects/snippetvault.svg' => ['SnippetVault', '#8b5cf6', '#a78bfa'],
            'projects/cli-invoice.svg' => ['CLI Invoice', '#06b6d4', '#22d3ee'],
            'projects/mediconnect.svg' => ['MediConnect', '#10b981', '#34d399'],
            'projects/freshmarket.svg' => ['FreshMarket', '#f59e0b', '#fbbf24'],
            'projects/eduplatform.svg' => ['EduPlatform', '#3b82f6', '#60a5fa'],
            'projects/propmanager.svg' => ['PropManager', '#ef4444', '#f87171'],
        ];

        $blogs = [
            'blogs/laravel-structure.svg' => ['Laravel', '#ff2d20', '#ff6b5b'],
            'blogs/vue-composables.svg' => ['Vue.js', '#42b883', '#64d8a4'],
            'blogs/docker-deploy.svg' => ['Docker', '#2496ed', '#56b4f5'],
            'blogs/tailwind-v4.svg' => ['Tailwind', '#06b6d4', '#22d3ee'],
            'blogs/fortify-auth.svg' => ['Fortify', '#ff2d20', '#ff6b5b'],
        ];

        foreach (array_merge($projects, $blogs) as $path => [$label, $color1, $color2]) {
            $svg = $this->makePlaceholderSvg($label, $color1, $color2);
            Storage::disk('public')->put($path, $svg);
        }
    }

    private function makePlaceholderSvg(string $label, string $color1, string $color2): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="400" viewBox="0 0 800 400">
  <defs>
    <linearGradient id="g" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:{$color1};stop-opacity:1" />
      <stop offset="100%" style="stop-color:{$color2};stop-opacity:1" />
    </linearGradient>
  </defs>
  <rect width="800" height="400" fill="url(#g)" rx="0"/>
  <text x="400" y="200" font-family="system-ui,sans-serif" font-size="48" font-weight="600" fill="white" text-anchor="middle" dominant-baseline="central" opacity="0.9">{$label}</text>
  <text x="400" y="250" font-family="system-ui,sans-serif" font-size="16" fill="white" text-anchor="middle" dominant-baseline="central" opacity="0.5">800 × 400</text>
</svg>
SVG;
    }
}
