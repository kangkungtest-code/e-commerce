{{-- Satu field form: @include('toko.partials.field', ['nama' => 'email', 'label' => __('Email'), 'tipe' => 'email', 'nilai' => ..., 'attr' => 'autocomplete=email']) --}}
@php($bag = $bag ?? 'default')
@php($pesan = $errors->getBag($bag)->first($nama))
<div @class(['field', 'field-galat' => $pesan])>
    <label for="f-{{ $nama }}">{{ $label }}</label>
    @if (($tipe ?? 'text') === 'textarea')
        <textarea id="f-{{ $nama }}" name="{{ $nama }}" rows="3" {!! $attr ?? '' !!} @if ($pesan) aria-invalid="true" aria-describedby="e-{{ $nama }}" @endif>{{ old($nama, $nilai ?? '') }}</textarea>
    @else
        <input id="f-{{ $nama }}" type="{{ $tipe ?? 'text' }}" name="{{ $nama }}" value="{{ ($tipe ?? 'text') === 'password' ? '' : old($nama, $nilai ?? '') }}" {!! $attr ?? '' !!} @if ($pesan) aria-invalid="true" aria-describedby="e-{{ $nama }}" @endif>
    @endif
    @if ($pesan)<p class="field-pesan" id="e-{{ $nama }}">{{ $pesan }}</p>@endif
    @isset($bantuan)<p class="field-bantuan">{{ $bantuan }}</p>@endisset
</div>
