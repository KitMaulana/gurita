<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('babs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajarans')->cascadeOnDelete();
            $table->enum('tingkat', ['X', 'XI', 'XII']);
            $table->string('kode', 30);                 // mis. BAB 1
            $table->string('judul');
            $table->text('capaian_pembelajaran')->nullable();
            $table->unsignedSmallInteger('urutan')->default(1);
            $table->timestamps();

            $table->index(['mata_pelajaran_id', 'tingkat']);
        });

        Schema::create('tujuan_pembelajarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bab_id')->constrained('babs')->cascadeOnDelete();
            $table->string('kode', 30);                 // mis. TP 1.1
            $table->text('deskripsi');
            $table->unsignedSmallInteger('urutan')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tujuan_pembelajarans');
        Schema::dropIfExists('babs');
    }
};
