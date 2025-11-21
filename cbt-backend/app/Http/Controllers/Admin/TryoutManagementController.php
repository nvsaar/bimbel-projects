<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use Illuminate\Http\Request;

class TryoutManagementController extends Controller
{
    public function index()
    {
        $tryouts = Tryout::with('creator')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $tryouts,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'duration_minutes' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = $request->user()->id;

        $tryout = Tryout::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tryout created successfully',
            'data' => $tryout,
        ], 201);
    }

    public function show($id)
    {
        $tryout = Tryout::with(['creator', 'questions.subject'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $tryout,
        ]);
    }

    public function update(Request $request, $id)
    {
        $tryout = Tryout::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after:start_time',
            'duration_minutes' => 'sometimes|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $tryout->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tryout updated successfully',
            'data' => $tryout,
        ]);
    }

    public function destroy($id)
    {
        $tryout = Tryout::findOrFail($id);
        $tryout->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tryout deleted successfully',
        ]);
    }

    public function toggleActive($id)
    {
        $tryout = Tryout::findOrFail($id);
        $tryout->is_active = !$tryout->is_active;
        $tryout->save();

        return response()->json([
            'success' => true,
            'message' => 'Tryout status updated successfully',
            'data' => $tryout,
        ]);
    }
}