@props(['nama', 'opsi' => [], 'terpilih' => null, 'kosong' => null])

<select name="{{ $nama }}" id="{{ $nama }}"
    {{ $attributes->merge([
        'class' => 'block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary '
            . ($errors->has($nama) ? 'border-rose-400' : ''),
    ]) }}>
    @if ($kosong !== null)
        <option value="">{{ $kosong }}</option>
    @endif
    @foreach ($opsi as $nilai => $label)
        <option value="{{ $nilai }}" @selected((string) old($nama, $terpilih) === (string) $nilai)>{{ $label }}</option>
    @endforeach
    {{ $slot }}
</select>
