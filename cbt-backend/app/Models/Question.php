<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'created_by',
        'question_text',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'option_e',
        'correct_option',
        'explanation',
        'difficulty_level',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tryoutQuestionSets()
    {
        return $this->hasMany(TryoutQuestionSet::class);
    }

    public function tryouts()
    {
        return $this->belongsToMany(Tryout::class, 'tryout_question_sets')
            ->withPivot('order_number', 'score_per_question')
            ->withTimestamps();
    }
}