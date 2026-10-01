<?php

namespace Database\Seeders;

use App\Actions\Katalog\BuatVarianAction;
use App\Models\ExchangeRate;
use App\Models\Faq;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingZone;
use App\Support\ProductImageStorage;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Katalog contoh toko baju untuk server dev/demo (data di database/data/katalog-demo.php).
 *
 * Idempotent per produk: produk yang SKU pertamanya sudah ada dilewati, jadi
 * produk buatan admin tidak tersentuh dan seeder aman dijalankan tiap deploy.
 */
class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $data = require database_path('data/katalog-demo.php');

        $dibuat = 0;
        foreach ($data['produk'] as $p) {
            $dibuat += $this->produk($p, $data['warna']) ? 1 : 0;
        }

        foreach ($data['kurs'] as [$asal, $tujuan, $rate, $margin]) {
            ExchangeRate::query()->firstOrCreate(
                ['mata_uang_asal' => $asal, 'mata_uang_tujuan' => $tujuan],
                ['rate' => $rate, 'margin_persen' => $margin, 'sumber' => 'manual', 'berlaku_dari' => now()],
            );
        }

        $this->zonaOngkir();

        if (Faq::query()->doesntExist()) {
            foreach ($data['faq'] as $i => $faq) {
                Faq::create([
                    'pertanyaan_terjemahan' => $faq['q'],
                    'jawaban_terjemahan' => $faq['a'],
                    'urutan' => $i + 1,
                    'is_active' => true,
                ]);
            }
        }

        $this->command?->info("Katalog demo: {$dibuat} produk baru.");
    }

    /** Tarif contoh (perkiraan, bukan tarif kurir sungguhan). */
    private function zonaOngkir(): void
    {
        $zona = [
            ['Indonesia', ['ID'], [[0, 1000, 20000], [1001, 3000, 35000], [3001, 10000, 60000], [10001, 30000, 120000]]],
            ['Taiwan', ['TW'], [[0, 1000, 180000], [1001, 3000, 320000], [3001, 10000, 650000]]],
        ];

        foreach ($zona as [$nama, $negara, $tarif]) {
            if (ShippingZone::query()->where('nama', $nama)->exists()) {
                continue;
            }

            $z = ShippingZone::create(['nama' => $nama, 'negara' => $negara, 'is_active' => true]);
            foreach ($tarif as [$min, $max, $idr]) {
                $z->rates()->create(['berat_min_gram' => $min, 'berat_max_gram' => $max, 'tarif_idr' => $idr]);
            }
        }
    }

    private function produk(array $p, array $palet): bool
    {
        $skuPertama = $this->sku($p['kode'], $p['warna'][0], $p['ukuran'][0]);
        if (ProductVariant::query()->where('sku', $skuPertama)->exists()) {
            return false;
        }

        DB::transaction(function () use ($p, $palet) {
            $product = Product::create([
                'nama_terjemahan' => $p['nama'],
                'deskripsi_terjemahan' => $p['deskripsi'],
                'kategori' => $p['kategori'],
                'is_active' => true,
            ]);

            $buatVarian = app(BuatVarianAction::class);
            foreach ($p['warna'] as $wi => $kodeWarna) {
                foreach ($p['ukuran'] as $ui => $ukuran) {
                    $buatVarian->execute($product, [
                        'sku' => $this->sku($p['kode'], $kodeWarna, $ukuran),
                        'opsi' => ['Warna' => $palet[$kodeWarna][0], 'Ukuran' => $ukuran],
                        // Ukuran besar sedikit lebih mahal, supaya contoh harga bervariasi.
                        'harga_idr' => $p['harga'] + (in_array($ukuran, ['XL', '36'], true) ? 10000 : 0),
                        'berat_gram' => $p['berat'],
                        // Stok contoh yang deterministik: ada yang banyak, sedikit, dan habis.
                        'stok_awal' => (($wi * 7 + $ui * 5 + strlen($p['kode'])) % 6) * 4,
                    ]);
                }

                $product->images()->create([
                    'path' => $this->fotoPlaceholder($p['bentuk'], $palet[$kodeWarna][3]),
                    'urutan' => $wi + 1,
                ]);
            }
        });

        return true;
    }

    private function sku(string $kode, string $warna, string $ukuran): string
    {
        return strtoupper($kode.'-'.$warna.'-'.str_replace(' ', '', $ukuran));
    }

    /** Gambar siluet pakaian sederhana di atas latar polos, disimpan lewat jalur upload biasa. */
    private function fotoPlaceholder(string $bentuk, string $hex): string
    {
        $w = 1200;
        $img = imagecreatetruecolor($w, $w);
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');

        // Latar sedikit lebih gelap untuk warna terang (putih/krem) supaya siluet tetap terlihat.
        $terang = (0.299 * $r + 0.587 * $g + 0.114 * $b) > 200;
        imagefill($img, 0, 0, $terang ? imagecolorallocate($img, 214, 209, 199) : imagecolorallocate($img, 244, 242, 238));

        $warna = imagecolorallocate($img, $r, $g, $b);
        $garis = imagecolorallocatealpha($img, 0, 0, 0, 100);

        $titik = match ($bentuk) {
            'tee' => [300, 260, 480, 220, 540, 270, 660, 270, 720, 220, 900, 260, 980, 470, 860, 520, 820, 430, 820, 980, 380, 980, 380, 430, 340, 520, 220, 470],
            'shirt' => [330, 250, 520, 210, 600, 300, 680, 210, 870, 250, 950, 900, 850, 920, 820, 480, 820, 990, 380, 990, 380, 480, 350, 920, 250, 900],
            'jacket' => [300, 250, 500, 200, 600, 260, 700, 200, 900, 250, 990, 920, 880, 940, 830, 500, 830, 1000, 370, 1000, 370, 500, 320, 940, 210, 920],
            'pants' => [400, 200, 800, 200, 840, 1000, 660, 1000, 600, 470, 540, 1000, 360, 1000],
            'dress' => [480, 200, 720, 200, 760, 450, 950, 1000, 250, 1000, 440, 450],
            'skirt' => [420, 330, 780, 330, 960, 920, 240, 920],
            'hat' => [330, 650, 380, 450, 480, 360, 600, 330, 720, 360, 820, 450, 870, 650, 1000, 700, 980, 740, 330, 700],
            'bag' => [330, 420, 870, 420, 900, 1000, 300, 1000],
            'socks' => [460, 200, 640, 200, 640, 760, 820, 820, 860, 960, 640, 960, 460, 860],
            default => [300, 300, 900, 300, 900, 900, 300, 900],
        };

        imagefilledpolygon($img, $titik, $warna);
        imagesetthickness($img, 4);
        imagepolygon($img, $titik, $garis);
        if ($bentuk === 'bag') {
            imagesetthickness($img, 28);
            imagearc($img, 600, 420, 360, 360, 180, 360, $warna);
        }

        $tmp = tempnam(sys_get_temp_dir(), 'demo').'.png';
        imagepng($img, $tmp);

        try {
            return app(ProductImageStorage::class)->store(new UploadedFile($tmp, 'demo.png', 'image/png', null, true));
        } finally {
            @unlink($tmp);
        }
    }
}
