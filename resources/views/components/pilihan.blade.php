@props(['nama', 'opsi' => [], 'terpilih' => null, 'kosong' => null])

<select name="{{ $nama }}" id="{{ $nama }}"
    {{ $attributes->merge([
        'class' => 'block w-full cursor-pointer rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-medium text-slate-800 shadow-2xs transition-all hover:border-slate-300 focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/15 '
            . ($errors->has($nama) ? 'border-rose-400 bg-rose-50/30' : ''),
    ]) }}>
    @if ($kosong !== null)
        <option value="">{{ $kosong }}</option>
    @endif
    @foreach ($opsi as $nilai => $label)
        <option value="{{ $nilai }}" @selected((string) old($nama, $terpilih) === (string) $nilai)>{{ $label }}</option>
    @endforeach
    {{ $slot }}
</select>
