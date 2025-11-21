<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'kelas',
        'asal_sekolah',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relasi
    public function createdQuestions()
    {
        return $this->hasMany(Question::class, 'created_by');
    }

    public function createdTryouts()
    {
        return $this->hasMany(Tryout::class, 'created_by');
    }

    public function studentTryouts()
    {
        return $this->hasMany(StudentTryout::class);
    }

    // Helper methods
    public function isAdminManajemen()
    {
        return $this->role === 'admin_manajemen';
    }

    public function isAdminPembuatSoal()
    {
        return $this->role === 'admin_pembuat_soal';
    }

    public function isSiswa()
    {
        return $this->role === 'siswa';
    }
}