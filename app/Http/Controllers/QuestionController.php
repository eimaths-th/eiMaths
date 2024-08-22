<?php

namespace App\Http\Controllers;

use App\Models\Option;
use App\Models\Question;
use Illuminate\Http\Request;


class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
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
        return view('question.qusetion-list');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $editing = false;
        return view('question.question-form', ['editing' => $editing, 'question' => null, 'options' => []]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        // dd($request);
        $validated = $request->validate([
            'text' => 'required|string',
            'type' => 'required|in:written,options',
            'code_snippet' => 'nullable|string',
            'answer_explanation' => 'nullable|string',
            'more_info_link' => 'nullable|url'
        ], [
            'text.required' => 'Please add Question text',
            'type.required' => 'Please select the type of answer',
            'type.in' => 'The selected type is invalid',
            'code_snippet.required' => 'Please add Code snippet',
            'answer_explanation.required' => 'Please add Answer Explanation',
            'more_info_link.required' => 'Please add More info link',
        ]);

        if ($validated['type'] === 'options') {
            $validatedOptions = $request->validate([
                'options' => 'required|array',
                'options.*.text' => 'required|string',
                'options.*.correct' => 'required|in:0,1',
            ], [
                'options.required' => 'Please add Options',
                'options.*.text.required' => 'Please add Options Text',
                'options.*.correct.required' => 'Please specify if the option is correct',
                'options.*.correct.in' => 'The selected option correctness is invalid',
            ]);

            $validateCorrects = $request->options;
            $countCorrect = 0;
            foreach ($validateCorrects as $validateCorrect) {
                if (isset($validateCorrect['correct']) && $validateCorrect['correct'] == 1) {
                    $countCorrect++;
                }
            }

            if ($countCorrect == 0) {
                return response()->json(['errors' => ['Please add at least one correct option']], 422);
            }
        } else if ($validated['type'] === 'written') {
            $validatedWritten = $request->validate([
                'written_answer' => 'required',
            ], [
                'written_answer.required' => 'Please provide the written answer',
            ]);
        }

        // if ($countCorrect == 0) {
        //     return response()->json(['errors' => 'Please add at least one correct option',]);
        // }

        $question = new Question();
        $question->text = $validated['text'];
        $question->type = $validated['type'];
        $question->code_snippet = $validated['code_snippet'] ?? null;
        $question->answer_explanation = $validated['answer_explanation'] ?? null;
        $question->more_info_link = $validated['more_info_link'] ?? null;
        $question->written_answer = $validated['type'] === 'written' ? $validatedWritten['written_answer'] : null;
        $question->save();
        $code = str_pad($question->id, 6, '0', STR_PAD_LEFT);
        $question->update(['code' => $code]);

        if ($validated['type'] === 'options') {
            $question->options()->delete();
            foreach ($validatedOptions['options'] as $option) {
                $question->options()->create([
                    'text' => $option['text'],
                    'correct' => $option['correct'],
                ]);
            }
        }

        return response()->json(['success' => 'Question created successfully!']);
    }

    public function rec()
    {
        $questions = Question::all()->pluck('text', 'id');
        return response()->json(['questions' => $questions]);
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
        $question = Question::findOrFail($id);
        $options = $question->options; // Assuming you have a relationship defined

        return response()->json(['question' => $question, 'options' => $options]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'text' => 'required|string',
            'options' => 'required|array',
            'options.*.text' => 'required|string',
            'options.*.correct' => 'required|in:0,1',
            'code_snippet' => 'required|string',
            'answer_explanation' => 'required|string',
            'more_info_link' => 'required|url'
        ], [
            'text.required' => 'Please add Question text',
            'options.required' => 'Please add Options',
            'options.*.text.required' => 'Please add Options Text',
            'code_snippet.required' => 'Please add Code snippet',
            'answer_explanation.required' => 'Please add Answer Explanation',
            'more_info_link.required' => 'Please add More info link',

        ]);

        $validateCorrects = $request->options;
        $countCorrect = 0;
        foreach ($validateCorrects as $validateCorrect) {
            if (isset($validateCorrect['correct']) && $validateCorrect['correct'] == 1) {
                $countCorrect++;
            }
        }
        if ($countCorrect == 0) {
            return response()->json(['errors' => 'Please add at least one correct option',]);
        }

        $question = Question::findOrFail($id);

        $question->update([
            'text' => $request->input('text'),
            'code_snippet' => $request->input('code_snippet'),
            'answer_explanation' => $request->input('answer_explanation'),
            'more_info_link' => $request->input('more_info_link'),
        ]);

        // $question->text = $request->input('text');
        // $question->code_snippet = $request->input('code_snippet');
        // $question->answer_explanation = $request->input('answer_explanation');
        // $question->more_info_link = $request->input('more_info_link');
        // $question->save();

        $options = $request->options;
        $question->options()->delete();

        foreach ($options as $option) {
            $question->options()->create([
                'text' => $option['text'],
                'correct' => isset($option['correct']) ? $option['correct'] : 0,
            ]);
        }

        // return redirect()->route('questions')->with('success', 'Question updated successfully!');
        return response()->json(['success' => 'Question created successfully!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $question = Question::find($id);
        $question->options()->delete();
        $question->delete();

        return response()->json(['success' => true, 'message' => 'Delete Question Success']);
    }
}
