<?php

namespace App\Http\Controllers;

// use App\Models\Question;

use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\Request;

class QuizzesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //

        if ($request->ajax()) {

            // $datas = Quiz::latest();
            $datas = Quiz::withCount('questions')->latest();
            return datatables()->of($datas)
                ->addColumn('#', function ($row) {
                    return $row->id; // Adjust based on your need
                })

                ->editColumn('level', function ($row) {
                    return $row->level;
                })
                ->editColumn('term', function ($row) {
                    return $row->term;
                })
                ->editColumn('published', function ($row) {
                    $class = $row->published ? 'success' : 'danger';
                    $text = $row->published ? 'Yes' : 'No';
                    // $html = '<input class="disabled:opacity-50 disabled:cursor-not-allowed" type="checkbox" disabled ' . $checked . '>';
                    $html = '<span class="badge border border-' . $class . ' text-' . $class . ' px-2 pt-5px pb-5px rounded fs-12px d-inline-flex align-items-center"><i class="fa fa-circle fs-9px fa-fw me-5px"></i>&nbsp' . $text . '</span>';
                    return $html;
                })
                ->editColumn('public', function ($row) {
                    $class = $row->public ? 'success' : 'danger';
                    $text = $row->public ? 'Yes' : 'No';
                    // $html = '<input class="disabled:opacity-50 disabled:cursor-not-allowed" type="checkbox" disabled ' . $checked . '>';
                    $html = '<span class="badge border border-' . $class . ' text-' . $class . ' px-2 pt-5px pb-5px rounded fs-12px d-inline-flex align-items-center"><i class="fa fa-circle fs-9px fa-fw me-5px"></i>&nbsp' . $text . '</span>';
                    return $html;
                })
                ->editColumn('questions_count', function ($row) {
                    return $row->questions_count;
                })
                ->addColumn('action', function ($row) {
                    // $html = '<a href="' . route('questions.edit', $row->id) . '" class="btn btn-sm btn-warning" id="getEditData" data-id="' . $row->id . '"><<i class="fa fa-edit"></i> Edit</a>';
                    $html = '<button type="button" class="btn btn-sm btn-warning" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</a>';
                    $html .= '<button type="button" class="btn btn-sm btn-danger delete-button" style="margin-left: 5px;" data-id="' . $row->id . '"><i class="fa fa-trash"></i> Delete</button>';
                    return $html;
                })->rawColumns(['action', 'published', 'public'])->toJson();
        }
        return view('quiz.quiz-list');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function rec()
    {
        $questions = Question::all();
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
            'published' => 'boolean',
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
            'published' => $validated['published'] ?? 0,
            'public' => $validated['public'] ?? 0,
        ]);

        $quiz->questions()->attach($validated['questions']);

        return response()->json(['success' => 'Quiz created successfully!']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $quiz = Quiz::findOrFail($id);
        $questions = $quiz->questions;

        $questions_all = Question::all();
        $html_questions_select = '';

        foreach ($questions_all as $question) {
            $select_question = $questions->contains('id', $question->id) ? 'selected' : '';
            $html_questions_select .= '<option value="' . $question->id . '" ' . $select_question . '>' . $question->text . '</option>';
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
            'published' => 'boolean',
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
            'published' => $request->boolean('published'),
            'public' => $request->boolean('public'),
        ]);

        $quiz->questions()->sync($request->input('questions', []));
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
