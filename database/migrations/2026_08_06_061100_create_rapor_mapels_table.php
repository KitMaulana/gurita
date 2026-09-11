<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rapor_mapels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajarans')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->decimal('nilai_formatif', 5, 2)->nullable();
            $table->decimal('nilai_sumatif_lingkup', 5, 2)->nullable();
            $table->decimal('nilai_sumatif_akhir', 5, 2)->nullable();
            $table->decimal('nilai_akhir', 5, 2)->default(0);
            $table->char('predikat', 1)->nullable();
            $table->boolean('is_tuntas')->default(false);
            $table->text('deskripsi_capaian')->nullable();
            $table->unsignedSmallInteger('jumlah_pertemuan')->default(0);
            $table->unsignedSmallInteger('jumlah_hadir')->default(0);
            $table->unsignedSmallInteger('jumlah_sakit')->default(0);
            $table->unsignedSmallInteger('jumlah_izin')->default(0);
            $table->unsignedSmallInteger('jumlah_alfa')->default(0);
            $table->timestamp('dibekukan_pada')->nullable();
            $table->timestamps();

            $table->unique(
                ['tahun_ajaran_id', 'kelas_id', 'mata_pelajaran_id', 'siswa_id'],
                'rapor_mapel_unik'
            );
        });

        Schema::create('pengaturans', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturans');
        Schema::dropIfExists('rapor_mapels');
    }
};
