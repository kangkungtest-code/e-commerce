<?php

namespace Tests\Feature;

use App\Models\ExchangeRate;
use App\Models\Faq;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\StockHistory;
use Database\Seeders\DemoCatalogSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\StockLocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DemoCatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_katalog_demo_terisi_dan_idempotent(): void
    {
        Storage::fake('public');
        $this->seed([RoleAndPermissionSeeder::class, StockLocationSeeder::class]);

        $this->seed(DemoCatalogSeeder::class);

        $jumlahProduk = count((require database_path('data/katalog-demo.php'))['produk']);
        $this->assertSame($jumlahProduk, Product::count());
        $this->assertGreaterThan(50, ProductVariant::count());
        $this->assertSame(2, ExchangeRate::count());
        $this->assertGreaterThan(0, Faq::count());

        $kaos = ProductVariant::where('sku', 'KAOS-BASIC-HITAM-S')->firstOrFail();
        $this->assertSame('Basic Combed Cotton Tee', $kaos->product->getTranslation('nama_terjemahan', 'en'));
        $this->assertSame('精梳棉基本款T恤', $kaos->product->getTranslation('nama_terjemahan', 'zh_TW'));
        $this->assertEquals(['Warna' => 'Hitam', 'Ukuran' => 'S'], $kaos->opsi);

        $foto = ProductImage::firstOrFail();
        Storage::disk('public')->assertExists($foto->path);

        // Stok awal tercatat sebagai riwayat restock.
        $this->assertGreaterThan(0, StockHistory::where('alasan', 'restock')->count());

        // Jalankan ulang: tidak ada duplikat.
        $this->seed(DemoCatalogSeeder::class);
        $this->assertSame($jumlahProduk, Product::count());
        $this->assertSame(2, ExchangeRate::count());
    }
}
