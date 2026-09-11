<?php

namespace App\Observers;

use App\Models\TahunAjaran;

class TahunAjaranObserver
{
    public function saving(TahunAjaran $tahunAjaran): void
    {
        // Hanya satu tahun ajaran boleh aktif (CLAUDE.md §5.1).
        if ($tahunAjaran->is_aktif) {
            TahunAjaran::where('is_aktif', true)
                ->when($tahunAjaran->exists, fn ($q) => $q->whereKeyNot($tahunAjaran->getKey()))
                ->update(['is_aktif' => false]);
        }
    }

    public function saved(TahunAjaran $tahunAjaran): void
    {
        TahunAjaran::lupakanCache();
    }

    public function deleted(TahunAjaran $tahunAjaran): void
    {
        TahunAjaran::lupakanCache();
    }
}
