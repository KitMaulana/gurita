<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nip')->nullable()->unique()->after('name');
            $table->string('jabatan')->nullable()->after('email');
            $table->text('quote')->nullable()->after('jabatan');
            $table->string('foto')->nullable()->after('quote');
            $table->boolean('is_aktif')->default(true)->after('foto');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['nip']);
            $table->dropColumn(['nip', 'jabatan', 'quote', 'foto', 'is_aktif']);
        });
    }
};
