<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class LogAktivitasController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.log.index', [
            'daftar' => Activity::query()
                ->when($request->filled('log'), fn ($q) => $q->where('log_name', $request->string('log')))
                ->with(['causer', 'subject'])
                ->latest()
                ->paginate(30)
                ->withQueryString(),
            'namaLog' => Activity::query()->distinct()->pluck('log_name')->filter()->values(),
        ]);
    }
}
