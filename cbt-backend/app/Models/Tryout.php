<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tryout extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'start_time',
        'end_time',
        'duration_minutes',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tryoutQuestionSets()
    {
        return $this->hasMany(TryoutQuestionSet::class);
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class, 'tryout_question_sets')
            ->withPivot('id', 'order_number', 'score_per_question')
            ->withTimestamps()
            ->orderBy('tryout_question_sets.order_number');
    }

    public function studentTryouts()
    {
        return $this->hasMany(StudentTryout::class);
    }

    // Helper methods
    public function isAvailable()
    {
        $now = now();
        return $this->is_active && 
               $this->start_time <= $now && 
               $this->end_time >= $now;
    }
}