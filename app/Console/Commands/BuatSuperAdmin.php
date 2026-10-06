<?php

namespace App\Console\Commands;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Console\Command;

/**
 * Buat / perbarui akun Super Admin (pemilik platform) di instalasi ini.
 * Password diambil dari env SUPERADMIN_PASSWORD supaya tidak tercatat di riwayat shell.
 *
 *   SUPERADMIN_PASSWORD=rahasia php artisan toko:super-admin frendi@contoh.com
 */
class BuatSuperAdmin extends Command
{
    protected $signature = 'toko:super-admin {email : Email akun Super Admin}';

    protected $description = 'Buat atau perbarui akun Super Admin (pengatur fitur & paket)';

    public function handle(): int
    {
        $email = mb_strtolower(trim((string) $this->argument('email')));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Email tidak valid.');

            return self::FAILURE;
        }

        $this->callSilently('db:seed', ['--class' => RoleAndPermissionSeeder::class, '--force' => true]);

        $password = (string) env('SUPERADMIN_PASSWORD', '');
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            if (mb_strlen($password) < 8) {
                $this->error('Akun belum ada: isi SUPERADMIN_PASSWORD (minimal 8 karakter).');

                return self::FAILURE;
            }
            $user = User::create([
                'nama_lengkap' => 'Super Admin',
                'email' => $email,
                'password' => $password,
                'bahasa_preferensi' => 'id',
                'mata_uang_preferensi' => 'IDR',
            ]);
            $user->forceFill(['email_verified_at' => now()])->save();
            $this->info("Akun {$email} dibuat.");
        } elseif (mb_strlen($password) >= 8) {
            $user->forceFill(['password' => $password])->save();
            $this->info("Password {$email} diperbarui.");
        }

        if (! $user->hasRole(RoleAndPermissionSeeder::SUPER_ADMIN)) {
            $user->assignRole(RoleAndPermissionSeeder::SUPER_ADMIN);
        }
        $this->info("{$email} sekarang Super Admin.");

        return self::SUCCESS;
    }
}
