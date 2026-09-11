<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwals', function (Blueprint $table) {
            $table->string('title')->nullable()->after('mata_pelajaran_id');
            $table->foreignId('kelas_id')->nullable()->change();
            $table->foreignId('mata_pelajaran_id')->nullable()->change();
            $table->foreignId('guru_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('jadwals', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->foreignId('kelas_id')->nullable(false)->change();
            $table->foreignId('mata_pelajaran_id')->nullable(false)->change();
            $table->foreignId('guru_id')->nullable(false)->change();
        });
    }
};
