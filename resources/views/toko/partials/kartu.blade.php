<a class="kartu" href="{{ $k['url'] }}">
    <span class="kartu-foto">
        @if ($k['foto'])
            <img src="{{ $k['foto'] }}" alt="" loading="lazy" width="400" height="400">
        @else
            <span class="tanpa-foto">{{ __('Photo coming soon') }}</span>
        @endif
    </span>
    <span class="kartu-nama">{{ $k['nama'] }}</span>
    <span class="kartu-harga">
        @if ($k['habis'])
            <span class="habis">{{ __('Sold out') }}</span>
        @elseif ($k['mulai_dari'])
            {{ __('from :price', ['price' => $k['harga']]) }}
        @else
            {{ $k['harga'] }}
        @endif
    </span>
    @if ($k['ringkas_opsi'])
        <span class="kartu-meta">{{ implode(', ', $k['ringkas_opsi']) }}</span>
    @endif
</a>
