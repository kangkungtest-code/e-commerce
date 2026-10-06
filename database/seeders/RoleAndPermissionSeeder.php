<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /** Semua permission staff, format `modul.aksi`. */
    public const PERMISSIONS = [
        'produk.kelola',
        'stok.edit',
        'order.lihat',
        'order.ubah_status',
        'retur.kelola',
        'faq.kelola',
        'kebijakan.kelola',
        'pengaturan.kelola',
        'pembayaran.kelola',
        'kurs.kelola',
        'ongkir.kelola',
        'laporan.lihat',
    ];

    public const GUARD = 'admin';

    /** Peran pemilik platform (Frendi). Hanya peran ini yang boleh mengatur fitur & paket. */
    public const SUPER_ADMIN = 'Super Admin';

    /** Izin yang hanya dimiliki Super Admin, tidak pernah diberikan ke peran client. */
    public const IZIN_SUPER = ['fitur.kelola'];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([...self::PERMISSIONS, ...self::IZIN_SUPER] as $name) {
            Permission::findOrCreate($name, self::GUARD);
        }

        // Owner (client) = semua izin toko.
        Role::findOrCreate('Owner', self::GUARD)->syncPermissions(self::PERMISSIONS);
        // Super Admin sengaja TIDAK punya izin toko (produk, pesanan, laporan, dll.): hanya mengatur platform.
        Role::findOrCreate(self::SUPER_ADMIN, self::GUARD)->syncPermissions(self::IZIN_SUPER);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
