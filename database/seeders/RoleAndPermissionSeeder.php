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
        'kurs.kelola',
        'ongkir.kelola',
        'laporan.lihat',
    ];

    public const GUARD = 'admin';

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $name) {
            Permission::findOrCreate($name, self::GUARD);
        }

        // MVP: satu role Owner dengan semua permission.
        Role::findOrCreate('Owner', self::GUARD)->syncPermissions(self::PERMISSIONS);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
