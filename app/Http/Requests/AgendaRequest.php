<?php

namespace App\Http\Requests;

use App\Enums\StatusAgenda;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AgendaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('agenda'));
    }

    public function rules(): array
    {
        return [
            'judul_materi' => ['required', 'string', 'max:255'],
            'uraian_kegiatan' => ['nullable', 'string', 'max:2000'],
            'metode' => ['nullable', 'string', 'max:255'],
            'bab_id' => ['nullable', 'exists:babs,id'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(array_keys(StatusAgenda::pilihan()))],
        ];
    }

    public function attributes(): array
    {
        return [
            'judul_materi' => 'judul materi',
            'uraian_kegiatan' => 'uraian kegiatan',
            'bab_id' => 'bab',
            'status' => 'status pertemuan',
        ];
    }
}
