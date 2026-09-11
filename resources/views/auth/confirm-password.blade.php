<x-guest-layout>
    <h1 class="text-lg font-bold text-primary">Konfirmasi Kata Sandi</h1>
    <p class="mt-1 text-sm text-slate-500">
        Ini adalah area yang dilindungi. Masukkan kembali kata sandi Anda untuk melanjutkan.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-5 space-y-4">
        @csrf

        <div class="space-y-1.5">
            <label for="password" class="block text-sm font-semibold text-slate-700">Kata Sandi</label>
            <input id="password" name="password" type="password" required autocomplete="current-password"
                   class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary">
            @error('password')
                <p class="text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="w-full rounded-lg bg-primary px-4 py-3 text-sm font-bold text-white hover:bg-primary-600">
            Konfirmasi
        </button>
    </form>
</x-guest-layout>
