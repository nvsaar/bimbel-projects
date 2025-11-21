<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionBankController extends Controller
{
    // Subject Management
    public function indexSubjects()
    {
        $subjects = Subject::withCount('questions')->get();

        return response()->json([
            'success' => true,
            'data' => $subjects,
        ]);
    }

    public function storeSubject(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $subject = Subject::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Subject created successfully',
            'data' => $subject,
        ], 201);
    }

    public function showSubject($id)
    {
        $subject = Subject::with('questions')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $subject,
        ]);
    }

    public function updateSubject(Request $request, $id)
    {
        $subject = Subject::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        $subject->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Subject updated successfully',
            'data' => $subject,
        ]);
    }

    public function destroySubject($id)
    {
        $subject = Subject::findOrFail($id);
        $subject->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subject deleted successfully',
        ]);
    }

    // Question Management
    public function indexQuestions(Request $request)
    {
        $query = Question::with(['subject', 'creator:id,name']);

        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->has('difficulty_level')) {
            $query->where('difficulty_level', $request->difficulty_level);
        }

        $questions = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $questions,
        ]);
    }

    public function storeQuestion(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'question_text' => 'required|string',
            'option_a' => 'required|string',
            'option_b' => 'required|string',
            'option_c' => 'required|string',
            'option_d' => 'required|string',
            'option_e' => 'nullable|string',
            'correct_option' => 'required|in:A,B,C,D,E',
            'explanation' => 'nullable|string',
            'difficulty_level' => 'required|in:mudah,sedang,sulit',
        ]);

        $validated['created_by'] = $request->user()->id;

        $question = Question::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Question created successfully',
            'data' => $question->load('subject'),
        ], 201);
    }

    public function showQuestion($id)
    {
        $question = Question::with(['subject', 'creator'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $question,
        ]);
    }

    public function updateQuestion(Request $request, $id)
    {
        $question = Question::findOrFail($id);

        $validated = $request->validate([
            'subject_id' => 'sometimes|exists:subjects,id',
            'question_text' => 'sometimes|string',
            'option_a' => 'sometimes|string',
            'option_b' => 'sometimes|string',
            'option_c' => 'sometimes|string',
            'option_d' => 'sometimes|string',
            'option_e' => 'nullable|string',
            'correct_option' => 'sometimes|in:A,B,C,D,E',
            'explanation' => 'nullable|string',
            'difficulty_level' => 'sometimes|in:mudah,sedang,sulit',
        ]);

        $question->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Question updated successfully',
            'data' => $question->load('subject'),
        ]);
    }

    public function destroyQuestion($id)
    {
        $question = Question::findOrFail($id);
        $question->delete();

        return response()->json([
            'success' => true,
            'message' => 'Question deleted successfully',
        ]);
    }
}