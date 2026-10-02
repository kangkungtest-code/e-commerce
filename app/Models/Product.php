<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
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

    /**
     * Cari di nama semua bahasa, tidak peka huruf besar/kecil.
     * (Kolom JSON di MySQL dibandingkan secara biner, jadi perlu CAST + LOWER.)
     */
    public static function cariNama(Builder $query, string $kata): Builder
    {
        $kata = '%'.addcslashes(mb_strtolower($kata), '%_\\').'%';

        return $query->whereRaw('LOWER(CAST(nama_terjemahan AS CHAR)) LIKE ?', [$kata]);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('urutan');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /** Nama opsi varian yang dipakai untuk mengaitkan foto ke warna. */
    public const OPSI_WARNA = 'Warna';

    /** Nilai warna yang dipakai varian produk ini, urut sesuai kemunculan. */
    public function daftarWarna(): array
    {
        return $this->variants
            ->map(fn (ProductVariant $v) => $v->opsi[self::OPSI_WARNA] ?? null)
            ->filter()->unique()->values()->all();
    }

    /** Foto untuk warna tertentu; kalau tidak ada, foto pertama produk. */
    public function fotoUntuk(?string $warna): ?ProductImage
    {
        return ($warna ? $this->images->firstWhere('warna', $warna) : null) ?? $this->images->first();
    }
}
