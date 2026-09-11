<?php

namespace App\Http\Requests;

use App\Enums\HariEnum;
use App\Models\TahunAjaran;
use App\Services\PemeriksaBentrokJadwal;
use App\Support\JamPelajaran;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JadwalBulkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        $isGlobal = $this->filled('title');

        $this->merge([
            'tahun_ajaran_id' => $this->input('tahun_ajaran_id') ?: TahunAjaran::aktif()?->id,
            'kelas_id' => $isGlobal ? null : ($this->input('kelas_id') ?: null),
        ]);
    }

    public function rules(): array
    {
        $isGlobal = $this->filled('title');

        $rules = [
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajarans,id'],
            'hari' => ['required', Rule::in(array_keys(HariEnum::pilihan()))],
            'jam_ke' => ['required', 'array', 'min:1'],
            'jam_ke.*' => ['integer', 'min:1', 'max:15'],
            'ruang' => ['nullable', 'string', 'max:50'],
        ];

        if ($isGlobal) {
            $rules['title'] = ['required', 'string', 'max:255'];
            $rules['kelas_id'] = ['nullable'];
            $rules['mata_pelajaran_id'] = ['nullable'];
            $rules['guru_id'] = ['nullable'];
        } else {
            $rules['title'] = ['nullable', 'string', 'max:255'];
            $rules['kelas_id'] = ['nullable', 'exists:kelas,id'];
            $rules['mata_pelajaran_id'] = ['required', 'exists:mata_pelajarans,id'];
            $rules['guru_id'] = ['required', 'exists:users,id'];
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $data = $validator->validated();

            if (empty($data['title'])) {
                $pemeriksa = app(PemeriksaBentrokJadwal::class);

                foreach ($data['jam_ke'] as $jp) {
                    $slot = JamPelajaran::getSlotTime($jp, $data['hari']);
                    $calon = [
                        'tahun_ajaran_id' => $data['tahun_ajaran_id'],
                        'kelas_id' => $data['kelas_id'],
                        'mata_pelajaran_id' => $data['mata_pelajaran_id'],
                        'guru_id' => $data['guru_id'],
                        'hari' => $data['hari'],
                        'jam_ke' => $jp,
                        'jam_mulai' => $slot['start'] ?? '07:00',
                        'jam_selesai' => $slot['end'] ?? '07:45',
                        'ruang' => $data['ruang'] ?? null,
                    ];

                    $pesanBentrok = $pemeriksa->periksa($calon);
                    foreach ($pesanBentrok as $pesan) {
                        $validator->errors()->add('jam_ke', "JP {$jp}: {$pesan}");
                    }
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'title' => 'nama agenda/kegiatan',
            'kelas_id' => 'kelas',
            'mata_pelajaran_id' => 'mata pelajaran',
            'guru_id' => 'guru pengampu',
            'hari' => 'hari',
            'jam_ke' => 'jam pelajaran',
        ];
    }
}
