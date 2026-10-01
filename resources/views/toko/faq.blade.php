@extends('layouts.toko', ['judul' => __('Frequently asked questions')])

@section('isi')
    <div class="wrap halaman sempit">
        <h1 class="judul-halaman">{{ __('Frequently asked questions') }}</h1>

        @forelse ($faq as $f)
            <details class="faq" @if ($loop->first) open @endif>
                <summary>{{ $f->getTranslation('pertanyaan_terjemahan', app()->getLocale()) }}</summary>
                <p>{{ $f->getTranslation('jawaban_terjemahan', app()->getLocale()) }}</p>
            </details>
        @empty
            <p class="kosong">{{ __('No questions yet.') }}</p>
        @endforelse
    </div>
@endsection
