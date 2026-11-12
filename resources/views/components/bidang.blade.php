@props(['label', 'nama', 'wajib' => false, 'petunjuk' => null])

{{-- Pembungkus satu bidang form: label + isian + pesan galat. --}}
<div {{ $attributes->only('class')->merge(['class' => 'space-y-1.5']) }}>
    <label for="{{ $nama }}" class="block text-xs font-semibold text-slate-700">
        {{ $label }}
        @if ($wajib)<span class="text-rose-500" title="Wajib diisi">*</span>@endif
    </label>
    {{ $slot }}
    @if ($petunjuk)
        <p class="text-[11px] text-slate-500">{{ $petunjuk }}</p>
    @endif
    @error($nama)
        <p class="flex items-center gap-1 text-xs font-medium text-rose-600">
            <x-heroicon-s-exclamation-circle class="h-3.5 w-3.5 shrink-0"/>
            <span>{{ $message }}</span>
        </p>
    @enderror
</div>
