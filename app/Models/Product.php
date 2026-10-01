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

    protected static function booted(): void
    {
        // Hapus foto lewat model supaya file di disk ikut terhapus
        // (cascade di database tidak memicu event model).
        static::deleting(function (self $product) {
            $product->images()->get()->each->delete();
        });
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('urutan');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
