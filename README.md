# e-commerce

E-commerce retail single-vendor. Laravel 13 + Filament 5 (panel admin), Sanctum (API `/api/v1`),
Spatie Permission (role/permission), Spatie Translatable (konten id / en / zh-TW).

## Jalan di laptop

```bash
composer install
cp .env.example .env        # sesuaikan DB_* dan ADMIN_PASSWORD
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

- Storefront: http://localhost:8000
- Panel admin: http://localhost:8000/admin (login pakai ADMIN_EMAIL / ADMIN_PASSWORD dari `.env`)

## Branch & deploy

- `claude-dev` → server dev  `http://<IP>:8080` (database `toko_dev`)
- `main`       → server demo `http://<IP>/`      (database `toko_demo`)

Tiap push menjalankan `.github/workflows/deploy.yml`: test dulu (MySQL + `php artisan test`),
lalu deploy kalau lulus. Setup server ada di `.github/workflows/provision.yml`.
