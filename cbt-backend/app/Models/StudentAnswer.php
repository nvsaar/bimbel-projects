<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_tryout_id',
        'question_id',
        'selected_option',
        'is_correct',
        'score',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'score' => 'decimal:2',
    ];

    public function studentTryout()
    {
        return $this->belongsTo(StudentTryout::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}