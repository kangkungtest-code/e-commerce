<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Admin dan customer satu tabel. Pembedanya role (Spatie Permission):
 * user tanpa role = customer biasa, user dengan role guard `admin` = staff.
 */
#[Fillable(['nama_lengkap', 'email', 'password', 'bahasa_preferensi', 'mata_uang_preferensi'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasLocalePreference, HasName
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, HasUuids, Notifiable;

    /** Role & permission staff dicek di guard `admin`. */
    protected string $guard_name = 'admin';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** Email & notifikasi dikirim dalam bahasa pilihan pembeli. */
    public function preferredLocale(): string
    {
        return $this->bahasa_preferensi ?: config('app.locale');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->roles()->where('guard_name', 'admin')->exists();
    }

    public function getFilamentName(): string
    {
        return $this->nama_lengkap;
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
