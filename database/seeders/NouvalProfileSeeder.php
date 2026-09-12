<?php

namespace Database\Seeders;

use App\Models\Certification;
use App\Models\Experience;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Data profil Nouval B. Saputra.
 * Jalankan: php artisan db:seed --class=NouvalProfileSeeder
 */
class NouvalProfileSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedIdentity();
        $this->seedExperiences();
        $this->seedCertifications();

        // Sembunyikan seksi yang belum ada isinya
        SiteSetting::set('section_projects_visible', '0');   // side projects
        SiteSetting::set('section_portfolio_visible', '0');
        SiteSetting::set('section_blog_visible', '0');
    }

    private function seedIdentity(): void
    {
        SiteSetting::set('hero_badge', 'Fresh Graduate S.Kom. — Teknik Informatika');
        SiteSetting::set('hero_title', 'Nouval B. Saputra');
        SiteSetting::set('hero_subtitle', 'Fresh Graduate Sarjana Komputer (S.Kom.) Teknik Informatika yang berfokus pada Web Development dan System Analysis. Membangun aplikasi web modern yang terstruktur, cepat, dan mudah digunakan.');
        SiteSetting::set('seo_title', 'Nouval B. Saputra — Web Developer & System Analyst');
        SiteSetting::set('seo_description', 'Portofolio Nouval B. Saputra, Fresh Graduate S.Kom. Teknik Informatika Universitas 17 Agustus 1945 Surabaya. Fokus pada Web Development (Laravel, Inertia.js, React TypeScript) dan System Analysis.');

        SiteSetting::set('about', implode("\n\n", [
            'Saya Nouval B. Saputra, Fresh Graduate Sarjana Komputer (S.Kom.) Teknik Informatika dari Universitas 17 Agustus 1945 Surabaya dengan fokus keahlian pada Web Development dan System Analysis. Memiliki ketertarikan mendalam dalam memecahkan masalah operasional dan kebutuhan bisnis melalui rekayasa aplikasi web modern yang terstruktur, cepat, dan mudah digunakan.',
            'Memiliki pengalaman praktis dalam merancang serta membangun aplikasi web berbasis Full-Stack (Laravel, Inertia.js, React TypeScript) dan arsitektur basis data relasional. Memiliki rekam jejak kontribusi nyata melalui program MSIB di LLDIKTI Wilayah VII, berperan aktif dalam digitalisasi alur perizinan dan akreditasi perguruan tinggi se-Jawa Timur mulai dari tahap elisitasi kebutuhan pengguna hingga rilis sistem.',
            'Terbiasa bekerja secara mandiri maupun kolaboratif dalam tim, adaptif terhadap teknologi baru, dan siap memberikan kontribusi optimal dalam pengembangan produk perangkat lunak.',
        ]));

        SiteSetting::set('hobbies', implode("\n", [
            '💻 Web Development — Mengembangkan aplikasi web full-stack dengan Laravel, Inertia.js, dan React TypeScript.',
            '🧩 System Analysis — Menganalisis kebutuhan bisnis dan menerjemahkannya menjadi alur sistem yang efisien.',
            '🎨 Desain UI — Merancang antarmuka dengan Figma, Canva, dan Corel Draw.',
            '🎬 Multimedia — Mengolah foto dan video dengan Photoshop serta Adobe Premiere.',
            '📚 Belajar Teknologi Baru — Menjajal framework dan tools terbaru untuk memperluas wawasan.',
        ]));

        SiteSetting::set('contact_email', 'novalbahri17@gmail.com');
        SiteSetting::set('contact_phone', '085748795707');
        SiteSetting::set('contact_address', 'Ds. Tawangsari, Taman, Sidoarjo');
    }

    private function seedExperiences(): void
    {
        $items = [
            [
                'type' => 'education',
                'title' => 'S1 Teknik Informatika (S.Kom.)',
                'institution' => 'Universitas 17 Agustus 1945 Surabaya',
                'location' => 'Surabaya',
                'start_date' => '2020-09-01',
                'end_date' => '2025-09-01',
                'is_current' => false,
                'sort_order' => 1,
                'description' => 'Menempuh studi Teknik Informatika dengan fokus pada Web Development dan System Analysis. Aktif mengikuti program MSIB (Magang dan Studi Independen Bersertifikat) Angkatan 7 di LLDIKTI Wilayah VII Jawa Timur.',
            ],
            [
                'type' => 'work',
                'title' => 'Web Developer',
                'institution' => 'MBA Laundry Express',
                'location' => 'Sidoarjo',
                'start_date' => '2026-02-01',
                'end_date' => null,
                'is_current' => true,
                'sort_order' => 1,
                'description' => "Membuat website profil usaha dan panel admin dari awal menggunakan React, TypeScript, dan Tailwind CSS.\nMembuat menu admin untuk mempermudah staf memperbarui daftar layanan, harga promo, foto galeri, dan artikel informasi secara langsung tanpa perlu ubah kode program.\nMengatur pemasangan (deployment) website di server VPS Linux menggunakan Docker agar website dapat diakses pelanggan setiap saat dengan stabil.",
            ],
            [
                'type' => 'work',
                'title' => 'System Analyst Intern (Program MSIB Angkatan 7)',
                'institution' => 'LLDIKTI Wilayah VII Jawa Timur',
                'location' => 'Surabaya',
                'start_date' => '2024-09-01',
                'end_date' => '2025-12-31',
                'is_current' => false,
                'sort_order' => 2,
                'description' => "Membangun website aplikasi menggunakan Laravel 11, Inertia.js, dan React TypeScript, menghasilkan website yang responsif, cepat, dan berpindah halaman tanpa perlu loading ulang yang berat.\nMembuat alur proses verifikasi akreditasi bertahap, mulai dari pengajuan berkas oleh kampus, pemeriksaan dokumen oleh petugas, penilaian berkas, sampai surat rekomendasi diterbitkan.\nMenerapkan sistem pemeriksaan data dua tahap sebelum disimpan ke database MySQL agar data berkas kampus tidak salah atau rusak.\nMengembangkan fitur penyimpanan dokumen legal (SK Kumham, SK Pendirian, Akta Notaris, SKBP) yang terhubung langsung ke Google Drive Storage dan bisa dilihat langsung dalam format PDF di website.",
            ],
            [
                'type' => 'work',
                'title' => 'HR Manager',
                'institution' => 'MBA Laundry Express',
                'location' => 'Sidoarjo',
                'start_date' => '2022-12-01',
                'end_date' => null,
                'is_current' => true,
                'sort_order' => 3,
                'description' => "Menyusun dan menstandardisasi Standard Operating Procedures (SOP) operasional mesin cuci/pengering komersial, tata kelola inventaris, dan alur penanganan komplain pelanggan.\nMenjalankan program pelatihan digitalisasi kasir (Point of Sale) dan pembukuan transaksi digital bagi staf operasional, meminimalisasi selisih pencatatan keuangan harian hingga di bawah 1%.\nMengelola rekrutmen, orientasi staf baru, dan evaluasi kinerja harian tim operasional guna menjaga standar kebersihan serta kepuasan pelanggan.\nMenganalisis laporan transaksi kasir berkala untuk mengidentifikasi efisiensi stok bahan baku dan mendukung penyusunan promo bulanan.",
            ],
            [
                'type' => 'work',
                'title' => 'Admin E-Commerce',
                'institution' => 'GopalShop',
                'location' => 'Sidoarjo',
                'start_date' => '2021-09-01',
                'end_date' => '2023-08-31',
                'is_current' => false,
                'sort_order' => 4,
                'description' => "Mencatat dan memperbarui daftar akun game yang dijual (dari akun biasa hingga akun langka/winrate tinggi), serta menyesuaikan harga jual mengikuti harga pasar.\nMembuat materi promosi foto tangkapan layar, cuplikan video game, dan bukti kepuasan pembeli (testimoni) di Instagram, Facebook, dan WhatsApp untuk menarik calon pembeli.\nMelayani tawar-menawar harga dengan pembeli, memeriksa bukti transfer bank atau e-wallet, dan menyerahkan data akun secara aman agar terhindar dari penipuan.\nMenjawab pertanyaan calon pembeli lewat chat dengan ramah, memberikan saran akun yang sesuai budget, dan membantu menyelesaikan kendala pembeli setelah transaksi.",
            ],
        ];

        foreach ($items as $item) {
            Experience::updateOrCreate(
                ['title' => $item['title'], 'institution' => $item['institution']],
                $item + ['published' => true]
            );
        }
    }

    private function seedCertifications(): void
    {
        Storage::disk('public')->makeDirectory('certifications');

        $svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="560" viewBox="0 0 800 560">
  <defs>
    <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:#1e3a8a;stop-opacity:1" />
      <stop offset="100%" style="stop-color:#2563eb;stop-opacity:1" />
    </linearGradient>
  </defs>
  <rect width="800" height="560" fill="url(#bg)"/>
  <rect x="40" y="40" width="720" height="480" fill="none" stroke="#93c5fd" stroke-width="3" opacity="0.7"/>
  <text x="400" y="180" font-family="system-ui,sans-serif" font-size="34" font-weight="700" fill="#ffffff" text-anchor="middle">SERTIFIKAT KOMPETENSI</text>
  <text x="400" y="240" font-family="system-ui,sans-serif" font-size="20" fill="#bfdbfe" text-anchor="middle">Junior Web Programmer</text>
  <text x="400" y="320" font-family="system-ui,sans-serif" font-size="26" font-weight="600" fill="#ffffff" text-anchor="middle">Badan Nasional Sertifikasi Profesi (BNSP)</text>
  <text x="400" y="360" font-family="system-ui,sans-serif" font-size="18" fill="#bfdbfe" text-anchor="middle">LSP UNTAG Surabaya</text>
  <text x="400" y="440" font-family="system-ui,sans-serif" font-size="16" fill="#ffffff" text-anchor="middle" opacity="0.85">Masa Berlaku: Januari 2026 - Januari 2029</text>
</svg>
SVG;

        Storage::disk('public')->put('certifications/junior-web-programmer.svg', $svg);

        Certification::updateOrCreate(
            ['title' => 'Junior Web Programmer'],
            [
                'description' => 'Sertifikasi kompetensi Junior Web Programmer dari Badan Nasional Sertifikasi Profesi (BNSP) melalui LSP UNTAG Surabaya. Masa berlaku: Januari 2026 – Januari 2029.',
                'image' => 'certifications/junior-web-programmer.svg',
                'published' => true,
                'sort_order' => 1,
            ]
        );

        SiteSetting::set('section_certifications_visible', '1');
    }
}
