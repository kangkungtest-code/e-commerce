@extends('layouts.toko', ['judul' => __('Account')])

@section('isi')
    <div class="wrap halaman">
        <h1 class="judul-halaman">{{ __('Hi, :name', ['name' => \Illuminate\Support\Str::before($user->nama_lengkap, ' ')]) }}</h1>
        @include('toko.akun._nav')

        <div class="grid-akun">
            <section class="blok">
                <div class="blok-kepala">
                    <h2>{{ __('Recent orders') }}</h2>
                    @if ($pesanan->isNotEmpty())<a href="{{ route('akun.pesanan') }}">{{ __('See all') }}</a>@endif
                </div>
                @forelse ($pesanan as $o)
                    <a class="baris-order" href="{{ $o['url'] }}">
                        <span><strong>{{ $o['nomor'] }}</strong><br><span class="redup">{{ $o['tanggal'] }}</span></span>
                        @include('toko.akun._status', ['status' => $o['status'], 'label' => $o['label_status']])
                        <span class="angka">{{ $o['total'] }}</span>
                    </a>
                @empty
                    <p class="redup">{{ __('No orders yet.') }}</p>
                    <a class="tombol tombol-kecil" href="{{ route('produk.index') }}">{{ __('Browse the collection') }}</a>
                @endforelse
            </section>

            <section class="blok">
                <div class="blok-kepala">
                    <h2>{{ __('Addresses') }}</h2>
                    <a href="{{ route('akun.alamat.create') }}">{{ __('Add address') }}</a>
                </div>
                @forelse ($alamat as $a)
                    <div class="kartu-alamat">
                        <span>
                            <strong>{{ $a->label }}</strong>@if ($a->is_default) <span class="lencana">{{ __('Default') }}</span>@endif<br>
                            {{ $a->nama_penerima }}, {{ $a->telepon }}<br>
                            {{ $a->detail_alamat }}, {{ $a->kota }} {{ $a->kode_pos }}, {{ __(config('toko.negara.'.$a->negara, $a->negara)) }}
                        </span>
                        <span class="aksi-alamat">
                            <a href="{{ route('akun.alamat.edit', $a) }}">{{ __('Edit') }}</a>
                            <form method="post" action="{{ route('akun.alamat.destroy', $a) }}" onsubmit="return confirm(@js(__('Delete this address?')))">
                                @csrf @method('delete')
                                <button type="submit" class="tautan">{{ __('Delete') }}</button>
                            </form>
                        </span>
                    </div>
                @empty
                    <p class="redup">{{ __('No saved addresses yet.') }}</p>
                @endforelse
            </section>

            <section class="blok">
                <h2>{{ __('Profile') }}</h2>
                <form method="post" action="{{ route('akun.profil') }}" class="form">
                    @csrf @method('put')
                    @include('toko.partials.field', ['nama' => 'nama_lengkap', 'label' => __('Full name'), 'nilai' => $user->nama_lengkap, 'attr' => 'autocomplete="name" required'])
                    <div class="field">
                        <label>{{ __('Email') }}</label>
                        <p class="nilai-tetap">{{ $user->email }}</p>
                    </div>
                    <div class="dua-field">
                        <div class="field">
                            <label for="f-bahasa">{{ __('Language') }}</label>
                            <select id="f-bahasa" name="bahasa_preferensi">
                                @foreach (config('toko.locales') as $k => $n)
                                    <option value="{{ $k }}" @selected($user->bahasa_preferensi === $k)>{{ $n }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label for="f-mu">{{ __('Currency') }}</label>
                            <select id="f-mu" name="mata_uang_preferensi">
                                @foreach (config('toko.currencies') as $k)
                                    <option value="{{ $k }}" @selected($user->mata_uang_preferensi === $k)>{{ $k }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="tombol tombol-kecil">{{ __('Save profile') }}</button>
                </form>
            </section>

            <section class="blok">
                <h2>{{ __('Password') }}</h2>
                <form method="post" action="{{ route('akun.password') }}" class="form" novalidate>
                    @csrf @method('put')
                    @include('toko.partials.field', ['bag' => 'password', 'nama' => 'password_lama', 'label' => __('Current password'), 'tipe' => 'password', 'attr' => 'autocomplete="current-password"', 'bantuan' => $punyaSosial ? __('Signed up with :provider? Use "Forgot password" on the sign-in page to create one.', ['provider' => ucfirst($punyaSosial[0])]) : null])
                    @include('toko.partials.field', ['bag' => 'password', 'nama' => 'password', 'label' => __('New password'), 'tipe' => 'password', 'attr' => 'autocomplete="new-password"'])
                    @include('toko.partials.field', ['bag' => 'password', 'nama' => 'password_confirmation', 'label' => __('Repeat password'), 'tipe' => 'password', 'attr' => 'autocomplete="new-password"'])
                    <button type="submit" class="tombol tombol-kecil">{{ __('Change password') }}</button>
                </form>
            </section>
        </div>
    </div>
@endsection
