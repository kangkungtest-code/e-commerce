<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

#[Fillable(['nama_terjemahan', 'deskripsi_terjemahan', 'kategori', 'is_active'])]
class Product extends Model
{
    use HasTranslations, HasUuids;

    /** @var array<int, string> Kolom JSON multi-bahasa (id / en / zh-TW). */
    public array $translatable = ['nama_terjemahan', 'deskripsi_terjemahan'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
