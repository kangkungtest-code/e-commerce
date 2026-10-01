<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama', 'negara', 'is_active'])]
class ShippingZone extends Model
{
    use HasUuids;

    protected function casts(): array
    {
        return ['negara' => 'array', 'is_active' => 'boolean'];
    }

    public function rates(): HasMany
    {
        return $this->hasMany(ShippingRate::class)->orderBy('berat_min_gram');
    }

    /** Semua kode negara yang bisa dikirimi (dari zona aktif). */
    public static function negaraTersedia(): array
    {
        return static::query()->where('is_active', true)->get()
            ->flatMap(fn (self $z) => $z->negara ?? [])
            ->unique()->sort()->values()->all();
    }

    public static function untukNegara(string $negara): ?self
    {
        return static::query()->where('is_active', true)->get()
            ->first(fn (self $z) => in_array(strtoupper($negara), $z->negara ?? [], true));
    }
}
