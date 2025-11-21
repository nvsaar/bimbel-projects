<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TryoutQuestionSet extends Model
{
    use HasFactory;

    protected $fillable = [
        'tryout_id',
        'question_id',
        'order_number',
        'score_per_question',
    ];

    protected $casts = [
        'score_per_question' => 'decimal:2',
    ];

    public function tryout()
    {
        return $this->belongsTo(Tryout::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}