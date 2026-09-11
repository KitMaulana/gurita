<?php

namespace App\Http\Middleware;

use App\Models\TahunAjaran;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hampir seluruh modul bergantung pada tahun ajaran aktif.
 * Bila belum ada, arahkan admin untuk membuatnya lebih dulu.
 */
class PastikanTahunAjaranAktif
{
    public function handle(Request $request, Closure $next): Response
    {
        if (TahunAjaran::aktif()) {
            return $next($request);
        }

        if ($request->user()?->isAdmin()) {
            if ($request->routeIs('admin.tahun-ajaran.*')) {
                return $next($request);
            }

            return redirect()
                ->route('admin.tahun-ajaran.index')
                ->with('peringatan', 'Belum ada tahun ajaran aktif. Aktifkan satu tahun ajaran terlebih dahulu.');
        }

        abort(503, 'Belum ada tahun ajaran aktif. Hubungi admin atau Waka Kurikulum.');
    }
}
