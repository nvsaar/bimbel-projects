<?php

namespace App\Http\Controllers;

use App\Models\Tryout;
use App\Models\StudentTryout;
use App\Models\StudentAnswer;
use App\Models\TryoutQuestionSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentTryoutController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        
        $tryouts = Tryout::where('is_active', true)
            ->with(['studentTryouts' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }])
            ->get()
            ->map(function ($tryout) {
                $studentTryout = $tryout->studentTryouts->first();
                
                return [
                    'id' => $tryout->id,
                    'name' => $tryout->name,
                    'description' => $tryout->description,
                    'start_time' => $tryout->start_time,
                    'end_time' => $tryout->end_time,
                    'duration_minutes' => $tryout->duration_minutes,
                    'is_available' => $tryout->isAvailable(),
                    'status' => $studentTryout ? $studentTryout->status : 'not_started',
                    'student_tryout_id' => $studentTryout ? $studentTryout->id : null,
                    'score' => $studentTryout && $studentTryout->status === 'submitted' 
                        ? $studentTryout->total_score 
                        : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $tryouts,
        ]);
    }

    public function start(Request $request, $tryout_id)
    {
        $tryout = Tryout::findOrFail($tryout_id);
        $userId = $request->user()->id;

        if (!$tryout->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'Tryout is not available at this time',
            ], 400);
        }

        // Check if already started
        $existingTryout = StudentTryout::where('user_id', $userId)
            ->where('tryout_id', $tryout_id)
            ->first();

        if ($existingTryout) {
            if ($existingTryout->status === 'submitted') {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already completed this tryout',
                ], 400);
            }

            if ($existingTryout->status === 'in_progress') {
                return response()->json([
                    'success' => true,
                    'message' => 'Tryout already in progress',
                    'data' => $existingTryout,
                ]);
            }
        }

        $startTime = now();
        $endTime = $startTime->copy()->addMinutes($tryout->duration_minutes);

        // Ensure end time doesn't exceed tryout end time
        if ($endTime->greaterThan($tryout->end_time)) {
            $endTime = $tryout->end_time;
        }

        $studentTryout = StudentTryout::create([
            'user_id' => $userId,
            'tryout_id' => $tryout_id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => 'in_progress',
        ]);

        // Create empty answers for all questions
        $questions = $tryout->tryoutQuestionSets;
        foreach ($questions as $questionSet) {
            StudentAnswer::create([
                'student_tryout_id' => $studentTryout->id,
                'question_id' => $questionSet->question_id,
                'selected_option' => null,
                'is_correct' => false,
                'score' => 0,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tryout started successfully',
            'data' => $studentTryout,
        ], 201);
    }

    public function getQuestions($student_tryout_id, Request $request)
    {
        $studentTryout = StudentTryout::findOrFail($student_tryout_id);

        // Verify ownership
        if ($studentTryout->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
            ], 403);
        }

        if ($studentTryout->status !== 'in_progress') {
            return response()->json([
                'success' => false,
                'message' => 'Tryout is not in progress',
            ], 400);
        }

        // Check if expired
        if ($studentTryout->isExpired()) {
            $studentTryout->update(['status' => 'expired']);
            
            return response()->json([
                'success' => false,
                'message' => 'Tryout time has expired',
            ], 400);
        }

        $questions = $studentTryout->tryout->questions()
            ->with('subject')
            ->get()
            ->map(function ($question) use ($studentTryout) {
                $answer = StudentAnswer::where('student_tryout_id', $studentTryout->id)
                    ->where('question_id', $question->id)
                    ->first();

                return [
                    'question_id' => $question->id,
                    'subject' => $question->subject->name,
                    'question_text' => $question->question_text,
                    'option_a' => $question->option_a,
                    'option_b' => $question->option_b,
                    'option_c' => $question->option_c,
                    'option_d' => $question->option_d,
                    'option_e' => $question->option_e,
                    'order_number' => $question->pivot->order_number,
                    'selected_option' => $answer ? $answer->selected_option : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'student_tryout' => $studentTryout,
                'questions' => $questions,
                'time_remaining_seconds' => now()->diffInSeconds($studentTryout->end_time, false),
            ],
        ]);
    }

    public function saveAnswer(Request $request, $student_tryout_id)
    {
        $studentTryout = StudentTryout::findOrFail($student_tryout_id);

        // Verify ownership
        if ($studentTryout->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
            ], 403);
        }

        if ($studentTryout->status !== 'in_progress') {
            return response()->json([
                'success' => false,
                'message' => 'Tryout is not in progress',
            ], 400);
        }

        if ($studentTryout->isExpired()) {
            $studentTryout->update(['status' => 'expired']);
            
            return response()->json([
                'success' => false,
                'message' => 'Tryout time has expired',
            ], 400);
        }

        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'selected_option' => 'required|in:A,B,C,D,E',
        ]);

        $answer = StudentAnswer::where('student_tryout_id', $student_tryout_id)
            ->where('question_id', $validated['question_id'])
            ->firstOrFail();

        $answer->update([
            'selected_option' => $validated['selected_option'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Answer saved successfully',
            'data' => $answer,
        ]);
    }

    public function submit($student_tryout_id, Request $request)
    {
        $studentTryout = StudentTryout::findOrFail($student_tryout_id);

        // Verify ownership
        if ($studentTryout->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
            ], 403);
        }

        if ($studentTryout->status === 'submitted') {
            return response()->json([
                'success' => false,
                'message' => 'Tryout already submitted',
            ], 400);
        }

        DB::beginTransaction();
        try {
            $tryout = $studentTryout->tryout;
            $totalScore = 0;
            $correctAnswers = 0;
            $wrongAnswers = 0;
            $unanswered = 0;

            // Get all answers
            $answers = StudentAnswer::where('student_tryout_id', $student_tryout_id)
                ->with(['question' => function ($query) use ($tryout) {
                    $query->with(['tryoutQuestionSets' => function ($q) use ($tryout) {
                        $q->where('tryout_id', $tryout->id);
                    }]);
                }])
                ->get();

            foreach ($answers as $answer) {
                if ($answer->selected_option === null) {
                    $unanswered++;
                    continue;
                }

                $question = $answer->question;
                $isCorrect = $answer->selected_option === $question->correct_option;
                
                // Get score per question from pivot
                $scorePerQuestion = $question->tryoutQuestionSets->first()->score_per_question ?? 4.00;
                $score = $isCorrect ? $scorePerQuestion : 0;

                $answer->update([
                    'is_correct' => $isCorrect,
                    'score' => $score,
                ]);

                $totalScore += $score;
                
                if ($isCorrect) {
                    $correctAnswers++;
                } else {
                    $wrongAnswers++;
                }
            }

            $studentTryout->update([
                'status' => 'submitted',
                'total_score' => $totalScore,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tryout submitted successfully',
                'data' => [
                    'student_tryout_id' => $studentTryout->id,
                    'total_score' => $totalScore,
                    'correct_answers' => $correctAnswers,
                    'wrong_answers' => $wrongAnswers,
                    'unanswered' => $unanswered,
                    'total_questions' => $answers->count(),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit tryout',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function result($student_tryout_id, Request $request)
    {
        $studentTryout = StudentTryout::with('tryout')->findOrFail($student_tryout_id);

        // Verify ownership
        if ($studentTryout->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
            ], 403);
        }

        if ($studentTryout->status !== 'submitted') {
            return response()->json([
                'success' => false,
                'message' => 'Tryout has not been submitted yet',
            ], 400);
        }

        $answers = StudentAnswer::where('student_tryout_id', $student_tryout_id)->get();

        $summary = [
            'student_tryout_id' => $studentTryout->id,
            'tryout_name' => $studentTryout->tryout->name,
            'total_score' => $studentTryout->total_score,
            'total_questions' => $answers->count(),
            'correct_answers' => $answers->where('is_correct', true)->count(),
            'wrong_answers' => $answers->where('is_correct', false)->where('selected_option', '!=', null)->count(),
            'unanswered' => $answers->where('selected_option', null)->count(),
            'submitted_at' => $studentTryout->updated_at,
        ];

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    public function review($student_tryout_id, Request $request)
    {
        $studentTryout = StudentTryout::with('tryout')->findOrFail($student_tryout_id);

        // Verify ownership
        if ($studentTryout->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden',
            ], 403);
        }

        if ($studentTryout->status !== 'submitted') {
            return response()->json([
                'success' => false,
                'message' => 'Tryout has not been submitted yet',
            ], 400);
        }

        $answers = StudentAnswer::where('student_tryout_id', $student_tryout_id)
            ->with(['question.subject'])
            ->get()
            ->map(function ($answer) {
                $question = $answer->question;
                
                return [
                    'question_id' => $question->id,
                    'subject' => $question->subject->name,
                    'question_text' => $question->question_text,
                    'option_a' => $question->option_a,
                    'option_b' => $question->option_b,
                    'option_c' => $question->option_c,
                    'option_d' => $question->option_d,
                    'option_e' => $question->option_e,
                    'correct_option' => $question->correct_option,
                    'selected_option' => $answer->selected_option,
                    'is_correct' => $answer->is_correct,
                    'score' => $answer->score,
                    'explanation' => $question->explanation,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'tryout_name' => $studentTryout->tryout->name,
                'total_score' => $studentTryout->total_score,
                'questions' => $answers,
            ],
        ]);
    }
}