<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kktps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajarans')->cascadeOnDelete();
            $table->enum('tingkat', ['X', 'XI', 'XII']);
            $table->unsignedTinyInteger('nilai')->default(75);
            $table->timestamps();

            $table->unique(['tahun_ajaran_id', 'mata_pelajaran_id', 'tingkat'], 'kktp_unik');
        });

        Schema::create('penilaians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajarans')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajarans')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('bab_id')->nullable()->constrained('babs')->nullOnDelete();
            $table->foreignId('tujuan_pembelajaran_id')->nullable()
                ->constrained('tujuan_pembelajarans')->nullOnDelete();
            $table->enum('jenis', ['formatif', 'sumatif_lingkup', 'sumatif_akhir']);
            $table->string('nama');
            $table->date('tanggal');
            $table->decimal('bobot', 5, 2)->default(0);
            $table->unsignedSmallInteger('nilai_maksimal')->default(100);
            $table->boolean('is_remedial')->default(false);
            $table->boolean('is_terkunci')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['kelas_id', 'mata_pelajaran_id', 'jenis']);
        });

        Schema::create('nilais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penilaian_id')->constrained('penilaians')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->decimal('nilai', 5, 2)->nullable();   // null = belum dinilai
            $table->string('catatan')->nullable();
            $table->timestamps();

            $table->unique(['penilaian_id', 'siswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilais');
        Schema::dropIfExists('penilaians');
        Schema::dropIfExists('kktps');
    }
};
