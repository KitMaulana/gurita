@props(['label', 'nama', 'wajib' => false, 'petunjuk' => null])

{{-- Pembungkus satu bidang form: label + isian + pesan galat. --}}
<div {{ $attributes->only('class')->merge(['class' => 'space-y-1.5']) }}>
    <label for="{{ $nama }}" class="block text-xs font-bold uppercase tracking-wider text-slate-600">
        {{ $label }}
        @if ($wajib)<span class="text-rose-500" title="Wajib diisi">*</span>@endif
    </label>
    {{ $slot }}
    @if ($petunjuk)
        <p class="flex items-center gap-1 text-[11px] font-medium text-slate-400">
            <span>💡</span>
            <span>{{ $petunjuk }}</span>
        </p>
    @endif
    @error($nama)
        <p class="flex items-center gap-1 text-xs font-semibold text-rose-600">
            <x-heroicon-s-exclamation-circle class="h-3.5 w-3.5 shrink-0"/>
            <span>{{ $message }}</span>
        </p>
    @enderror
</div>
