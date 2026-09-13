<?php

namespace App\Http\Requests;

use App\Enums\JenisPenilaian;
use App\Models\TahunAjaran;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PenilaianRequest extends FormRequest
{
    public function authorize(): bool
    {
        $penilaian = $this->route('penilaian');

        return $penilaian
            ? $this->user()->can('update', $penilaian)
            : $this->user()->can('create', \App\Models\Penilaian::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'tahun_ajaran_id' => TahunAjaran::aktif()?->id,
            'guru_id' => $this->user()->id,
            'is_remedial' => $this->boolean('is_remedial'),
            'bobot' => ($this->filled('bobot') && is_numeric($this->input('bobot'))) ? $this->input('bobot') : 0,
        ]);
    }

    public function rules(): array
    {
        return [
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajarans,id'],
            'guru_id' => ['required', 'exists:users,id'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'mata_pelajaran_id' => ['required', 'exists:mata_pelajarans,id'],
            'bab_id' => ['nullable', 'exists:babs,id'],
            'tujuan_pembelajaran_id' => ['nullable', 'exists:tujuan_pembelajarans,id'],
            'jenis' => ['required', Rule::in(array_keys(JenisPenilaian::pilihan()))],
            'nama' => ['required', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
            'bobot' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'nilai_maksimal' => ['required', 'integer', 'min:1', 'max:1000'],
            'is_remedial' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'kelas_id' => 'kelas',
            'mata_pelajaran_id' => 'mata pelajaran',
            'bab_id' => 'bab',
            'tujuan_pembelajaran_id' => 'tujuan pembelajaran',
            'jenis' => 'jenis penilaian',
            'nama' => 'nama penilaian',
            'nilai_maksimal' => 'nilai maksimal',
        ];
    }
}
