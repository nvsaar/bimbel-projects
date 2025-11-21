<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin_manajemen', 'admin_pembuat_soal', 'siswa'])
                ->default('siswa')
                ->after('password');
            $table->string('kelas')->nullable()->after('role');
            $table->string('asal_sekolah')->nullable()->after('kelas');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'kelas', 'asal_sekolah']);
        });
    }
};