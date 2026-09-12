# Portofolio

Website portofolio pribadi sekaligus blog, dibangun dengan **Laravel 12**, **Vue 3**, **Inertia.js v2**, dan **Tailwind CSS v4**. Tampilan bertema gelap (navy + aksen biru), satu halaman landing, dilengkapi panel admin.

## Fitur

- **Landing satu halaman** — Hero, Tentang Saya, Hobi, Side Projects, Portofolio, Sertifikasi, Blog, dan Kontak
- **Tema gelap** — Palet navy biru dengan aksen biru
- **Blog** — Halaman artikel dengan konten rich text, tag, dan paginasi
- **Portofolio & Side Projects** — CRUD lengkap dari panel admin
- **Sertifikasi** — Carousel sertifikat
- **Form kontak** — Tersimpan di database dengan notifikasi SweetAlert2
- **SEO** — Open Graph, Twitter Card, dan meta tag yang bisa diatur dari admin
- **Statistik kunjungan** — Pelacakan page view dengan dashboard Chart.js
- **Autentikasi 2FA** — Didukung Laravel Fortify
- **Panel admin** — Kelola proyek, blog, kontak, sertifikasi, pengaturan situs, dan analitik

## Tech Stack

| Lapisan | Teknologi |
|---------|-----------|
| Backend | Laravel 12, PHP 8.2+ |
| Frontend | Vue 3, Inertia.js v2, TypeScript |
| Styling | Tailwind CSS v4 |
| Ikon | Tabler Icons (publik), Lucide (admin) |
| Editor | CKEditor 5 |
| Auth | Laravel Fortify (2FA) |
| Database | MySQL |
| Build | Vite 7 |

## Persyaratan

- PHP >= 8.2 dengan ekstensi umum Laravel
- Composer
- Node.js >= 20 dan npm
- MySQL (atau MariaDB)

## Instalasi Lokal

```bash
# 1. Dependensi
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate
```

Sesuaikan koneksi database pada `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=portofolio
DB_USERNAME=root
DB_PASSWORD=
```

```bash
# 3. Migrasi + data awal
php artisan migrate --seed

# 4. Tautkan storage (untuk gambar unggahan)
php artisan storage:link

# 5. Jalankan
npm run dev
php artisan serve
```

Buka `http://127.0.0.1:8000`.

### Akun Admin Default

| Field | Nilai |
|-------|-------|
| Email | `admin@example.com` |
| Password | `password` |

> **Penting:** segera ganti password dan email ini setelah deploy pertama.

## Struktur Singkat

```
app/
  Http/Controllers/       # IndexController (publik), Admin/* (panel)
  Models/                 # Blog, Project, Certification, Contact, SiteSetting, Visit
resources/
  js/pages/Index.vue      # Halaman publik satu-halaman
  js/pages/admin/         # Halaman admin
  css/app.css             # Token tema Tailwind v4
database/
  migrations/             # Skema tabel
  seeders/                # AdminSeeder, data contoh
routes/
  web.php                 # Rute publik
scripts/
  make-brand-assets.ps1   # Generator favicon & apple-touch-icon
```

## Build Produksi

```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Deploy (Singkat)

1. Clone repo di server, lalu `composer install --no-dev --optimize-autoloader`
2. Buat `.env` produksi (`APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` sesuai domain)
3. `php artisan key:generate`
4. `php artisan migrate --force`
5. `npm ci && npm run build`
6. `php artisan storage:link`
7. Arahkan document root web server ke folder `public/`
8. Jalankan `php artisan config:cache route:cache view:cache`

Ada `Dockerfile` dan `docker-compose.yml` bila ingin deploy berbasis container.

## Lisensi

MIT
