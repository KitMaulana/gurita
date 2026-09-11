@props(['label', 'nama', 'wajib' => false, 'petunjuk' => null])

{{-- Pembungkus satu bidang form: label + isian + pesan galat. --}}
<div {{ $attributes->only('class')->merge(['class' => 'space-y-1.5']) }}>
    <label for="{{ $nama }}" class="block text-sm font-semibold text-slate-700">
        {{ $label }}
        @if ($wajib)<span class="text-rose-500">*</span>@endif
    </label>
    {{ $slot }}
    @if ($petunjuk)
        <p class="text-xs text-slate-500">{{ $petunjuk }}</p>
    @endif
    @error($nama)
        <p class="text-xs font-medium text-rose-600">{{ $message }}</p>
    @enderror
</div>
