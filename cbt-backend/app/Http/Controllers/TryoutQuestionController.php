<?php

namespace App\Http\Controllers;

use App\Models\Tryout;
use App\Models\TryoutQuestionSet;
use Illuminate\Http\Request;

class TryoutQuestionController extends Controller
{
    public function index($tryout_id)
    {
        $tryout = Tryout::findOrFail($tryout_id);
        
        $questions = $tryout->questions()
            ->with('subject')
            ->get()
            ->map(function ($question) {
                return [
                    'tryout_question_set_id' => $question->pivot->id,
                    'question_id' => $question->id,
                    'subject' => $question->subject->name,
                    'question_text' => $question->question_text,
                    'difficulty_level' => $question->difficulty_level,
                    'order_number' => $question->pivot->order_number,
                    'score_per_question' => $question->pivot->score_per_question,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'tryout' => $tryout,
                'questions' => $questions,
                'total_questions' => $questions->count(),
            ],
        ]);
    }

    public function store(Request $request, $tryout_id)
    {
        $tryout = Tryout::findOrFail($tryout_id);

        $validated = $request->validate([
            'questions' => 'required|array',
            'questions.*.question_id' => 'required|exists:questions,id',
            'questions.*.order_number' => 'required|integer|min:1',
            'questions.*.score_per_question' => 'nullable|numeric|min:0',
        ]);

        $created = [];

        foreach ($validated['questions'] as $questionData) {
            $tryoutQuestion = TryoutQuestionSet::updateOrCreate(
                [
                    'tryout_id' => $tryout_id,
                    'question_id' => $questionData['question_id'],
                ],
                [
                    'order_number' => $questionData['order_number'],
                    'score_per_question' => $questionData['score_per_question'] ?? 4.00,
                ]
            );

            $created[] = $tryoutQuestion;
        }

        return response()->json([
            'success' => true,
            'message' => 'Questions added to tryout successfully',
            'data' => $created,
        ], 201);
    }

    public function update(Request $request, $tryout_id, $tryout_question_set_id)
    {
        $tryoutQuestion = TryoutQuestionSet::where('tryout_id', $tryout_id)
            ->where('id', $tryout_question_set_id)
            ->firstOrFail();

        $validated = $request->validate([
            'order_number' => 'sometimes|integer|min:1',
            'score_per_question' => 'sometimes|numeric|min:0',
        ]);

        $tryoutQuestion->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Question order/score updated successfully',
            'data' => $tryoutQuestion,
        ]);
    }

    public function destroy($tryout_id, $tryout_question_set_id)
    {
        $tryoutQuestion = TryoutQuestionSet::where('tryout_id', $tryout_id)
            ->where('id', $tryout_question_set_id)
            ->firstOrFail();

        $tryoutQuestion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Question removed from tryout successfully',
        ]);
    }
}