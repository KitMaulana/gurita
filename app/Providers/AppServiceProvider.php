<?php

namespace App\Providers;

use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Observers\TahunAjaranObserver;
use App\Policies\KelasPolicy;
use App\Services\PeringatanService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        TahunAjaran::observe(TahunAjaranObserver::class);

        // Nama policy Kelas tidak mengikuti konvensi jamak Laravel, jadi didaftarkan manual.
        Gate::policy(Kelas::class, KelasPolicy::class);

        // Wali kelas boleh melihat rekap lengkap kelas perwaliannya (§10).
        Gate::define('lihat-rekap-kelas', function (User $user, Kelas $kelas) {
            return $user->isAdmin() || $kelas->wali_kelas_id === $user->id;
        });

        // Data yang dipakai sidebar & topbar di setiap halaman.
        View::composer('layouts.app', function ($view) {
            $pengguna = auth()->user();

            $view->with('tahunAjaranAktif', TahunAjaran::aktif());

            // Lonceng notifikasi: di-cache 5 menit agar tidak query di tiap halaman.
            $view->with('peringatanGlobal', $pengguna
                ? Cache::remember(
                    "peringatan.{$pengguna->id}",
                    now()->addMinutes(5),
                    fn () => app(PeringatanService::class)->untuk($pengguna)
                )
                : collect());
        });
    }
}
