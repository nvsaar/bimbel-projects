<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use App\Models\StudentTryout;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function tryoutsList()
    {
        $tryouts = Tryout::withCount('studentTryouts')
            ->with(['studentTryouts' => function ($query) {
                $query->where('status', 'submitted');
            }])
            ->get()
            ->map(function ($tryout) {
                $submittedTryouts = $tryout->studentTryouts->where('status', 'submitted');
                
                return [
                    'id' => $tryout->id,
                    'name' => $tryout->name,
                    'start_time' => $tryout->start_time,
                    'end_time' => $tryout->end_time,
                    'total_participants' => $submittedTryouts->count(),
                    'average_score' => $submittedTryouts->avg('total_score') ?? 0,
                    'highest_score' => $submittedTryouts->max('total_score') ?? 0,
                    'lowest_score' => $submittedTryouts->min('total_score') ?? 0,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $tryouts,
        ]);
    }

    public function tryoutDetail($tryout_id)
    {
        $tryout = Tryout::findOrFail($tryout_id);

        $participants = StudentTryout::where('tryout_id', $tryout_id)
            ->where('status', 'submitted')
            ->with('user:id,name,email,kelas,asal_sekolah')
            ->get()
            ->map(function ($studentTryout) {
                return [
                    'student_tryout_id' => $studentTryout->id,
                    'student_id' => $studentTryout->user->id,
                    'student_name' => $studentTryout->user->name,
                    'email' => $studentTryout->user->email,
                    'kelas' => $studentTryout->user->kelas,
                    'asal_sekolah' => $studentTryout->user->asal_sekolah,
                    'score' => $studentTryout->total_score,
                    'start_time' => $studentTryout->start_time,
                    'submitted_at' => $studentTryout->updated_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'tryout' => $tryout,
                'participants' => $participants,
            ],
        ]);
    }

    public function studentHistory($student_id)
    {
        $student = User::findOrFail($student_id);

        if ($student->role !== 'siswa') {
            return response()->json([
                'success' => false,
                'message' => 'User is not a student',
            ], 400);
        }

        $history = StudentTryout::where('user_id', $student_id)
            ->with('tryout:id,name,start_time,end_time')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($studentTryout) {
                return [
                    'student_tryout_id' => $studentTryout->id,
                    'tryout_id' => $studentTryout->tryout_id,
                    'tryout_name' => $studentTryout->tryout->name,
                    'status' => $studentTryout->status,
                    'score' => $studentTryout->total_score,
                    'start_time' => $studentTryout->start_time,
                    'end_time' => $studentTryout->end_time,
                    'submitted_at' => $studentTryout->status === 'submitted' ? $studentTryout->updated_at : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'student' => $student,
                'history' => $history,
            ],
        ]);
    }
}