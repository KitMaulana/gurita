<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('folder_materis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajarans')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('folder_materis')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('users')->cascadeOnDelete();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->string('warna')->default('from-primary to-primary-600');
            $table->timestamps();
        });

        Schema::create('folder_materi_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_materi_id')->constrained('folder_materis')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();

            $table->unique(['folder_materi_id', 'kelas_id'], 'folder_kelas_unik');
        });

        Schema::create('materis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_materi_id')->constrained('folder_materis')->cascadeOnDelete();
            $table->foreignId('bab_id')->nullable()->constrained('babs')->nullOnDelete();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->enum('tipe', ['file', 'tautan', 'video'])->default('file');
            $table->string('path')->nullable();
            $table->string('url')->nullable();
            $table->unsignedBigInteger('ukuran')->nullable();   // byte
            $table->unsignedInteger('jumlah_unduh')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materis');
        Schema::dropIfExists('folder_materi_kelas');
        Schema::dropIfExists('folder_materis');
    }
};
