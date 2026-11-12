@props(['kepala' => null])

{{-- Tabel selalu dibungkus overflow-x-auto agar aman di layar ponsel (§11). --}}
<div class="kartu overflow-hidden border border-slate-200/80 shadow-xs">
    <div class="overflow-x-auto">
        <table {{ $attributes->merge(['class' => 'min-w-full divide-y divide-slate-200/80 text-sm']) }}>
            @if ($kepala)
                <thead class="bg-slate-50/90 text-left text-[11px] font-extrabold uppercase tracking-wider text-slate-500">
                    {{ $kepala }}
                </thead>
            @endif
            <tbody class="divide-y divide-slate-100 bg-white">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>
