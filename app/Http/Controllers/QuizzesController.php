<?php

namespace App\Http\Controllers;

// use App\Models\Question;

use App\Models\Contact;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;



class QuizzesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //

        if ($request->ajax()) {
            $query = Quiz::select('quizzes.*')
                ->withCount('questions');

            return datatables()->eloquent($query)
                ->addColumn('#', function ($row) {
                    return $row->id;
                })
                ->editColumn('level', function ($row) {
                    return $row->level;
                })
                ->editColumn('term', function ($row) {
                    return $row->term;
                })
                ->editColumn('public', function ($row) {
                    $class = $row->public ? 'success' : 'danger';
                    $text = $row->public ? 'Yes' : 'No';
                    return '<span class="badge border border-' . $class . ' text-' . $class . ' px-2 pt-5px pb-5px rounded fs-12px d-inline-flex align-items-center"><i class="fa fa-circle fs-9px fa-fw me-5px"></i>&nbsp' . $text . '</span>';
                })
                ->editColumn('questions_count', function ($row) {
                    return $row->questions_count;
                })
                ->addColumn('action', function ($row) {
                    $html = '';
                    $html .= '<a href="' . route('quizzes.show', $row->id) . '" class="btn btn-sm btn-primary" target="_blank"><i class="fa fa-eye"></i> View</a>';
                    $html .= '<a type="button" class="btn btn-sm btn-warning" id="getEditData" style="margin-left: 5px;" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</a>';
                    $html .= '<button type="button" class="btn btn-sm btn-danger delete-button" style="margin-left: 5px;" data-id="' . $row->id . '"><i class="fa fa-trash"></i> Delete</button>';
                    return $html;
                })
                ->filterColumn('questions_count', function ($query, $keyword) {
                    // Remove questions_count from search since it's a derived column
                    // The search will still work on other columns
                    return $query;
                })
                ->orderColumn('questions_count', function ($query, $order) {
                    // Properly order by the questions count
                    $query->orderByRaw('(SELECT COUNT(*) FROM question_quiz WHERE question_quiz.quiz_id = quizzes.id) ' . $order);
                })
                // Ensure ordering works for primitive columns as expected
                ->orderColumn('id', 'quizzes.id $1')
                ->orderColumn('title', 'quizzes.title $1')
                ->orderColumn('slug', 'quizzes.slug $1')
                ->orderColumn('description', 'quizzes.description $1')
                ->orderColumn('public', 'quizzes.public $1')
                ->orderColumn('level', 'quizzes.level $1')
                ->orderColumn('term', 'quizzes.term $1')
                ->orderColumn('section', 'quizzes.section $1')
                ->rawColumns(['action', 'public'])
                ->toJson();
        }

        $students = Contact::all();
        $quizzes = Quiz::all();
        return view('quiz.quiz-list', [
            'students' => $students,
            'quizzes' => $quizzes,

        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function rec(Request $request)
    {
        if ($request->has(['level', 'term', 'section'])) {
            # code...
            $questions = Question::where([
                'level' => $request->level,
                'term' => $request->term,
                'section' => $request->section,
            ])
                ->get();
        } else {
            # code...
            $questions = Question::all();
        }

        return response()->json(['questions' => $questions]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:quizzes,slug',
            'level' => 'required',
            'term' => 'required',
            'section' => 'required',
            'description' => 'nullable|string',
            'questions' => 'required|array',
            'questions.*' => 'exists:questions,id',
            // 'published' => 'boolean',
            'public' => 'boolean',
        ]);

        // dd($request->all());

        $quiz = Quiz::create([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'level' => $validated['level'],
            'term' => $validated['term'],
            'section' => $validated['section'],
            'description' => $validated['description'],
            'published' => 0,
            'public' => $validated['public'] ?? 0,
            'created_by' => Auth::user()->id,

        ]);

        $quiz->questions()->attach($validated['questions']);
        // Build position: set sequential positions based on the incoming order
        $pos = 1;
        foreach ($validated['questions'] as $qid) {
            DB::table('question_quiz')
                ->where('quiz_id', $quiz->id)
                ->where('question_id', $qid)
                ->update(['position' => $pos++]);
        }

        return response()->json(['success' => 'Quiz created successfully!']);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        $quizzes = Quiz::findOrFail($id);
        $questions = $quizzes->questions; // Assuming a relationship: Quiz hasMany Questions
        if (!empty($getAnserSessionIndex['answer']) && is_array($getAnserSessionIndex['answer'])) {
            $getAnserSessionIndexWhole = $getAnserSessionIndex['answer']['whole'] ?? null;
            $getAnserSessionIndexNumerator = $getAnserSessionIndex['answer']['numerator'] ?? null;
            $getAnserSessionIndexDenominator = $getAnserSessionIndex['answer']['denominator'] ?? null;
        }
        // dd($questions);
        return view('quiz.quiz-view', [
            'quizzes' => $quizzes,
            'questions' => $questions,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    // public function edit(string $id)
    // {
    //     $quiz = Quiz::findOrFail($id);
    //     $questions = $quiz->questions;

    //     $questions_all = Question::all();
    //     $html_questions_select = '';

    //     foreach ($questions_all as $question) {
    //         $select_question = $questions->contains('id', $question->id) ? 'selected' : '';
    //         $html_questions_select .= '<option value="' . $question->id . '" ' . $select_question . '>' . $question->text . '</option>';
    //     }

    //     return response()->json([
    //         'quiz' => $quiz,
    //         'questions' => $questions,
    //         'html_questions_select' => $html_questions_select
    //     ]);
    // }

    public function edit(string $id)
    {
        $quiz = Quiz::findOrFail($id);
        $questions = $quiz->questions;

        $questions_all = Question::all();
        $html_questions_select = '';

        foreach ($questions_all as $question) {
            $select_question = $questions->contains('id', $question->id) ? 'selected' : '';
            $truncated_text = Str::limit($question->text, 200, '...'); // Truncate to 50 chars
            $html_questions_select .= '<option value="' . $question->id . '" ' . $select_question . '> [ ' . $question->code . ' ]' . $truncated_text . '</option>';
        }

        return response()->json([
            'quiz' => $quiz,
            'questions' => $questions,
            'html_questions_select' => $html_questions_select
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // dd($request);
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:quizzes,slug,' . $id,
            'level' => 'required',
            'term' => 'required',
            'section' => 'required',
            'description' => 'nullable|string',
            'questions' => 'array',
            'questions.*' => 'integer|exists:questions,id',
            // 'published' => 'boolean',
            'public' => 'boolean',
        ]);


        $quiz = Quiz::findOrFail($id);

        $quiz->update([
            'title' => $request->input('title'),
            'slug' => $request->input('slug'),
            'level' => $request->input('level'),
            'term' => $request->input('term'),
            'section' => $request->input('section'),
            'description' => $request->input('description'),
            'published' => 0,
            'public' => $request->boolean('public'),
            'updated_by' => Auth::user()->id,

        ]);

        $quiz->questions()->sync($request->input('questions', []));

        // Option B: If questions[] is present, treat it as the intended new order.
        if ($request->has('questions')) {
            $ordered = $request->input('questions', []);

            // Put ordered ids first
            $pos = 1;
            foreach ($ordered as $qid) {
                DB::table('question_quiz')
                    ->where('quiz_id', $quiz->id)
                    ->where('question_id', $qid)
                    ->update(['position' => $pos++]);
            }

            // Append remaining ids not specified, preserving their existing relative order if available
            $existingIds = DB::table('question_quiz')
                ->where('quiz_id', $quiz->id)
                ->pluck('question_id')
                ->toArray();

            $remaining = array_values(array_diff($existingIds, $ordered));

            if (!empty($remaining)) {
                $hasAnyPosition = DB::table('question_quiz')
                    ->where('quiz_id', $quiz->id)
                    ->whereNotNull('position')
                    ->exists();

                if ($hasAnyPosition) {
                    $remaining = DB::table('question_quiz')
                        ->where('quiz_id', $quiz->id)
                        ->whereIn('question_id', $remaining)
                        ->orderBy('position', 'asc')
                        ->pluck('question_id')
                        ->toArray();
                } else {
                    $remaining = DB::table('question_quiz')
                        ->where('quiz_id', $quiz->id)
                        ->whereIn('question_id', $remaining)
                        ->orderBy('question_id', 'asc')
                        ->pluck('question_id')
                        ->toArray();
                }

                foreach ($remaining as $qid) {
                    DB::table('question_quiz')
                        ->where('quiz_id', $quiz->id)
                        ->where('question_id', $qid)
                        ->update(['position' => $pos++]);
                }
            }
        }

        return response()->json(['success' => 'Quiz updated successfully']);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $quiz = Quiz::findOrFail($id);

        foreach ($quiz->tests as $test) {
            $test->answers()->delete();
        }
        $quiz->tests()->delete();

        $quiz->delete();

        return response()->json(['success' => true, 'message' => 'Delete Quiz Success']);
    }
}
