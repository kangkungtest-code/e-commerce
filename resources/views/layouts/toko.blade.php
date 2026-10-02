<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($judul) ? $judul.' | ' : '' }}{{ config('toko.nama') }}</title>
    @isset($deskripsi)<meta name="description" content="{{ $deskripsi }}">@endisset
    <link rel="preload" href="{{ asset('fonts/bricolage/bricolage-grotesque-latin-standard-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/toko.css') }}?v={{ filemtime(public_path('css/toko.css')) }}">
    <script src="{{ asset('js/toko.js') }}?v={{ filemtime(public_path('js/toko.js')) }}" defer></script>
</head>
<body>
    <a class="skip" href="#isi">{{ __('Skip to content') }}</a>

    <header class="topbar">
        <div class="wrap topbar-inner">
            <a class="wordmark" href="{{ route('home') }}">{{ config('toko.nama') }}</a>

            <nav class="nav" aria-label="{{ __('Main navigation') }}">
                <a href="{{ route('produk.index') }}" @class(['aktif' => request()->routeIs('produk.*')])>{{ __('Shop') }}</a>
                <a href="{{ route('faq') }}" @class(['aktif' => request()->routeIs('faq')])>{{ __('FAQ') }}</a>
            </nav>

            <div class="akun-nav">
                @if ($pembeli)
                    <a href="{{ route('akun') }}" @class(['aktif' => request()->routeIs('akun*')])>{{ __('Account') }}</a>
                @else
                    <a href="{{ route('login') }}" @class(['aktif' => request()->routeIs('login', 'daftar')])>{{ __('Sign in') }}</a>
                @endif
                <a href="{{ route('keranjang') }}" class="keranjang-link" @if (request()->routeIs('keranjang')) aria-current="page" @endif>
                    {{ __('Cart') }}<span class="jumlah" aria-label="{{ trans_choice('{0} empty|{1} :count item|[2,*] :count items', $jumlahKeranjang) }}">{{ $jumlahKeranjang }}</span>
                </a>
            </div>

            <form class="prefs" method="post" action="{{ route('preferensi') }}" data-auto-submit>
                @csrf
                <label>
                    <span class="sr-only">{{ __('Language') }}</span>
                    <select name="locale">
                        @foreach (config('toko.locales') as $kode => $nama)
                            <option value="{{ $kode }}" @selected(app()->getLocale() === $kode)>{{ $nama }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span class="sr-only">{{ __('Currency') }}</span>
                    <select name="currency">
                        @foreach (config('toko.currencies') as $kode)
                            <option value="{{ $kode }}" @selected(\App\Support\TampilanProduk::mataUang() === $kode)>{{ $kode }}</option>
                        @endforeach
                    </select>
                </label>
                <noscript><button type="submit">{{ __('Apply') }}</button></noscript>
            </form>
        </div>
    </header>

    <main id="isi">
        @include('toko.partials.pesan')
        @yield('isi')
    </main>

    <div class="chat" data-chat data-url="{{ route('chatbot') }}" data-t-galat="{{ __('Sorry, something went wrong. Please try again.') }}">
        <button type="button" class="chat-buka" aria-expanded="false" aria-controls="chat-panel" data-chat-buka>{{ __('Ask us') }}</button>
        <section class="chat-panel" id="chat-panel" role="dialog" aria-modal="false" aria-label="{{ __('Ask us') }}" hidden>
            <header class="chat-kepala">
                <p class="chat-judul">{{ __('Ask us') }}</p>
                <button type="button" class="tautan" data-chat-tutup aria-label="{{ __('Close') }}">{{ __('Close') }}</button>
            </header>
            <div class="chat-isi" data-chat-isi aria-live="polite"></div>
            <div class="chat-saran" data-chat-saran></div>
            <form class="chat-form" data-chat-form>
                <label class="sr-only" for="chat-pesan">{{ __('Type your question') }}</label>
                <input id="chat-pesan" name="pesan" type="text" maxlength="500" autocomplete="off" placeholder="{{ __('Type your question') }}" required>
                <button type="submit" class="tombol tombol-kecil">{{ __('Send') }}</button>
            </form>
            <p class="chat-catatan">{{ __('Automated answers. For anything else, chat with our team.') }}</p>
        </section>
    </div>

    <footer class="footer">
        <div class="wrap footer-inner">
            <p class="wordmark wordmark-kecil">{{ config('toko.nama') }}</p>
            <p>{{ __('Prices shown in :currency. Converted from rupiah at the store\'s daily rate.', ['currency' => \App\Support\TampilanProduk::mataUang()]) }}</p>
            <p class="footer-tautan">
                <a href="{{ route('faq') }}">{{ __('FAQ') }}</a>
                @foreach (\App\Models\HalamanKebijakan::tautan() as $kb)
                    <a href="{{ $kb['url'] }}">{{ $kb['judul'] }}</a>
                @endforeach
                @foreach (\App\Support\KontakAdmin::tautan() as $k)
                    <a href="{{ $k['url'] }}" target="_blank" rel="noopener">{{ $k['jenis'] === 'wa' ? 'WhatsApp' : 'LINE' }}</a>
                @endforeach
            </p>
        </div>
    </footer>
</body>
</html>
