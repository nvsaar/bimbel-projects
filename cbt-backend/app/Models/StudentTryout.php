<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentTryout extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tryout_id',
        'start_time',
        'end_time',
        'status',
        'total_score',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'total_score' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tryout()
    {
        return $this->belongsTo(Tryout::class);
    }

    public function studentAnswers()
    {
        return $this->hasMany(StudentAnswer::class);
    }

    // Helper methods
    public function isExpired()
    {
        return $this->end_time && now()->greaterThan($this->end_time);
    }

    public function canContinue()
    {
        return $this->status === 'in_progress' && !$this->isExpired();
    }
}