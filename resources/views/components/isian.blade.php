@props(['nama', 'tipe' => 'text'])

<input type="{{ $tipe }}" name="{{ $nama }}" id="{{ $nama }}"
    {{ $attributes->merge([
        'class' => 'block w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-medium text-slate-800 shadow-2xs transition-all placeholder:font-normal placeholder:text-slate-400 hover:border-slate-300 focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/15 '
            . ($errors->has($nama) ? 'border-rose-400 bg-rose-50/30 focus:border-rose-500 focus:ring-rose-500/15' : ''),
    ]) }}>
