<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactQuiz;
use App\Models\Quiz;
use Illuminate\Http\Request;

class AssignQuizToStudentController extends Controller
{
    //
    public function assignQuiz(Request $request)
    {
        $studentIds = $request->student_id; // array of selected student IDs
        $quizIds = $request->quiz_id;       // array of selected quiz IDs
        $status = $request->status ?? 'active'; // fallback status

        foreach ($studentIds as $studentId) {
            foreach ($quizIds as $quizId) {
                // Avoid duplicate assignment (optional)
                $alreadyAssigned = ContactQuiz::where('contact_id', $studentId)
                    ->where('quiz_id', $quizId)
                    ->exists();

                if (!$alreadyAssigned) {
                    ContactQuiz::create([
                        'contact_id' => $studentId,
                        'quiz_id' => $quizId,
                        'assigned_at' => now(),
                        'status' => $status,
                    ]);
                }
            }
        }

        return response()->json(['success' => 'Quiz assigned successfully']);
    }


    public function index(Request $request)
    {
        $students = Contact::all(); // or add filters/sorting as needed
        $quizzes = Quiz::all();
       $assignments = ContactQuiz::with(['student', 'quiz'])->get()->groupBy('contact_id');

        // Filter Options
        $levels = Quiz::select('level')->distinct()->orderBy('level')->pluck('level');
        $terms = Quiz::select('term')->distinct()->orderBy('term')->pluck('term');
        $sections = Quiz::select('section')->distinct()->orderBy('section')->pluck('section');

        return view('quiz.assign-index', compact(
            'students',
            'quizzes',
            'assignments',
            'levels',
            'terms',
            'sections'
        ));
    }
    public function getStudentAssignments($studentId)
    {
        $quizIds = ContactQuiz::where('contact_id', $studentId)->pluck('quiz_id');
        return response()->json(['quiz_ids' => $quizIds]);
    }

    public function updateStudentAssignments(Request $request)
    {
        $studentIds = $request->student_id; 
        // Handle array or single value
        $studentId = is_array($studentIds) ? $studentIds[0] : $studentIds;

        $newQuizIds = $request->quiz_id ?? [];
        $status = $request->status ?? 'active';

        // Sync logic
        $currentQuizIds = ContactQuiz::where('contact_id', $studentId)->pluck('quiz_id')->toArray();

        // To delete
        $toDelete = array_diff($currentQuizIds, $newQuizIds);
        if (!empty($toDelete)) {
             ContactQuiz::where('contact_id', $studentId)->whereIn('quiz_id', $toDelete)->delete();
        }

        // To Add
        $toAdd = array_diff($newQuizIds, $currentQuizIds);
        foreach ($toAdd as $quizId) {
             ContactQuiz::create([
                 'contact_id' => $studentId,
                 'quiz_id' => $quizId,
                 'assigned_at' => now(),
                 'status' => $status
             ]);
        }
        
        return response()->json(['success' => 'Assignments updated successfully']);
    }
}
