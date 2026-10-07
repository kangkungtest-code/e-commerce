@php
    use App\Filament\Support\LabelAdmin;
    use App\Http\Controllers\Admin\LaporanController as LC;
    $rp = fn ($n) => LabelAdmin::rupiah($n);
    $totalKategori = max(1, $kategori->sum());
@endphp
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Laporan penjualan {{ $toko }} {{ LC::tanggal($l->dari) }} – {{ LC::tanggal($l->sampai) }}</title>
<style>
    @page { margin: 28px 34px 40px; }
    body { font-family: "DejaVu Sans", sans-serif; font-size: 10px; color: #1f2937; }
    h1 { font-size: 17px; margin: 0; }
    h2 { font-size: 12px; margin: 18px 0 6px; padding-bottom: 3px; border-bottom: 2px solid {{ $warna }}; }
    .muted { color: #6b7280; }
    .kepala { border-bottom: 1px solid #e5e7eb; padding-bottom: 8px; margin-bottom: 4px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 4px 6px; text-align: left; vertical-align: top; }
    th { background: #f3f4f6; font-weight: bold; }
    tr.garis td { border-bottom: 1px solid #f0f0f0; }
    .kanan { text-align: right; }
    .kartu td { width: 33%; border: 1px solid #e5e7eb; padding: 8px 10px; }
    .kartu .angka { font-size: 15px; font-weight: bold; }
    .dua > tbody > tr > td { width: 50%; padding: 0 8px 0 0; }
    .bar { height: 6px; background: {{ $warna }}; }
    .kosong { color: #9ca3af; font-style: italic; padding: 6px; }
    .kaki { position: fixed; bottom: -24px; left: 0; right: 0; font-size: 8px; color: #9ca3af; }
</style>
</head>
<body>
<div class="kaki">{{ $toko }} · Laporan penjualan · dibuat {{ $dibuat->format('d/m/Y H:i') }} {{ $dibuat->format('T') }}</div>

<div class="kepala">
    <h1>Laporan penjualan — {{ $toko }}</h1>
    <div class="muted">Periode {{ LC::tanggal($l->dari) }} – {{ LC::tanggal($l->sampai) }} · Nilai dalam Rupiah · Penjualan = pesanan yang sudah dibayar, menurut tanggal bayar</div>
</div>

<table class="kartu" style="margin-top:10px">
    <tr>
        <td><div class="muted">Total penjualan</div><div class="angka">{{ $rp($ringkasan['penjualan']) }}</div></td>
        <td><div class="muted">Pesanan terbayar</div><div class="angka">{{ number_format($ringkasan['jumlah'], 0, ',', '.') }}</div></td>
        <td><div class="muted">Rata-rata per pesanan</div><div class="angka">{{ $rp($ringkasan['rata']) }}</div></td>
    </tr>
</table>

<table class="dua"><tr>
<td>
    <h2>Per metode pembayaran</h2>
    <table>
        <tr><th>Metode</th><th class="kanan">Penjualan</th></tr>
        @forelse ($metode as $m => $n)
            <tr class="garis"><td>{{ $m }}</td><td class="kanan">{{ $rp($n) }}</td></tr>
        @empty
            <tr><td colspan="2" class="kosong">Belum ada penjualan</td></tr>
        @endforelse
    </table>
</td>
<td>
    <h2>Status pesanan (dibuat di periode ini)</h2>
    <table>
        <tr><th>Status</th><th class="kanan">Jumlah</th></tr>
        @foreach ($status as [$label, $n])
            <tr class="garis"><td>{{ $label }}</td><td class="kanan">{{ $n }}</td></tr>
        @endforeach
    </table>
</td>
</tr></table>

<h2>Penjualan per kategori</h2>
<table>
    <tr><th>Kategori</th><th style="width:40%"></th><th class="kanan">Penjualan barang</th><th class="kanan">%</th></tr>
    @forelse ($kategori as $k => $n)
        <tr class="garis">
            <td>{{ $k }}</td>
            <td><div class="bar" style="width: {{ round($n / $totalKategori * 100) }}%"></div></td>
            <td class="kanan">{{ $rp($n) }}</td>
            <td class="kanan">{{ round($n / $totalKategori * 100) }}%</td>
        </tr>
    @empty
        <tr><td colspan="4" class="kosong">Belum ada penjualan</td></tr>
    @endforelse
</table>

<h2>Barang terlaris{{ $barang->count() >= 20 ? ' (20 teratas)' : '' }}</h2>
<table>
    <tr><th>#</th><th>Produk</th><th>Varian</th><th>SKU</th><th class="kanan">Qty</th><th class="kanan">Omzet</th></tr>
    @forelse ($barang as $i => $b)
        <tr class="garis">
            <td>{{ $i + 1 }}</td><td>{{ $b['produk'] }}</td><td>{{ $b['varian'] }}</td><td>{{ $b['sku'] }}</td>
            <td class="kanan">{{ $b['qty'] }}</td><td class="kanan">{{ $rp($b['omzet']) }}</td>
        </tr>
    @empty
        <tr><td colspan="6" class="kosong">Belum ada barang terjual</td></tr>
    @endforelse
</table>

<table class="dua"><tr>
<td>
    <h2>Penjualan harian</h2>
    <table>
        <tr><th>Tanggal</th><th class="kanan">Penjualan</th></tr>
        @forelse ($harian as $tgl => $n)
            <tr class="garis"><td>{{ LC::tanggal(\Illuminate\Support\Carbon::parse($tgl)) }}</td><td class="kanan">{{ $rp($n) }}</td></tr>
        @empty
            <tr><td colspan="2" class="kosong">Belum ada penjualan</td></tr>
        @endforelse
    </table>
    @if ($harian->isNotEmpty())<div class="muted" style="margin-top:3px">Hari tanpa penjualan tidak ditampilkan.</div>@endif
</td>
<td>
    @if ($retur !== null)
        <h2>Retur (diajukan di periode ini)</h2>
        <table>
            <tr><th>Status</th><th class="kanan">Jumlah</th></tr>
            @foreach ($retur as [$label, $n])
                <tr class="garis"><td>{{ $label }}</td><td class="kanan">{{ $n }}</td></tr>
            @endforeach
        </table>
    @endif
</td>
</tr></table>
</body>
</html>
