<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Subject;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin users
        User::create([
            'name' => 'Admin Manajemen',
            'email' => 'admin@cbt.com',
            'password' => Hash::make('password'),
            'role' => 'admin_manajemen',
        ]);

        User::create([
            'name' => 'Admin Pembuat Soal',
            'email' => 'pembuat@cbt.com',
            'password' => Hash::make('password'),
            'role' => 'admin_pembuat_soal',
        ]);

        // Create sample student
        User::create([
            'name' => 'Siswa Demo',
            'email' => 'siswa@cbt.com',
            'password' => Hash::make('password'),
            'role' => 'siswa',
            'kelas' => '12 IPA',
            'asal_sekolah' => 'SMA Negeri 1',
        ]);

        // Create sample subjects
        $subjects = [
            ['name' => 'Matematika', 'description' => 'Mata pelajaran Matematika'],
            ['name' => 'Bahasa Indonesia', 'description' => 'Mata pelajaran Bahasa Indonesia'],
            ['name' => 'Bahasa Inggris', 'description' => 'Mata pelajaran Bahasa Inggris'],
            ['name' => 'TPS', 'description' => 'Tes Potensi Skolastik'],
            ['name' => 'Fisika', 'description' => 'Mata pelajaran Fisika'],
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }
    }
}