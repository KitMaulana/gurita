<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TahunAjaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $id = $this->route('tahunAjaran')?->id;

        return [
            'nama' => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/', 'max:20'],
            'semester' => ['required', Rule::in(['ganjil', 'genap'])],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after:tanggal_mulai'],
            'is_aktif' => ['nullable', 'boolean'],
            'nama_semester' => [
                Rule::unique('tahun_ajarans', 'nama')
                    ->where(fn ($q) => $q->where('semester', $this->input('semester')))
                    ->ignore($id),
            ],
            'otomatis_salin' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        // Kolom bantu agar aturan unik gabungan (nama + semester) bisa dijalankan.
        $this->merge([
            'nama_semester' => $this->input('nama'),
            'is_aktif' => $this->boolean('is_aktif'),
            'otomatis_salin' => $this->boolean('otomatis_salin'),
        ]);
    }

    public function attributes(): array
    {
        return [
            'nama' => 'nama tahun ajaran',
            'nama_semester' => 'kombinasi tahun ajaran dan semester',
            'tanggal_mulai' => 'tanggal mulai',
            'tanggal_selesai' => 'tanggal selesai',
            'otomatis_salin' => 'otomatis salin data',
        ];
    }

    public function messages(): array
    {
        return [
            'nama.regex' => 'Format nama tahun ajaran harus seperti 2026/2027.',
            'nama_semester.unique' => 'Tahun ajaran dengan semester tersebut sudah terdaftar.',
            'tanggal_selesai.after' => 'Tanggal selesai harus setelah tanggal mulai.',
        ];
    }

    /** @return array<string,mixed> data siap simpan (tanpa kolom bantu) */
    public function dataTersimpan(): array
    {
        return $this->safe()->except(['nama_semester', 'otomatis_salin']);
    }
}
