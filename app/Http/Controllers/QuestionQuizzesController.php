<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
    // public function store(Request $request)
    // {
    //     dd($request->all());

    //     $type = $request->type;
    //     $commonRules = [
    //         'questions' => 'required|array',
    //         'questions.*.text' => 'required|string',
    //         'questions.*.type' => 'required|string|in:options,written',
    //         'questions.*.code_snippet' => 'nullable|string',
    //         'questions.*.answer_explanation' => 'nullable|string',
    //         'questions.*.more_info_link' => 'nullable|string',
    //         'questions.*.written' => 'nullable|string',
    //         'questions.*.options' => 'nullable|array',
    //         'questions.*.options.*.text' => 'nullable|string',
    //         'questions.*.options.*.correct' => 'nullable|boolean',
    //         'questions_id' => 'nullable|array',
    //         'questions_id.*' => 'integer',
    //         'type' => 'required|integer',
    //     ];

    //     if ($type === "1") {
    //         $request->validate(array_merge($commonRules, [
    //             'title' => 'required|string',
    //             'slug' => 'required|string',
    //             'level' => 'required|string',
    //             'term' => 'required|integer',
    //             'section' => 'required|integer',
    //             'description' => 'nullable|string',
    //             // 'published' => 'nullable|boolean',
    //             'public' => 'nullable|boolean',
    //         ]));
    //     } else {
    //         $request->validate($commonRules);
    //     }


    //     $quiz_id = $request->quiz_id;
    //     $questions = $request->questions;

    //     $questions_ids = $request->questions_id ?? [];
    //     $questions_create_id = [];

    //     foreach ($questions as $questionn) {

    //         $question = new Question();
    //         $question->text = $questionn['text'];
    //         $question->type = $questionn['type'];
    //         $question->code_snippet = $questionn['code_snippet'] ?? null;
    //         $question->answer_explanation = $questionn['answer_explanation'] ?? null;
    //         $question->more_info_link = $questionn['more_info_link'] ?? null;
    //         $question->written_answer = $questionn['type'] === 'written' ? $questionn['written'] : null;
    //         $question->save();
    //         $code = str_pad($question->id, 6, '0', STR_PAD_LEFT);
    //         $question->update(['code' => $code]);

    //         if ($questionn['type'] === 'options') {
    //             $question->options()->delete();
    //             foreach ($questionn['options'] as $option) {
    //                 $question->options()->create([
    //                     'text' => $option['text'],
    //                     'correct' => $option['correct'],
    //                 ]);
    //             }
    //         }

    //         $questions_create_id[] = $question->id;
    //     }

    //     $questions_ids = array_merge($questions_ids, $questions_create_id);

    //     // dd(
    //     //     $request->all(),
    //     //     $quiz_id,
    //     // );
    //     if ($type === "1") {

    //         $quiz = Quiz::create([
    //             'title' => $request['title'],
    //             'slug' => $request['slug'],
    //             'level' => $request['level'],
    //             'term' => $request['term'],
    //             'section' => $request['section'],
    //             'description' => $request['description'],
    //             'published' => 0,
    //             'public' => $request['public'] ?? 0,
    //         ]);
    //     } else {

    //         $quiz = Quiz::find($quiz_id);
    //         if (!$quiz) {
    //             return response()->json(['error' => 'Quiz not found'], 404);
    //         }
    //     }

    //     $quiz->questions()->sync($questions_ids);
    //     // dd($request->all());

    //     return response()->json(['success' => 'Quiz created successfully!']);
    // }

    public function store(Request $request)
    {
        try {
            // Validate the incoming request
            // dd($request->all());
            $type = $request->type;

            //แก้ไข validate ให้เป็น array ที่มี key ตามชื่อของ field ที่ต้องการ validate
            $rules = [
                'type' => ['required'],
                'questions' => ['required', 'array', 'min:1'],

                // Either text or image (manual check later)
                'questions.*.text' => ['nullable', 'string'],
                'questions.*.img_name' => ['nullable', 'image', 'file', 'mimes:jpg,png,jpeg', 'max:2048'],
                'questions.*.answer_explanation_image' => ['nullable', 'image', 'file', 'mimes:jpg,png,jpeg', 'max:2048'],

                'questions.*.code_snippet' => ['nullable', 'string'],
                'questions.*.answer_explanation' => ['nullable', 'string'],
                'questions.*.more_info_link' => ['nullable', 'url'],

                // Options for multiple choice
                'questions' => ['required', 'array', 'min:1'],
                // Image options
                'questions.*.imgOptions' => ['required_if:questions.*.type,image', 'array', 'min:1'],
                'questions.*.imgOptions.*.file' => ['required', 'image'],
                'questions.*.imgOptions.*.correct' => ['required', 'boolean'],
            ];

            $messages = [
                'type.required' => 'Quiz type is required.',
                'questions.required' => 'At least one question is required.',
                'questions.*.options.*.correct.required' => 'Each option must have a correct/incorrect status.',
                'questions.*.imgOptions.*.file.required' => 'Image file is required for image options.',
                'questions.*.imgOptions.*.correct.required' => 'Correct answer status is required for image options.',
                'questions.*.answer_numerator.required' => 'Numerator is required for fraction answer.',
                'questions.*.answer_denominator.required' => 'Denominator is required for fraction answer.',
                'questions.*.answer_denominator.not_in' => 'Denominator cannot be zero.',
                'title.required' => 'Quiz title is required.',
                'slug.required' => 'Quiz slug is required.',
                'slug.unique' => 'Slug must be unique.',
                'level.required' => 'Grade is required.',
                'term.required' => 'Term is required.',
                'section.required' => 'Section is required.',
            ];

            // Additional required fields for 'create' type
            if ($type === 'create') {
                $rules = array_merge($rules, [
                    'title' => ['required', 'string'],
                    'slug' => ['required', 'string', 'unique:quizzes,slug'],
                    'level' => ['required', 'string'],
                    'term' => ['required', 'string'],
                    'section' => ['required', 'string'],
                    'description' => ['required', 'string'],
                    'public' => ['required', 'boolean'],
                ]);
            }
            foreach ($request->questions as $qIndex => $question) {
                $rules["questions.$qIndex.type"] = ['required', 'string', 'in:options,written,image,fraction'];
                $rules["questions.$qIndex.level"] = ['required', 'string'];
                $rules["questions.$qIndex.term"] = ['required', 'string'];
                $rules["questions.$qIndex.section"] = ['required', 'string'];
                $messages["questions.$qIndex.type.required"] = 'questions : [' . ($qIndex + 1) . '] — type is required.';
                $messages["questions.$qIndex.level.required"] = 'questions : [' . ($qIndex + 1) . '] — level is required.';
                $messages["questions.$qIndex.term.required"] = 'questions : [' . ($qIndex + 1) . '] — term is required.';
                $messages["questions.$qIndex.section.required"] = 'questions : [' . ($qIndex + 1) . '] — section is required.';
            }


            $validator = Validator::make($request->all(), $rules, $messages);
            // Build validator
            // dd($validator);
            // Add custom logic: must have either text or img_name
            $validator->after(function ($validator) use ($request) {
                // dd($request->all());

                foreach ($request->questions as $qIndex => $question) {
                    // dd($request->all(),$question);
                    $text = $question['text'] ?? null;
                    $img = $question['img_name'] ?? null;
                    // Require text or image
                    $messages['questions.' . $qIndex . '.type.required'] = 'questions : [' . $qIndex + 1 . '] — type is required.';

                    if (empty($text) && empty($img)) {
                        $validator->errors()->add("questions.$qIndex.text", 'questions : [' . $qIndex + 1 . '] — Either text or image is required.');
                        // $validator->errors()->add("questions.$qIndex.img_name", 'questions : ' . $qIndex + 1 . 'Either image or text is required.');
                    }

                    // Validate options for 'options' type questions

                }
            });

            $validator->validate();

            foreach ($request->questions as $index => $question) {

                switch ($question['type']) {
                    case 'written':
                        $rules = [
                            "questions.$index.written" => 'required|string',
                        ];
                        $messages = [
                            "questions.$index.written.required" => 'Question: [' . ($index + 1) . '] — Please provide the written answer.',
                        ];
                        $validator = Validator::make($request->all(), $rules, $messages);
                        if ($validator->fails()) {
                            throw new \Illuminate\Validation\ValidationException($validator);
                        }
                        break;

                    case 'options':
                        $rules = [
                            "questions.$index.options" => 'required|array|min:1',
                        ];
                        $messages = [
                            "questions.$index.options.required" => 'Question: [' . ($index + 1) . '] — Please add Options.',
                        ];

                        $options = $question['options'] ?? [];
                        foreach ($options as $oIndex => $option) {
                            $rules["questions.$index.options.$oIndex.text"] = 'required|string';
                            $rules["questions.$index.options.$oIndex.correct"] = 'required|in:0,1';
                            $messages["questions.$index.options.$oIndex.text.required"] = 'Question: [' . ($index + 1) . '] Option: [' . ($oIndex + 1) . '] — Each option must have text.';
                            $messages["questions.$index.options.$oIndex.correct.required"] = 'Question: [' . ($index + 1) . '] Option: [' . ($oIndex + 1) . '] — Please specify if the option is correct.';
                        }

                        $validator = Validator::make($request->all(), $rules, $messages);
                        $validator->after(function ($validator) use ($options, $index) {
                            $correctCount = collect($options)->where('correct', 1)->count();
                            if ($correctCount === 0) {
                                $validator->errors()->add(
                                    "questions.$index.options",
                                    'Question: [' . ($index + 1) . '] — At least one option must be marked correct.'
                                );
                            }
                        });
                        if ($validator->fails()) {
                            throw new \Illuminate\Validation\ValidationException($validator);
                        }
                        break;

                    case 'image':
                        $rules = [
                            "questions.$index.imgOptions" => 'required|array|min:1',
                        ];
                        $messages = [
                            "questions.$index.imgOptions.required" => 'Question: [' . ($index + 1) . '] — Please add Image Options.',
                        ];

                        $imgOptions = $question['imgOptions'] ?? [];
                        foreach ($imgOptions as $oIndex => $option) {
                            $rules["questions.$index.imgOptions.$oIndex.file"] = 'required|file|mimes:jpg,jpeg,png|max:1024';
                            $rules["questions.$index.imgOptions.$oIndex.correct"] = 'required|in:0,1';
                            $messages["questions.$index.imgOptions.$oIndex.file.required"] = 'Question: [' . ($index + 1) . '] Image Option: [' . ($oIndex + 1) . '] — Please upload an image file.';
                            $messages["questions.$index.imgOptions.$oIndex.file.mimes"] = 'Question: [' . ($index + 1) . '] Image Option: [' . ($oIndex + 1) . '] — Invalid image type.';
                            $messages["questions.$index.imgOptions.$oIndex.file.max"] = 'Question: [' . ($index + 1) . '] Image Option: [' . ($oIndex + 1) . '] — File too large.';
                            $messages["questions.$index.imgOptions.$oIndex.correct.required"] = 'Question: [' . ($index + 1) . '] — Please specify if the image is correct.';
                        }

                        $validator = Validator::make($request->all(), $rules, $messages);
                        $validator->after(function ($validator) use ($imgOptions, $index) {
                            $correctCount = collect($imgOptions)->where('correct', 1)->count();
                            if ($correctCount === 0) {
                                $validator->errors()->add(
                                    "questions.$index.imgOptions",
                                    'Question: [' . ($index + 1) . '] — At least one image must be marked correct.'
                                );
                            }
                        });
                        if ($validator->fails()) {
                            throw new \Illuminate\Validation\ValidationException($validator);
                        }
                        break;

                    case 'fraction':

                        $fractionType = $question['answer_type_fraction'] ?? null;

                        $rules = [
                            "questions.$index.answer_type_fraction" => 'required|in:written,options',
                        ];
                        $messages = [
                            "questions.$index.answer_type_fraction.required" => 'Question: [' . ($index + 1) . '] — Please select Type Answer for Fraction.',
                            "questions.$index.answer_type_fraction.in" => 'Question: [' . ($index + 1) . '] — Invalid Type Answer for Fraction.',
                        ];

                        if ($fractionType === 'written') {
                            $rules += [
                                "questions.$index.answer_numerator" => 'required|integer',
                                "questions.$index.answer_denominator" => 'required|integer|min:1',
                                "questions.$index.answer_type" => 'required|in:frac,mixed',
                            ];
                            $messages += [
                                "questions.$index.answer_numerator.required" => 'Question: [' . ($index + 1) . '] — Numerator is required.',
                                "questions.$index.answer_denominator.required" => 'Question: [' . ($index + 1) . '] — Denominator is required.',
                                "questions.$index.answer_denominator.min" => 'Question: [' . ($index + 1) . '] — Denominator must be at least 1.',
                                "questions.$index.answer_type.required" => 'Question: [' . ($index + 1) . '] — Answer type is required.',
                            ];
                            $validator = Validator::make($request->all(), $rules, $messages);
                            if ($validator->fails()) {
                                throw new \Illuminate\Validation\ValidationException($validator);
                            }
                        } elseif ($fractionType === 'options') {
                            $options = $question['options'] ?? [];
                            $rules["questions.$index.options"] = 'required|array|min:1';
                            $messages["questions.$index.options.required"] = 'Question: [' . ($index + 1) . '] — Please add fraction options.';
                            foreach ($options as $oIndex => $option) {
                                $rules["questions.$index.options.$oIndex.answer_numerator"] = 'required|integer';
                                $rules["questions.$index.options.$oIndex.answer_denominator"] = 'required|integer|min:1';
                                $rules["questions.$index.options.$oIndex.answer_type"] = 'required|in:frac,mixed';
                                $rules["questions.$index.options.$oIndex.correct"] = 'required|in:0,1';

                                $messages["questions.$index.options.$oIndex.answer_numerator.required"] = 'Question: [' . ($index + 1) . '] Option: [' . ($oIndex + 1) . '] — Numerator required.';
                                $messages["questions.$index.options.$oIndex.answer_denominator.required"] = 'Question: [' . ($index + 1) . '] Option: [' . ($oIndex + 1) . '] — Denominator required.';
                                $messages["questions.$index.options.$oIndex.correct.required"] = 'Question: [' . ($index + 1) . '] Option: [' . ($oIndex + 1) . '] — Mark correct/incorrect.';
                            }

                            $validator = Validator::make($request->all(), $rules, $messages);
                            $validator->after(function ($validator) use ($options, $index) {
                                $correctCount = collect($options)->where('correct', 1)->count();
                                if ($correctCount === 0) {
                                    $validator->errors()->add(
                                        "questions.$index.options",
                                        'Question: [' . ($index + 1) . '] — At least one correct fraction option is required.'
                                    );
                                }
                            });
                            if ($validator->fails()) {
                                throw new \Illuminate\Validation\ValidationException($validator);
                            }
                        } else {
                            $validator = Validator::make($request->all(), $rules, $messages);
                            if ($validator->fails()) {
                                throw new \Illuminate\Validation\ValidationException($validator);
                            }
                        }

                        break;
                }
            }

            $quiz_id = $request->quiz_id;
            $questions_ids = $request->questions_id ?? [];
            $questions_create_id = [];
            // dd($request->all());
            $questions = $request->questions;

            foreach ($questions as $questionn) {
                $question = new Question();
                $question->text = $questionn['text'];
                $question->level = $questionn['level'];
                $question->term = $questionn['term'];
                $question->section = $questionn['section'];
                $question->type = $questionn['type'];
                $question->code_snippet = $questionn['code_snippet'] ?? null;
                $question->answer_explanation = $questionn['answer_explanation'] ?? null;
                $question->more_info_link = $questionn['more_info_link'] ?? null;
                $question->written_answer = $questionn['type'] === 'written' ? $questionn['written'] : null;
                $question->created_by = Auth::user()->id;
                $question->save();
                $code = str_pad($question->id, 6, '0', STR_PAD_LEFT);
                $question->update(['code' => $code]);

                if (isset($questionn['img_name']) && $questionn['img_name'] instanceof \Illuminate\Http\UploadedFile) {
                    $imgName = $questionn['img_name'];
                    if ($imgName instanceof \Illuminate\Http\UploadedFile) {
                        $fileName = time() . '_' . uniqid() . '.' . $imgName->getClientOriginalExtension();
                        $imgName->move(public_path('img_questions'), $fileName);
                        $question->update(['img_name' => $fileName]);
                    }
                }

                if (isset($questionn['answer_explanation_image']) && $questionn['answer_explanation_image'] instanceof \Illuminate\Http\UploadedFile) {
                    $answerExplanationImage = $questionn['answer_explanation_image'];
                    if ($answerExplanationImage instanceof \Illuminate\Http\UploadedFile) {
                        $fileName = time() . '_' . uniqid() . '.' . $answerExplanationImage->getClientOriginalExtension();
                        $answerExplanationImage->move(public_path('img_questions'), $fileName);
                        $question->update(['answer_explanation_image' => $fileName]);
                    }
                }

                if ($questionn['type'] === 'options') {
                    $question->options()->delete();
                    foreach ($questionn['options'] as $option) {
                        $question->options()->create([
                            'text' => $option['text'],
                            'correct' => $option['correct'],
                        ]);
                    }
                } elseif ($questionn['type'] === 'image') {
                    $question->images()->delete();
                    foreach ($questionn['imgOptions'] as $index => $imgOption) {
                        $path = null;
                        if (isset($imgOption['file']) && $imgOption['file'] instanceof \Illuminate\Http\UploadedFile) {

                            $fileName = time() . '_' . uniqid() . '.' . $imgOption['file']->getClientOriginalExtension();

                            $filePath = $imgOption['file']->move(public_path('images_options'), $fileName);

                            $question->images()->create([
                                'img_name' => $fileName,
                                'correct' => $imgOption['correct'],
                            ]);
                        }
                    }
                } elseif (($questionn['type']) === 'fraction') {
                    $question->fractions()->delete();


                    if (($questionn['answer_type_fraction']) === 'written') {
                        $question->fractions()->create([
                            'numerator' => $questionn['answer_numerator'] ?? null,
                            'denominator' => $questionn['answer_denominator'] ?? null,
                            'type' => $questionn['answer_type_fraction'] ?? null,
                            'answer_type' => $questionn['answer_type'] ?? 'mixed',
                            'correct' => $questionn['correct'] ?? 0,
                        ]);
                    } else {
                        if (!empty($questionn['options'])) {
                            foreach ($questionn['options'] as $fraction) {
                                $question->fractions()->create([
                                    'type' => $questionn['answer_type_fraction'] ?? null,
                                    'numerator' => $fraction['answer_numerator'] ?? null,
                                    'denominator' => $fraction['answer_denominator'] ?? null,
                                    'answer_type' => $fraction['answer_type'] ?? 'mixed',
                                    'correct' => $fraction['correct'] ?? 0,
                                ]);
                            }
                        }
                    }
                }

                $questions_create_id[] = $question->id;
            }

            $questions_ids = array_merge($questions_ids, $questions_create_id);

            if ($type === "create") {
                $quiz = Quiz::create([
                    'title' => $request['title'],
                    'slug' => $request['slug'],
                    'level' => $request['level'],
                    'term' => $request['term'],
                    'section' => $request['section'],
                    'description' => $request['description'],
                    'published' => 0,
                    'public' => $request['public'] ?? 0,
                    'created_by' => Auth::user()->id,
                ]);
            } else {
                $quiz = Quiz::find($quiz_id);
                if (!$quiz) {
                    return response()->json(['errors' => 'Quiz not found'], 404);
                }
            }

            $quiz->questions()->syncWithoutDetaching($questions_ids);

            return response()->json(['success' => 'Quiz created successfully!']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'errors' => $e->errors(),
                'message' => 'Validation failed'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while creating the quiz',
                'message' => $e->getMessage(),
            ], 500);
        }
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

        $quizLevel = $quiz->level;
        $quizTerm = $quiz->term;
        $quizSection = $quiz->section;
        $html_questions_select = '';
        foreach ($questions_all as $question) {
            $select_question = $questions->contains('id', $question->id) ? 'selected' : '';
            $html_questions_select .= '<option value="' . $question->id . '" ' . $select_question . '>' . $question->text . '</option>';
        }

        return response()->json([
            'html_questions_select' => $html_questions_select,
            'quizLevel' => $quizLevel,
            'quizTerm' => $quizTerm,
            'quizSection' => $quizSection,
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
