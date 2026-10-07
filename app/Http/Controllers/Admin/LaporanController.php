<?php

namespace App\Http\Controllers\Admin;

use App\Filament\Support\LabelAdmin;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Stock;
use App\Support\Fitur;
use App\Support\Laporan;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Fitur "Laporan lengkap": laporan periode sebagai PDF (dibuka di tab baru; pengguna bisa
 * mengunduh/mencetak dari penampil PDF browser) atau Excel (.xlsx, data mentah untuk diolah).
 * Rute ada di dalam panel admin (sudah login & adalahAdmin), plus fitur & izin laporan.lihat.
 */
class LaporanController extends Controller
{
    private function laporan(Request $request): Laporan
    {
        abort_unless(Fitur::aktif('laporan_lengkap'), 404);
        abort_unless($request->user('admin')?->hasPermissionTo('laporan.lihat'), 403);

        $data = $request->validate([
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:dari'],
        ]);

        return new Laporan($data['dari'] ?? null, $data['sampai'] ?? null);
    }

    private function namaBerkas(Laporan $l, string $ext): string
    {
        $toko = str(config('toko.nama'))->slug()->toString() ?: 'toko';

        return "laporan-{$toko}-{$l->dari->format('Ymd')}-{$l->sampai->format('Ymd')}.{$ext}";
    }

    public function pdf(Request $request): Response
    {
        $l = $this->laporan($request);

        $html = view('admin.laporan-pdf', [
            'toko' => config('toko.nama'),
            'warna' => config('toko.warna_admin') ?: '#d97706',
            'l' => $l,
            'ringkasan' => $l->ringkasan(),
            'harian' => $l->penjualanHarian()->filter(),
            'metode' => $l->perMetodeBayar(),
            'kategori' => $l->penjualanPerKategori(),
            'barang' => $l->barangTerjual()->take(20),
            'status' => collect(LabelAdmin::STATUS_ORDER)->map(fn ($label, $s) => [$label, $l->statusOrder()[$s] ?? 0]),
            'retur' => Fitur::aktif('retur')
                ? collect(LabelAdmin::STATUS_RETUR)->map(fn ($label, $s) => [$label, $l->statusRetur()[$s] ?? 0])
                : null,
            'dibuat' => now(config('toko.zona_waktu')),
        ])->render();

        $opsi = new Options;
        $opsi->setIsRemoteEnabled(false);
        $opsi->setDefaultFont('DejaVu Sans');
        $opsi->setTempDir(storage_path('framework/cache'));
        $opsi->setFontCache(storage_path('framework/cache'));
        $pdf = new Dompdf($opsi);
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('A4');
        $pdf->render();

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$this->namaBerkas($l, 'pdf').'"',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function excel(Request $request): StreamedResponse
    {
        $l = $this->laporan($request);
        $tz = config('toko.zona_waktu');

        return response()->streamDownload(function () use ($l, $tz) {
            $tebal = (new Style)->setFontBold();
            $w = new Writer;
            $w->openToFile('php://output');

            // 1. Ringkasan
            $w->getCurrentSheet()->setName('Ringkasan');
            $r = $l->ringkasan();
            $baris = [
                ['Laporan penjualan', config('toko.nama')],
                ['Periode', $l->dari->format('d/m/Y').' – '.$l->sampai->format('d/m/Y')],
                ['Dibuat', now($tz)->format('d/m/Y H:i')],
                [],
                ['Total penjualan (IDR)', $r['penjualan']],
                ['Jumlah pesanan terbayar', $r['jumlah']],
                ['Rata-rata per pesanan (IDR)', round($r['rata'])],
                [],
            ];
            foreach ($baris as $b) {
                $w->addRow(Row::fromValues($b));
            }
            $w->addRow(Row::fromValues(['Metode pembayaran', 'Penjualan (IDR)'], $tebal));
            foreach ($l->perMetodeBayar() as $m => $n) {
                $w->addRow(Row::fromValues([$m, $n]));
            }
            $w->addRow(Row::fromValues([]));
            $w->addRow(Row::fromValues(['Kategori', 'Penjualan barang (IDR)'], $tebal));
            foreach ($l->penjualanPerKategori() as $k => $n) {
                $w->addRow(Row::fromValues([$k, $n]));
            }

            // 2. Pesanan
            $w->addNewSheetAndMakeItCurrent()->setName('Pesanan');
            $w->addRow(Row::fromValues([
                'Nomor', 'Dibayar', 'Pembeli', 'Email', 'Status', 'Metode bayar', 'Mata uang',
                'Total (mata uang order)', 'Subtotal (IDR)', 'Ongkir (IDR)', 'Total (IDR)', 'Resi',
            ], $tebal));
            foreach ($l->daftarPesanan() as $o) {
                /** @var Order $o */
                $w->addRow(Row::fromValues([
                    $o->nomor,
                    $o->dibayar_pada?->timezone($tz)->format('Y-m-d H:i'),
                    $o->user?->nama_lengkap ?? ($o->alamat_snapshot['nama_penerima'] ?? '-'),
                    $o->user?->email ?? '-',
                    LabelAdmin::STATUS_ORDER[$o->status] ?? $o->status,
                    Laporan::labelMetode($o),
                    $o->mata_uang,
                    (float) $o->total,
                    (float) $o->subtotal_idr,
                    (float) $o->ongkir_idr,
                    (float) $o->total_idr,
                    $o->resi ?? '',
                ]));
            }

            // 3. Barang terjual
            $w->addNewSheetAndMakeItCurrent()->setName('Barang terjual');
            $w->addRow(Row::fromValues(['Produk', 'Varian', 'SKU', 'Qty', 'Omzet (IDR)'], $tebal));
            foreach ($l->barangTerjual() as $b) {
                $w->addRow(Row::fromValues([$b['produk'], $b['varian'], $b['sku'], $b['qty'], $b['omzet']]));
            }

            // 4. Stok saat ini (bukan per periode)
            $w->addNewSheetAndMakeItCurrent()->setName('Stok saat ini');
            $w->addRow(Row::fromValues(['Produk', 'SKU', 'Lokasi', 'Fisik', 'Dipesan (reserved)', 'Tersedia'], $tebal));
            Stock::query()->with(['variant.product', 'location'])->get()
                ->sortBy(fn (Stock $s) => [$s->variant?->product?->getTranslation('nama_terjemahan', 'id'), $s->variant?->sku])
                ->each(fn (Stock $s) => $w->addRow(Row::fromValues([
                    $s->variant?->product?->getTranslation('nama_terjemahan', 'id') ?? 'Produk terhapus',
                    $s->variant?->sku ?? '-',
                    $s->location?->nama ?? '-',
                    (int) $s->jumlah,
                    (int) $s->jumlah_reserved,
                    $s->tersedia(),
                ])));

            $w->close();
        }, $this->namaBerkas($l, 'xlsx'), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    /** Untuk view: tanggal Indonesia singkat. */
    public static function tanggal(Carbon $c): string
    {
        return $c->locale('id')->isoFormat('D MMM YYYY');
    }
}
