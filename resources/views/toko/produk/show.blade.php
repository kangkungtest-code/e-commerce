@extends('layouts.toko', ['judul' => $p['nama'], 'deskripsi' => \Illuminate\Support\Str::limit($p['deskripsi'] ?? '', 150)])

@section('isi')
    <div class="wrap halaman">
        <nav class="remah" aria-label="breadcrumb">
            <a href="{{ route('produk.index') }}">{{ __('Shop') }}</a>
            @if ($p['kategori'])
                <span aria-hidden="true">/</span>
                <a href="{{ route('produk.index', ['kategori' => $p['kategori']]) }}">{{ __($p['kategori']) }}</a>
            @endif
        </nav>

        <div class="produk">
            <div class="galeri" data-galeri>
                <div class="galeri-utama">
                    @if ($p['foto'])
                        <img src="{{ $p['foto'][0]['url'] }}" alt="{{ $p['nama'] }}" width="1600" height="1600" data-galeri-utama>
                    @else
                        <span class="tanpa-foto">{{ __('Photo coming soon') }}</span>
                    @endif
                </div>
                @if (count($p['foto']) > 1)
                    <ul class="galeri-thumb" aria-label="{{ __('Product photos') }}">
                        @foreach ($p['foto'] as $i => $f)
                            <li>
                                <button type="button" data-foto="{{ $f['url'] }}" @if ($i === 0) aria-current="true" @endif aria-label="{{ __('Show photo :n', ['n' => $i + 1]) }}">
                                    <img src="{{ $f['thumb'] }}" alt="" width="400" height="400" loading="lazy">
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="info" data-pemilih>
                <h1 class="judul-produk">{{ $p['nama'] }}</h1>
                <p class="harga" data-harga>{{ $p['awal']['harga'] ?? '' }}</p>

                @foreach ($p['opsi'] as $o)
                    <fieldset class="opsi">
                        <legend>{{ $o['label'] }}<span class="opsi-terpilih" data-terpilih="{{ $o['kunci'] }}"></span></legend>
                        <div class="opsi-pilihan">
                            @foreach ($o['nilai'] as $n)
                                <label class="chip">
                                    <input type="radio" name="opsi[{{ $o['kunci'] }}]" value="{{ $n['nilai'] }}" data-label="{{ $n['label'] }}"
                                        @checked(($p['awal']['opsi']->{$o['kunci']} ?? null) === $n['nilai'])>
                                    <span>{{ $n['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach

                <p class="stok" data-stok
                   data-t-habis="{{ __('Sold out') }}"
                   data-t-ada="{{ __('In stock') }}"
                   data-t-sisa="{{ __('Only :count left') }}"
                   data-t-tidak-ada="{{ __('This combination is not available') }}"></p>

                <button type="button" class="tombol tombol-lebar" disabled>{{ __('Ordering opens soon') }}</button>
                <p class="catatan">{{ __('Online ordering is being prepared. You can already browse every color and size.') }}</p>

                @if ($p['deskripsi'])
                    <div class="deskripsi">{!! nl2br(e($p['deskripsi'])) !!}</div>
                @endif
                <p class="sku" data-sku>{{ $p['awal']['sku'] ?? '' }}</p>

                <script type="application/json" data-varian>@json($p['varian'])</script>
            </div>
        </div>

        @if ($terkait->isNotEmpty())
            <section class="bagian">
                <div class="bagian-kepala"><h2>{{ __('You might also like') }}</h2></div>
                <div class="grid">
                    @foreach ($terkait as $k)
                        @include('toko.partials.kartu', ['k' => $k])
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
