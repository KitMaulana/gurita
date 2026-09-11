<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->constrained('jadwals')->cascadeOnDelete();
            $table->date('tanggal');
            $table->unsignedSmallInteger('pertemuan_ke');
            $table->string('judul_materi');
            $table->text('uraian_kegiatan')->nullable();
            $table->string('metode')->nullable();
            $table->foreignId('bab_id')->nullable()->constrained('babs')->nullOnDelete();
            $table->text('catatan')->nullable();
            $table->enum('status', [
                'terlaksana', 'tugas_mandiri', 'kosong', 'libur', 'kegiatan_sekolah',
            ])->default('terlaksana');
            $table->boolean('is_terkunci')->default(false);
            $table->timestamps();

            $table->unique(['jadwal_id', 'tanggal']);
            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agendas');
    }
};
