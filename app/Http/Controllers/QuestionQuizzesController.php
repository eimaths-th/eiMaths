<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\Request;

class QuestionQuizzesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        if ($request->ajax()) {

            $datas = Question::latest();

            return datatables()->of($datas)
                ->addColumn('#', function ($row) {
                    return $row->id;
                })
                ->addColumn('option', function ($row) {
                    if ($row->type === 'written') {
                        # code...
                        return $row->written_answer;
                    } else {

                        return $row->options()->count();
                    }
                })
                ->addColumn('type_option', function ($row) {
                    return '';
                })
                ->addColumn('action', function ($row) {
                    // $html = '<a href="' . route('questions.edit', $row->id) . '" class="btn btn-sm btn-warning" id="getEditData" data-id="' . $row->id . '"><<i class="fa fa-edit"></i> Edit</a>';
                    $html = '<button type="button" class="btn btn-sm btn-warning" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</a>';
                    $html .= '<button type="button" class="btn btn-sm btn-danger delete-button" style="margin-left: 5px;" data-id="' . $row->id . '"><i class="fa fa-trash"></i> Delete</button>';
                    return $html;
                })->rawColumns(['action'])->toJson();
        }
        return view('questionquizzes.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $editing = false;
        $quizzes = Quiz::all();
        return view('questionquizzes.create', [
            'quizzes' => $quizzes,
            'editing' => $editing,
            'question' => null,
            'options' => [],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $type = $request->type;

        $commonRules = [
            'questions' => 'required|array',
            'questions.*.text' => 'required|string',
            'questions.*.type' => 'required|string|in:options,written',
            'questions.*.code_snippet' => 'nullable|string',
            'questions.*.answer_explanation' => 'nullable|string',
            'questions.*.more_info_link' => 'nullable|string',
            'questions.*.written' => 'nullable|string',
            'questions.*.options' => 'nullable|array',
            'questions.*.options.*.text' => 'nullable|string',
            'questions.*.options.*.correct' => 'nullable|boolean',
            'questions_id' => 'nullable|array',
            'questions_id.*' => 'integer',
            'type' => 'required|integer',
        ];

        if ($type === "1") {
            $request->validate(array_merge($commonRules, [
                'title' => 'required|string',
                'slug' => 'required|string',
                'level' => 'required|string',
                'term' => 'required|integer',
                'section' => 'required|integer',
                'description' => 'nullable|string',
                'published' => 'nullable|boolean',
                'public' => 'nullable|boolean',
            ]));
        } else {
            $request->validate($commonRules);
        }


        $quiz_id = $request->quiz_id;
        $questions = $request->questions;

        $questions_ids = $request->questions_id ?? [];
        $questions_create_id = [];

        foreach ($questions as $questionn) {

            $question = new Question();
            $question->text = $questionn['text'];
            $question->type = $questionn['type'];
            $question->code_snippet = $questionn['code_snippet'] ?? null;
            $question->answer_explanation = $questionn['answer_explanation'] ?? null;
            $question->more_info_link = $questionn['more_info_link'] ?? null;
            $question->written_answer = $questionn['type'] === 'written' ? $questionn['written'] : null;
            $question->save();

            $code = str_pad($question->id, 6, '0', STR_PAD_LEFT);
            $question->update(['code' => $code]);

            if ($questionn['type'] === 'options') {
                $question->options()->delete();
                foreach ($questionn['options'] as $option) {
                    $question->options()->create([
                        'text' => $option['text'],
                        'correct' => $option['correct'],
                    ]);
                }
            }

            $questions_create_id[] = $question->id;
        }

        $questions_ids = array_merge($questions_ids, $questions_create_id);

        // dd(
        //     $request->all(),
        //     $quiz_id,
        // );
        if ($type === "1") {

            $quiz = Quiz::create([
                'title' => $request['title'],
                'slug' => $request['slug'],
                'level' => $request['level'],
                'term' => $request['term'],
                'section' => $request['section'],
                'description' => $request['description'],
                'published' => $request['published'] ?? 0,
                'public' => $request['public'] ?? 0,
            ]);
        } else {

            $quiz = Quiz::find($quiz_id);
            if (!$quiz) {
                return response()->json(['error' => 'Quiz not found'], 404);
            }
        }

        $quiz->questions()->sync($questions_ids);
        // dd($request->all());

        return response()->json(['success' => 'Quiz created successfully!']);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }
    public function selectedQuestions(string $id)
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
            'html_questions_select' => $html_questions_select
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
