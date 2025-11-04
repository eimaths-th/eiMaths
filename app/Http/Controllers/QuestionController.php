<?php

namespace App\Http\Controllers;

use App\Models\Option;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function replaceMixedFractions($text)
    {
        return preg_replace_callback('/(MixFrac|Frac)\((\d+)(?:,\s*(\d+))?(?:,\s*(\d+))?\)/', function ($matches) {

            $function = $matches[1]; // "MixFrac" or "Frac"
            $whole = null;

            if ($function === 'MixFrac') {
                if (isset($matches[4])) {
                    // MixFrac(whole, num, denom)
                    $whole = (int)$matches[2];
                    $numerator = (int)$matches[3];
                    $denominator = (int)$matches[4];
                } else {
                    // Fallback if only 2 numbers
                    $numerator = (int)$matches[2];
                    $denominator = (int)$matches[3];
                }
                return toMixedFraction($numerator, $denominator,$whole);
            } else {
                // Frac(num, denom)
                $numerator = (int)$matches[2];
                $denominator = (int)$matches[3];
                return toFraction($numerator, $denominator);
            }
        }, $text);
    }

    public function textFraction($numerator, $denominator)
    {
        // This generates LaTeX syntax for the fraction
        return "\\(\\frac{{$numerator}}{{$denominator}}\\)";
    }
    public function toFraction($numerator, $denominator)
    {
        if ($numerator % $denominator == 0) {
            return (string) ($numerator / $denominator); // Whole number
        }
        return "\(\\frac{{$numerator}}{{$denominator}}\)";
    }

    function toMixedFraction($numerator, $denominator, $whole = null)
    {

        if ($whole) {
            $remainder = $numerator % $denominator;
            $whole = $whole + intdiv($numerator, $denominator);
        } else {

            if ($numerator % $denominator == 0) {
                return (string) ($numerator / $denominator); // Whole number
            }

            $whole = intdiv($numerator, $denominator);
            $remainder = $numerator % $denominator;
        }

        return $whole > 0
            ? "\({$whole} \\frac{{$remainder}}{{$denominator}}\)"
            : "\(\\frac{{$remainder}}{{$denominator}}\)";
    }
    public function index(Request $request)
    {
        $check = $request->check;
        $grade = $request->grade;
        $term = $request->term;
        $section = $request->section;

        if ($request->ajax()) {
            // Start with base query
            if ($check) {
                $datas = Question::where('created_by', Auth::user()->id);
            } else {
                $datas = Question::query();
            }

            // Apply filters if provided
            if (!empty($grade)) {
                $datas->where('level', $grade);
            }

            if (!empty($term)) {
                $datas->where('term', $term);
            }

            if (!empty($section)) {
                $datas->where('section', $section);
            }

            // Do not force default ordering here; let DataTables handle per-column ordering

            return datatables()->eloquent($datas)
                ->addColumn('#', function ($row) {
                    return $row->id;
                })
                ->editColumn('text', function ($row) {
                    Log::info("Original Text: " . $row->text);
                    $textFrac = replaceMixedFractions($row->text);
                    Log::info("Processed Text: " . $textFrac);
                    return $textFrac;
                })
                ->addColumn('option', function ($row) {
                    if ($row->type === 'written') {
                        # code...
                        return replaceMixedFractions($row->written_answer);
                    } elseif ($row->type === 'image') {
                        # code...
                        return $row->images()->count();
                    } elseif ($row->type === 'fraction') {
                        $fraction = $row->fractions()->first(); // Get the first fraction record

                        if ($fraction) {
                            if ($fraction->type === 'written') {
                                if ($fraction->answer_type === 'frac') {
                                    return toFraction($fraction->numerator, $fraction->denominator);
                                } else {
                                    return toMixedFraction($fraction->numerator, $fraction->denominator,$fraction->whole ?? null);
                                }
                                return $fraction->numerator . '/' . $fraction->denominator;
                            } else {
                                return $row->fractions()->count(); // Count all fraction options
                            }
                        }

                        return 'N/A'; // Handle case where no fraction data exists
                    } else {

                        return $row->options()->count();
                    }
                })
                // prevent searching on computed columns that don't directly map to DB fields
                ->filterColumn('option', function ($query, $keyword) {
                    return $query; // ignore search on computed 'option'
                })
                ->addColumn('type_option', function ($row) {
                    return '';
                })
                ->addColumn('action', function ($row) {
                    // $html = '<a href="' . route('questions.edit', $row->id) . '" class="btn btn-sm btn-warning" id="getEditData" data-id="' . $row->id . '"><<i class="fa fa-edit"></i> Edit</a>';
                    $html = '<button type="button" class="btn btn-sm btn-warning" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</a>';
                    $html .= '<button type="button" class="btn btn-sm btn-danger delete-button" style="margin-left: 5px;" data-id="' . $row->id . '"><i class="fa fa-trash"></i> Delete</button>';
                    return $html;
                })
                ->rawColumns(['action', 'text'])
                ->toJson();
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
        // Validate the base fields
        // dd($request->all());
        $validated = Validator::make($request->all(), [
            'text' => 'nullable|string',
            'img_name' => 'nullable|image|file|mimes:jpg,png,jpeg|max:2048',
            'level' => 'required|string',
            'term' => 'required|string',
            'section' => 'required|string',
            'type' => 'required|in:written,options,image,fraction',
            'code_snippet' => 'nullable|string',
            'answer_explanation' => 'nullable|string',
            'more_info_link' => 'nullable|url',
        ], [
            'level.required' => 'Please add Question grade',
            'term.required' => 'Please add Question term',
            'section.required' => 'Please add Question level',
            'type.required' => 'Please select the type of answer',
            'type.in' => 'The selected type is invalid',
            'img_name.image' => 'The file must be an image',
            'img_name.mimes' => 'The image must be a file of type: jpg, png, jpeg',
            'img_name.max' => 'The image may not be greater than 2MB',
        ])->after(function ($validator) use ($request) {
            if (empty($request->text) && !$request->hasFile('img_name')) {
                $validator->errors()->add('text', 'Either question text or an image is required.');
                $validator->errors()->add('img_name', 'Either an image or question text is required.');
            }
        });

        $validated->validate();
        $validatedData = $validated->validated();

        // Additional validations based on type
        switch ($validatedData['type']) {
            case 'written':
                $request->validate([
                    'written_answer' => 'required',
                ], [
                    'written_answer.required' => 'Please provide the written answer',
                ]);
                break;

            case 'options':
                $request->validate([
                    'options' => 'required|array',
                    'options.*.text' => 'required|string',
                    'options.*.correct' => 'required|in:0,1',
                ], [
                    'options.required' => 'Please add Options',
                    'options.*.text.required' => 'Please add Options Text',
                    'options.*.correct.required' => 'Please specify if the option is correct',
                    'options.*.correct.in' => 'The selected option correctness is invalid',
                ]);

                $correctCount = collect($request->options)->where('correct', 1)->count();
                if ($correctCount === 0) {
                    return response()->json(['errors' => ['options' => ['Please add at least one correct option']]], 422);
                }
                break;

            case 'image':
                $request->validate([
                    'imgOptions' => 'required|array',
                    'imgOptions.*.file' => 'required|file|mimes:jpg,png,jpeg|max:5120',
                    'imgOptions.*.correct' => 'required|in:0,1',
                ], [
                    'imgOptions.required' => 'Please add Image Options',
                    'imgOptions.*.file.required' => 'Please upload an image file',
                    'imgOptions.*.file.mimes' => 'Only jpg, png, and jpeg files are allowed',
                    'imgOptions.*.file.max' => 'Each image must not exceed 5 MB in size',
                    'imgOptions.*.correct.required' => 'Please specify if the image option is correct',
                ]);

                $correctCount = collect($request->imgOptions)->where('correct', 1)->count();
                if ($correctCount === 0) {
                    return response()->json(['errors' => ['imgOptions' => ['Please add at least one correct image option']]], 422);
                }
                break;

            case 'fraction':
                $rules = ['fraction_type' => 'required|in:written,options'];
                $messages = [
                    'fraction_type.required' => 'Please select a fraction type.',
                    'fraction_type.in' => 'Invalid fraction type.',
                ];

                if ($request->fraction_type === 'written') {
                    $rules += [
                        'numerator' => 'required|integer',
                        'denominator' => 'required|integer|min:1',
                        'answer_type' => 'required|in:frac,mixed',
                    ];
                    $messages += [
                        'numerator.required' => 'Numerator is required.',
                        'numerator.integer' => 'Numerator must be an integer.',
                        'denominator.required' => 'Denominator is required.',
                        'denominator.integer' => 'Denominator must be an integer.',
                        'denominator.min' => 'Denominator must be at least 1.',
                        'answer_type.required' => 'Answer type is required.',
                        'answer_type.in' => 'Answer type must be "frac" or "mixed".',
                    ];
                } else {
                    $rules += [
                        'options' => 'required|array|min:1',
                        'options.*.numerator' => 'required|integer',
                        'options.*.denominator' => 'required|integer|min:1',
                        'options.*.answer_type' => 'required|in:frac,mixed',
                        'options.*.correct' => 'required|boolean',
                    ];
                    $messages += [
                        'options.required' => 'Please provide options.',
                        'options.*.numerator.required' => 'Numerator is required for each option.',
                        'options.*.denominator.required' => 'Denominator is required for each option.',
                        'options.*.denominator.min' => 'Denominator must be at least 1.',
                        'options.*.answer_type.required' => 'Answer type is required for each option.',
                        'options.*.correct.required' => 'Each option must specify if it is correct.',
                    ];
                }

                $request->validate($rules, $messages);

                if ($request->fraction_type === 'options') {
                    $correctCount = collect($request->options)->where('correct', 1)->count();
                    if ($correctCount === 0) {
                        return response()->json([
                            'errors' => ['options' => ['Please provide at least one correct fraction option.']]
                        ], 422);
                    }
                }
                break;
        }

        // Store question
        $question = new Question();
        $question->text = $validatedData['text'] ?? null;
        $question->type = $validatedData['type'];
        $question->level = $validatedData['level'];
        $question->term = $validatedData['term'];
        $question->section = $validatedData['section'];
        $question->code_snippet = $validatedData['code_snippet'] ?? null;
        $question->answer_explanation = $validatedData['answer_explanation'] ?? null;
        $question->more_info_link = $validatedData['more_info_link'] ?? null;
        $question->written_answer = $validatedData['type'] === 'written' ? $request->written_answer : null;
        $question->created_by = Auth::user()->id;
        $question->save();

        // Generate and save question code
        $question->update(['code' => str_pad($question->id, 6, '0', STR_PAD_LEFT)]);
        
        // Save main image
        if ($request->hasFile('img_name')) {
            $file = $request->file('img_name');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img_questions'), $fileName);
            $question->update(['img_name' => $fileName]);
        }

        // Save answer explanation image
        if ($request->hasFile('answer_explanation_image')) {
            $file = $request->file('answer_explanation_image');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img_questions'), $fileName);
            $question->update(['answer_explanation_image' => $fileName]);
        }

        // Store answer options
        if ($validatedData['type'] === 'options') {
            foreach ($request->options as $option) {
                $question->options()->create([
                    'text' => $option['text'],
                    'correct' => $option['correct'],
                ]);
            }
        }

        // Store image options
        if ($validatedData['type'] === 'image') {
            foreach ($request->imgOptions as $imgOption) {
                if (isset($imgOption['file']) && $imgOption['file'] instanceof \Illuminate\Http\UploadedFile) {
                    $fileName = time() . '_' . uniqid() . '.' . $imgOption['file']->getClientOriginalExtension();
                    $imgOption['file']->move(public_path('images_options'), $fileName);
                    $question->images()->create([
                        'img_name' => $fileName,
                        'correct' => $imgOption['correct'],
                    ]);
                }
            }
        }

        // Store fractions
        if ($validatedData['type'] === 'fraction') {
            if ($request->fraction_type === 'written') {
                $question->fractions()->create([
                    'type' => 'written',
                    'numerator' => $request->numerator,
                    'denominator' => $request->denominator,
                    'answer_type' => $request->answer_type,
                ]);
            } else {
                foreach ($request->options as $option) {
                    $question->fractions()->create([
                        'type' => 'options',
                        'numerator' => $option['numerator'],
                        'denominator' => $option['denominator'],
                        'answer_type' => $option['answer_type'],
                        'correct' => $option['correct'],
                    ]);
                }
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
        $image_options = $question->images; // Assuming you have a relationship defined
        $fractions = $question->fractions; // Assuming you have a relationship defined
        if ($question->type === 'fraction') {
            $fractionsTypeAnswer = $question->fractions->first()->type; // Assuming you have a relationship defined
        } else {
            $fractionsTypeAnswer = null;
        }
        return response()->json([
            'fractionsTypeAnswer' => $fractionsTypeAnswer,
            'question' => $question,
            'options' => $options,
            'fractions' => $fractions,
            'image_options' => $image_options->map(function ($image) {
                return [
                    'file_id' => $image->id,
                    'file_name' => $image->img_name,
                    'file_path' => asset('images_options/' . $image->img_name),
                    'correct' => $image->correct,
                ];
            }),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        
        $validated = Validator::make($request->all(), [
            'text' => 'nullable|string',
            'img_name' => 'nullable|image|file|mimes:jpg,png,jpeg|max:2048',
            'level' => 'required|string',
            'term' => 'required|string',
            'section' => 'required|string',
            'type' => 'required|in:written,options,image,fraction',
            'code_snippet' => 'nullable|string',
            'answer_explanation' => 'nullable|string',
            'more_info_link' => 'nullable|url',
        ], [
            'level.required' => 'Please add Question grade',
            'term.required' => 'Please add Question term',
            'section.required' => 'Please add Question level',
            'type.required' => 'Please select the type of answer',
            'type.in' => 'The selected type is invalid',
            'img_name.image' => 'The file must be an image',
            'img_name.mimes' => 'The image must be a file of type: jpg, png, jpeg',
            'img_name.max' => 'The image may not be greater than 2MB',
        ])->after(function ($validator) use ($request) {
            $text = $request->input('text');
            $old_img_name = $request->input('old_img_name');
            $img = $request->file('img_name');
            if (empty($text) && empty($img) && empty($old_img_name)) {
                $validator->errors()->add('text', 'Either text or image is required.');
                // $validator->errors()->add('old_img_name', 'Either text or image is required.');
                // $validator->errors()->add('img_name', 'Either image or text is required.');
            }
        });

        $validated->validate();
        $validatedData = $validated->validated();

        if ($validatedData['type'] === 'options') {
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
        } else if ($validatedData['type'] === 'written') {
            $validatedWritten = $request->validate([
                'written_answer' => 'required',
            ], [
                'written_answer.required' => 'Please provide the written answer',
            ]);
        } elseif ($validatedData['type'] === 'image') {
            if ($request->has('imgOptions')) {
                // Validate when 'imgOptions' is present
                $validatedImageOptions = $request->validate([
                    'imgOptions' => 'required|array',
                    'imgOptions.*.file' => 'required|file|mimes:jpg,png,jpeg|max:5120', // 5 MB = 5120 KB
                    'imgOptions.*.correct' => 'required|in:0,1',
                    'imgOptionsShow.*.correct' => 'required|in:0,1',
                ], [
                    'imgOptions.required' => 'Please add Image Options',
                    'imgOptions.*.file.required' => 'Please upload an image file',
                    'imgOptions.*.file.mimes' => 'Only jpg, png, and jpeg files are allowed',
                    'imgOptions.*.file.max' => 'Each image must not exceed 1 MB in size',
                    'imgOptions.*.correct.required' => 'Please specify if the image option is correct',
                    'imgOptions.*.correct.in' => 'The selected option correctness is invalid',

                    'imgOptionsShow.*.correct.required' => 'Please specify if the image option is correct',
                    'imgOptionsShow.*.correct.in' => 'The selected option correctness is invalid',
                ]);
            } else {
                // Validate when 'imgOptions' is not present
                $validatedImageOptions = $request->validate([
                    'imgOptionsShow' => 'required|array',
                    // 'imgOptionsShow.*.file_id' => 'required|integer|exists:image_options,id', // Ensure file_id exists
                    'imgOptionsShow.*.correct' => 'required|in:0,1',
                ], [

                    'imgOptionsShow.*.correct.required' => 'Please specify if the image option is correct',
                    'imgOptionsShow.*.correct.in' => 'The selected option correctness is invalid',
                ]);
            }

            $validateCorrects = array_merge($request->imgOptions ?? [], $request->imgOptionsShow ?? []);
            $countCorrect = 0;

            foreach ($validateCorrects as $validateCorrect) {
                if (isset($validateCorrect['correct']) && $validateCorrect['correct'] == 1) {
                    $countCorrect++;
                }
            }

            if ($countCorrect == 0) {
                return response()->json(['errors' => ['Please add at least one correct option']], 422);
            }
        } else if ($validatedData['type'] === 'fraction') {



            $validatedFraction = $request->validate([
                'fraction_type' => 'required|in:written,options',
            ], [
                'fraction_type.required' => 'Please select the type of fraction',
                'fraction_type.in' => 'The selected fraction type is invalid',
            ]);

            if ($validatedFraction['fraction_type'] === 'written') {
                $validatedFractionWritten = $request->validate([
                    'numerator' => 'required',
                    'denominator' => 'required',
                    'answer_type' => 'required|in:frac,mixed',
                ], [
                    'numerator.required' => 'Numerator is required.',
                    'denominator.required' => 'Denominator is required.',
                    'answer_type.required' => 'Answer type is required.',
                    'answer_type.in' => 'Answer type must be "frac" or "mixed".',
                ]);
            } else if ($validatedFraction['fraction_type'] === 'options') {
                $validatedFractionOptions = $request->validate([
                    'options' => 'required|array',
                    'options.*.numerator' => 'required',
                    'options.*.denominator' => 'required',
                    'options.*.answer_type' => 'required|in:frac,mixed',
                ], [
                    'options.required' => 'Please add Fraction Options',
                    'options.*.numerator.required' => 'Please add Fraction Options Text',
                    'options.*.denominator.required' => 'Please specify if the fraction option is correct',
                    'options.*.answer_type.required' => 'The selected option correctness is invalid',
                ]);
            }
        }

        $question = Question::findOrFail($id);

        // dd($request->all());
        // Update the question with basic fields
        $updateData = [
            'text' => $request->input('text'),
            'code_snippet' => $request->input('code_snippet'),
            'term' => $request->input('term'),
            'level' => $request->input('level'),
            'section' => $request->input('section'),
            'answer_explanation' => $request->input('answer_explanation'),
            'more_info_link' => $request->input('more_info_link'),
            'type' => $request->input('type'),
            'updated_by' => Auth::user()->id,
            'written_answer' => $request->input('type') === 'written' ? $request->input('written_answer') : null,
        ];

        // Handle answer explanation image
        $oldAnswerExplanationImage = $question->answer_explanation_image;
        $removeAnswerExplanationImage = $request->input('remove_answer_explanation_image') === '1';

        // 1. If user uploads a new answer explanation image
        if ($request->hasFile('answer_explanation_image')) {
            // Delete old answer explanation image if exists
            if ($oldAnswerExplanationImage && file_exists(public_path('img_questions/' . $oldAnswerExplanationImage))) {
                unlink(public_path('img_questions/' . $oldAnswerExplanationImage));
            }

            $file = $request->file('answer_explanation_image');
            $fileName = 'explanation_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('img_questions'), $fileName);
            $updateData['answer_explanation_image'] = $fileName;
        }
        // 2. If user removes the existing answer explanation image
        elseif ($removeAnswerExplanationImage && $oldAnswerExplanationImage) {
            // Delete the file
            if (file_exists(public_path('img_questions/' . $oldAnswerExplanationImage))) {
                unlink(public_path('img_questions/' . $oldAnswerExplanationImage));
            }
            $updateData['answer_explanation_image'] = null;
        }
        // 3. If no new image uploaded but user kept the old one
        elseif ($oldAnswerExplanationImage && !$removeAnswerExplanationImage) {
            $updateData['answer_explanation_image'] = $oldAnswerExplanationImage;
        }
        // 4. No action needed if there was no image and none is being added

        // Update the question with all fields
        $question->update($updateData);
        $oldImgName = $request->input('old_img_name');

        // 1. If user uploads a new image
        if ($request->hasFile('img_name')) {
            // Delete old one if exists
            if ($oldImgName && file_exists(public_path('img_questions/' . $oldImgName))) {
                unlink(public_path('img_questions/' . $oldImgName));
            }

            $img = $request->file('img_name');
            $fileName = time() . '_' . uniqid() . '.' . $img->getClientOriginalExtension();
            $img->move(public_path('img_questions'), $fileName);

            $question->img_name = $fileName;
        }

        // 2. If no new image uploaded but user kept the old one
        elseif ($oldImgName) {
            $question->img_name = $oldImgName;
        }

        // 3. If image removed
        else {
            // Delete file if exists
            if ($question->img_name && file_exists(public_path('img_questions/' . $question->img_name))) {
                unlink(public_path('img_questions/' . $question->img_name));
            }
            $question->img_name = null;
        }

        $question->save();

        // dd('no');

        if ($request->input('type') === 'options') {
            $question->images()->delete();
            $question->fractions()->delete();
            $question->options()->delete();
            $question->written_answer = null;
            foreach ($request->input('options') as $option) {
                $question->options()->create([
                    'text' => $option['text'],
                    'correct' => $option['correct'],
                ]);
            }
        } elseif ($request->input('type') === 'written') {
            $question->options()->delete();
            $question->images()->delete();
            $question->fractions()->delete();
        } elseif ($request->input('type') === 'image') {

            $question->options()->delete();
            $question->fractions()->delete();
            $question->written_answer = null;
            $fileIds = collect($request->input('imgOptionsShow', []))->pluck('file_id')->toArray();

            $imagesToDelete = $question->images()->whereNotIn('id', $fileIds)->get();

            foreach ($imagesToDelete as $image) {
                $filePath = public_path('images_options/' . $image->img_name);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $question->images()->whereNotIn('id', $fileIds)->delete();

            foreach ($request->input('imgOptionsShow', []) as $imgOption) {
                if (isset($imgOption['file_id'])) {
                    $question->images()->where('id', $imgOption['file_id'])->update(['correct' => $imgOption['correct']]);
                }
            }

            if ($request->has('imgOptions')) {
                foreach ($validatedImageOptions['imgOptions'] as $imgOption) {
                    if (isset($imgOption['file']) && $imgOption['file'] instanceof \Illuminate\Http\UploadedFile) {
                        $fileName = time() . '_' . uniqid() . '.' . $imgOption['file']->getClientOriginalExtension();
                        $filePath = $imgOption['file']->move(public_path('images_options'), $fileName);

                        $question->images()->create([
                            'img_name' => $fileName,
                            'correct' => $imgOption['correct'],
                        ]);
                    }
                }
            }
        } else if ($request->input('type') === 'fraction') {
            $question->written_answer = null;
            $question->images()->delete();
            $question->options()->delete();
            $question->fractions()->delete();
            if ($request->fraction_type === 'written') {
                $question->fractions()->create([
                    'type' => 'written',
                    'numerator' => $request->numerator,
                    'denominator' => $request->denominator,
                    'answer_type' => $request->answer_type,
                ]);
            } else {
                foreach ($request->options as $option) {
                    $question->fractions()->create([
                        'type' => 'options',
                        'numerator' => $option['numerator'],
                        'denominator' => $option['denominator'],
                        'answer_type' => $option['answer_type'],
                        'correct' => $option['correct'],
                    ]);
                }
            }
        }
        // return redirect()->route('questions')->with('success', 'Question updated successfully!');
        return response()->json([
            'success' => 'Question updated successfully!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Retrieve the question or fail
        $question = Question::findOrFail($id);

        // Delete related data based on the type of the question
        if ($question->type === 'options') {
            $question->options()->delete();
        } elseif ($question->type === 'image') {
            $images = $question->images()->get();
            foreach ($images as $image) {
                $filePath = public_path('images_options/' . $image->img_name);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            $question->images()->delete();
        }

        // Delete the question itself
        $question->delete();

        return response()->json(['success' => true, 'message' => 'Delete Question Success']);
    }
}
