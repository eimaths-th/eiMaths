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
        $status = $request->status ?? 'pending'; // fallback status

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
}
