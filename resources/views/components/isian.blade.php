@props(['nama', 'tipe' => 'text'])

<input type="{{ $tipe }}" name="{{ $nama }}" id="{{ $nama }}"
    {{ $attributes->merge([
        'class' => 'block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary '
            . ($errors->has($nama) ? 'border-rose-400' : ''),
    ]) }}>
