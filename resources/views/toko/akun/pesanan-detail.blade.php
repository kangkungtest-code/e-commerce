@extends('layouts.toko', ['judul' => $o['nomor']])

@section('isi')
    <div class="wrap halaman">
        <nav class="remah"><a href="{{ route('akun.pesanan') }}">{{ __('Orders') }}</a></nav>
        <div class="judul-order">
            <h1 class="judul-halaman">{{ $o['nomor'] }}</h1>
            @include('toko.akun._status', ['status' => $o['status'], 'label' => $o['label_status']])
        </div>
        <p class="redup">{{ __('Placed on :date', ['date' => $o['tanggal']]) }}</p>

        <div class="dua-kolom">
            <div>
                @if ($o['menunggu'])
                    <section class="blok blok-sorot">
                        <h2>{{ __('Pay :total by :deadline', ['total' => $o['total'], 'deadline' => $o['batas_bayar']]) }}</h2>
                        <p>{{ __('Your items are held until then. If the order isn\'t paid in time it is cancelled automatically.') }}</p>
                        <button type="button" class="tombol" disabled>{{ __('Online payment opens soon') }}</button>
                    </section>
                @endif

                <section class="blok">
                    <h2>{{ __('Items') }}</h2>
                    <ul class="daftar-barang">
                        @foreach ($o['items'] as $i)
                            <li class="barang">
                                <span class="barang-foto">@if ($i['foto'])<img src="{{ $i['foto'] }}" alt="" width="400" height="400" loading="lazy">@endif</span>
                                <div class="barang-info">
                                    <span class="barang-nama">{{ $i['nama'] }}</span>
                                    @if ($i['opsi'])<span class="barang-opsi">{{ $i['opsi'] }}</span>@endif
                                    <span class="barang-harga">{{ $i['harga'] }}</span>
                                </div>
                                <div class="barang-aksi">
                                    <span class="barang-qty">× {{ $i['qty'] }}</span>
                                    <span class="barang-total">{{ $i['total'] }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section class="blok">
                    <h2>{{ __('Ship to') }}</h2>
                    <p>
                        <strong>{{ $o['alamat']['nama_penerima'] ?? '' }}</strong>, {{ $o['alamat']['telepon'] ?? '' }}<br>
                        {{ $o['alamat']['detail_alamat'] ?? '' }}<br>
                        {{ $o['alamat']['kota'] ?? '' }} {{ $o['alamat']['kode_pos'] ?? '' }}, {{ $o['nama_negara'] }}
                    </p>
                    @if ($o['resi'])<p>{{ __('Tracking number') }}: <strong>{{ $o['resi'] }}</strong></p>@endif
                </section>
            </div>

            <aside class="ringkasan">
                <dl>
                    <div><dt>{{ __('Subtotal') }}</dt><dd>{{ $o['subtotal'] }}</dd></div>
                    <div><dt>{{ __('Shipping (:kg kg)', ['kg' => $o['berat_kg']]) }}</dt><dd>{{ $o['ongkir'] }}</dd></div>
                    <div class="ringkasan-total"><dt>{{ __('Total') }}</dt><dd>{{ $o['total'] }}</dd></div>
                </dl>
                <p class="catatan">{{ __('Prices locked in :currency when the order was placed.', ['currency' => $o['mata_uang']]) }}</p>

                @if ($o['menunggu'])
                    <form method="post" action="{{ route('akun.pesanan.batal', $o['nomor']) }}" onsubmit="return confirm(@js(__('Cancel this order? The items go back on sale.')))">
                        @csrf
                        <button type="submit" class="tombol tombol-garis tombol-lebar">{{ __('Cancel order') }}</button>
                    </form>
                @endif
            </aside>
        </div>
    </div>
@endsection
