<?php

namespace Tests\Feature;

use App\Actions\Order\BuatOrderAction;
use App\Actions\Order\UbahStatusOrderAction;
use App\Filament\Pages\Dashboard;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use App\Support\Fitur;
use Database\Seeders\RoleAndPermissionSeeder as R;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use OpenSpout\Reader\XLSX\Reader;
use Tests\Feature\Concerns\TokoFixture;
use Tests\TestCase;

class LaporanLengkapTest extends TestCase
{
    use RefreshDatabase, TokoFixture;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->siapkanToko();
        $this->seed(R::class);
        Filament::setCurrentPanel('admin');

        $this->owner = User::factory()->create();
        $this->owner->assignRole(R::OWNER);
    }

    private function orderTerbayar(): Order
    {
        $pembeli = $this->pembeli();
        $alamat = $this->alamat($pembeli);
        $pembeli->cart()->create()->items()->create(['variant_id' => $this->kaosM->id, 'qty' => 2]);
        $order = app(BuatOrderAction::class)->execute($pembeli, $alamat, 'IDR');
        app(UbahStatusOrderAction::class)->execute($order, Order::STATUS_DIBAYAR, $this->owner);

        return $order->fresh();
    }

    public function test_pdf_tampil_inline_di_tab_baru(): void
    {
        $this->orderTerbayar();
        $this->actingAs($this->owner, 'admin');

        $hari = now(config('toko.zona_waktu'))->toDateString();
        $res = $this->get(route('filament.admin.laporan.pdf', ['dari' => $hari, 'sampai' => $hari]));

        $res->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('inline;', $res->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', $res->getContent());

        // Isi view: ringkasan & barang terlaris.
        $html = view('admin.laporan-pdf', [
            'toko' => 'Uji', 'warna' => '#000', 'l' => $l = new \App\Support\Laporan($hari, $hari),
            'ringkasan' => $l->ringkasan(), 'harian' => $l->penjualanHarian()->filter(),
            'metode' => $l->perMetodeBayar(), 'kategori' => $l->penjualanPerKategori(),
            'barang' => $l->barangTerjual(), 'status' => collect(), 'retur' => null, 'dibuat' => now(),
        ])->render();
        $this->assertStringContainsString('Rp220.000', $html);
        $this->assertStringContainsString('Kaos Hitam', $html);
    }

    public function test_excel_berisi_pesanan_barang_dan_stok(): void
    {
        $order = $this->orderTerbayar();
        $this->actingAs($this->owner, 'admin');

        $res = $this->get(route('filament.admin.laporan.excel'));
        $res->assertOk()->assertDownload();
        $this->assertStringEndsWith('.xlsx', $res->headers->get('Content-Disposition'));

        $berkas = tempnam(sys_get_temp_dir(), 'lap').'.xlsx';
        file_put_contents($berkas, $res->streamedContent());
        $reader = new Reader;
        $reader->open($berkas);
        $isi = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $isi[$sheet->getName()][] = $row->toArray();
            }
        }
        $reader->close();
        @unlink($berkas);

        $this->assertSame(['Ringkasan', 'Pesanan', 'Barang terjual', 'Stok saat ini'], array_keys($isi));
        $ringkasan = collect($isi['Ringkasan'])->filter(fn ($r) => count($r) >= 2)->mapWithKeys(fn ($r) => [$r[0] => $r[1]]);
        $this->assertEquals(220000, $ringkasan['Total penjualan (IDR)']);
        $this->assertEquals(1, $ringkasan['Jumlah pesanan terbayar']);
        $this->assertSame($order->nomor, $isi['Pesanan'][1][0]);
        $this->assertEquals(220000, $isi['Pesanan'][1][10]);
        $this->assertSame('Kaos Hitam', $isi['Barang terjual'][1][0]);
        $this->assertEquals(2, $isi['Barang terjual'][1][3]);
        $this->assertGreaterThan(1, count($isi['Stok saat ini']));
    }

    public function test_tombol_dan_akses_mengikuti_fitur_dan_izin(): void
    {
        $this->actingAs($this->owner, 'admin');
        Livewire::test(Dashboard::class)->assertActionVisible('laporanPdf')->assertActionVisible('laporanExcel');

        // Periode tidak valid ditolak.
        $this->get(route('filament.admin.laporan.pdf', ['dari' => '2026-10-05', 'sampai' => '2026-10-01']))->assertSessionHasErrors('sampai');

        // Paket tanpa laporan_lengkap: tombol hilang, URL 404.
        Fitur::terapkan(1, []);
        Livewire::test(Dashboard::class)->assertActionHidden('laporanPdf')->assertActionHidden('laporanExcel');
        $this->get(route('filament.admin.laporan.pdf'))->assertNotFound();
        $this->get(route('filament.admin.laporan.excel'))->assertNotFound();
        Fitur::terapkan(2, []);

        // Staf tanpa izin laporan.lihat: ditolak.
        Role::findOrCreate('Gudang', R::GUARD)->syncPermissions(['stok.edit']);
        $staf = User::factory()->create();
        $staf->assignRole('Gudang');
        $this->flushSession();
        $this->actingAs($staf, 'admin');
        $this->get(route('filament.admin.laporan.excel'))->assertForbidden();

        // Super Admin tidak melihat data toko.
        $super = User::factory()->create();
        $super->assignRole(R::SUPER_ADMIN);
        $this->flushSession();
        $this->actingAs($super, 'admin');
        $this->get(route('filament.admin.laporan.pdf'))->assertForbidden();

        // Belum login → ke halaman masuk.
        auth('admin')->logout();
        $this->flushSession();
        $this->get(route('filament.admin.laporan.pdf'))->assertRedirect();
    }
}
