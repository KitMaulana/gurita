<?php

namespace App\Http\Requests;

use App\Enums\HariEnum;
use App\Models\TahunAjaran;
use App\Services\PemeriksaBentrokJadwal;
use App\Support\JamPelajaran;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JadwalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        $hari = $this->input('hari');
        $jamKe = (int) $this->input('jam_ke');
        $jamMulai = $this->input('jam_mulai');
        $jamSelesai = $this->input('jam_selesai');

        // Jika jam mulai/selesai kosong, otomatis hitung dari konfigurasi slot waktu
        if ($hari && $jamKe && (blank($jamMulai) || blank($jamSelesai))) {
            $slot = JamPelajaran::getSlotTime($jamKe, $hari);
            if ($slot) {
                $jamMulai = $jamMulai ?: $slot['start'];
                $jamSelesai = $jamSelesai ?: $slot['end'];
            }
        }

        $isGlobal = $this->filled('title');

        $this->merge([
            'tahun_ajaran_id' => $this->input('tahun_ajaran_id') ?: TahunAjaran::aktif()?->id,
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai,
            'kelas_id' => $isGlobal ? null : ($this->input('kelas_id') ?: null),
            'mata_pelajaran_id' => $isGlobal ? null : $this->input('mata_pelajaran_id'),
            'guru_id' => $isGlobal ? null : $this->input('guru_id'),
        ]);
    }

    public function rules(): array
    {
        $isGlobal = $this->filled('title');

        $rules = [
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajarans,id'],
            'hari' => ['required', Rule::in(array_keys(HariEnum::pilihan()))],
            'jam_ke' => ['required', 'integer', 'min:1', 'max:15'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
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

    /** Cek bentrok setelah aturan dasar lolos. */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $data = $validator->validated();

            // Hanya cek bentrok jika bukan agenda bersama global
            if (empty($data['title'])) {
                $pesanBentrok = app(PemeriksaBentrokJadwal::class)
                    ->periksa($data, $this->route('jadwal')?->id);

                foreach ($pesanBentrok as $pesan) {
                    $validator->errors()->add('jam_ke', $pesan);
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
            'jam_ke' => 'jam ke',
            'jam_mulai' => 'jam mulai',
            'jam_selesai' => 'jam selesai',
        ];
    }

    public function messages(): array
    {
        return [
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
        ];
    }
}
