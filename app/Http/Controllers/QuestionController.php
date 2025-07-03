<?php

namespace App\Http\Controllers;

use App\Models\Option;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function replaceMixedFractions($text)
    {
        return preg_replace_callback('/(MixFrac|Frac)\((\d+),\s*(\d+)\)/', function ($matches) {
            $function = $matches[1]; // Function name (either toMixedFraction or toFraction)
            $numerator = (int) $matches[2];
            $denominator = (int) $matches[3];

            return $function === 'MixFrac'
                ? $this->toMixedFraction($numerator, $denominator)
                : $this->toFraction($numerator, $denominator);
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

    public function toMixedFraction($numerator, $denominator)
    {
        if ($numerator % $denominator == 0) {
            return (string) ($numerator / $denominator); // Whole number
        }

        $whole = intdiv($numerator, $denominator);
        $remainder = $numerator % $denominator;

        return $whole > 0
            ? "\({$whole} \\frac{{$remainder}}{{$denominator}}\)"
            : "\(\\frac{{$remainder}}{{$denominator}}\)";
    }
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $datas = Question::latest();

            return datatables()->of($datas)
                ->addColumn('#', function ($row) {
                    return $row->id;
                })
                ->addColumn('level', function ($row) {
                    return $row->level;
                })
                ->addColumn('term', function ($row) {
                    return $row->term;
                })
                ->addColumn('section', function ($row) {
                    return $row->section;
                })
                ->addColumn('text', function ($row) {
                    Log::info("Original Text: " . $row->text);
                    $textFrac = replaceMixedFractions($row->text);
                    Log::info("Processed Text: " . $textFrac);
                    return $textFrac;
                })
                ->addColumn('option', function ($row) {
                    if ($row->type === 'written') {
                        # code...
                        return $row->written_answer;
                    } elseif ($row->type === 'image') {
                        # code...
                        return $row->images()->count();
                    } elseif ($row->type === 'fraction') {
                        $fraction = $row->fractions()->first(); // Get the first fraction record

                        if ($fraction) {
                            if ($fraction->type === 'written') {
                                if ($fraction->answer_type === 'frac') {
                                    return $this->toFraction($fraction->numerator, $fraction->denominator);
                                } else {
                                    return $this->toMixedFraction($fraction->numerator, $fraction->denominator);
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
                ->addColumn('type_option', function ($row) {
                    return '';
                })
                ->addColumn('action', function ($row) {
                    // $html = '<a href="' . route('questions.edit', $row->id) . '" class="btn btn-sm btn-warning" id="getEditData" data-id="' . $row->id . '"><<i class="fa fa-edit"></i> Edit</a>';
                    $html = '<button type="button" class="btn btn-sm btn-warning" id="getEditData" data-id="' . $row->id . '"><i class="fa fa-edit"></i> Edit</a>';
                    $html .= '<button type="button" class="btn btn-sm btn-danger delete-button" style="margin-left: 5px;" data-id="' . $row->id . '"><i class="fa fa-trash"></i> Delete</button>';
                    return $html;
                })->rawColumns(['action','text'])->toJson();
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
        // dd($request->all());

        $validated = $request->validate([
            'text' => 'required|string',
            'level' => 'required|string',
            'term' => 'required|string',
            'section' => 'required|string',
            'img_name' => 'nullable|image|file|mimes:jpg,png,jpeg|max:2048',
            'type' => 'required|in:written,options,image,fraction',
            'code_snippet' => 'nullable|string',
            'answer_explanation' => 'nullable|string',
            'more_info_link' => 'nullable|url',
        ], [
            'text.required' => 'Please add Question text',
            'level.required' => 'Please add Question grade',
            'term.required' => 'Please add Question term',
            'section.required' => 'Please add Question lavel',
            'type.required' => 'Please select the type of answer',
            'type.in' => 'The selected type is invalid',
            'code_snippet.required' => 'Please add Code snippet',
            'answer_explanation.required' => 'Please add Answer Explanation',
            'more_info_link.required' => 'Please add More info link',
            'img_name.image' => 'The file must be an image',
            'img_name.mimes' => 'The image must be a file of type: jpg, png, jpeg',
            'img_name.max' => 'The image may not be greater than 2MB',
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
        } elseif ($validated['type'] === 'image') {
            $validatedImageOptions = $request->validate([
                'imgOptions' => 'required|array',
                'imgOptions.*.file' => 'required|file|mimes:jpg,png,jpeg|max:1024', // 1 MB = 1024 KB
                'imgOptions.*.correct' => 'required|in:0,1',
            ], [
                'imgOptions.required' => 'Please add Image Options',
                'imgOptions.*.file.required' => 'Please upload an image file',
                'imgOptions.*.file.mimes' => 'Only jpg, png, and jpeg files are allowed',
                'imgOptions.*.file.max' => 'Each image must not exceed 1 MB in size',
                'imgOptions.*.correct.required' => 'Please specify if the image option is correct',
                'imgOptions.*.correct.in' => 'The selected option correctness is invalid',
            ]);
            $validateCorrects = $request->imgOptions;
            $countCorrect = 0;
            foreach ($validateCorrects as $validateCorrect) {
                if (isset($validateCorrect['correct']) && $validateCorrect['correct'] == 1) {
                    $countCorrect++;
                }
            }

            if ($countCorrect == 0) {
                return response()->json(['errors' => ['Please add at least one correct option']], 422);
            }
        } elseif ($validated['type'] === 'fraction') {
            $rules = [
                'fraction_type' => 'required|in:written,options', // Must be 'written' or 'options'
            ];

            if ($request->fraction_type === 'written') {
                // Validate written fraction
                $rules['numerator'] = 'required|integer';
                $rules['denominator'] = 'required|integer|min:1'; // Denominator cannot be 0
                $rules['answer_type'] = 'required|in:frac,mixed'; // Must be fraction or mixed fraction
            } elseif ($request->fraction_type === 'options') {
                // Validate fraction options
                $rules['options'] = 'required|array|min:1'; // Must have at least one fraction option
                $rules['options.*.numerator'] = 'required|integer';
                $rules['options.*.denominator'] = 'required|integer|min:1';
                $rules['options.*.answer_type'] = 'required|in:frac,mixed';
                $rules['options.*.correct'] = 'required|boolean'; // Correct field should be boolean (1 or 0)
            }

            // Apply validation
            $validatedData = $request->validate($rules);
        }
        // dd($request->all());

        $question = new Question();
        $question->text = $validated['text'];
        $question->type = $validated['type'];
        $question->level = $validated['level'];
        $question->term = $validated['term'];
        $question->section = $validated['section'];
        $question->code_snippet = $validated['code_snippet'] ?? null;
        $question->answer_explanation = $validated['answer_explanation'] ?? null;
        $question->more_info_link = $validated['more_info_link'] ?? null;
        $question->written_answer = $validated['type'] === 'written' ? $validatedWritten['written_answer'] : null;
        $question->save();
        $code = str_pad($question->id, 6, '0', STR_PAD_LEFT);
        $question->update(['code' => $code]);

        if ($request->hasFile('img_name')) {
            $imgName = $request->file('img_name');
            if ($imgName instanceof \Illuminate\Http\UploadedFile) {
                $fileName = time() . '_' . uniqid() . '.' . $imgName->getClientOriginalExtension();
                $imgName->move(public_path('img_questions'), $fileName);
                $question->update(['img_name' => $fileName]);
            }
        }

        // dd($request->all());
        if ($validated['type'] === 'options') {
            $question->options()->delete();
            foreach ($validatedOptions['options'] as $option) {
                $question->options()->create([
                    'text' => $option['text'],
                    'correct' => $option['correct'],
                ]);
            }
        } elseif ($validated['type'] === 'image') {

            $question->images()->delete();

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
        } elseif ($validated['type'] === 'fraction') {

            if ($request->fraction_type === 'written') {
                // Create a new written fraction
                $question->fractions()->create([
                    'type' => $validatedData['fraction_type'],
                    'numerator' => $validatedData['numerator'],
                    'denominator' => $validatedData['denominator'],
                    'answer_type' => $validatedData['answer_type'],
                ]);
            } elseif ($request->fraction_type === 'options') {
                // Create a new fraction options
                foreach ($validatedData['options'] as $option) {
                    $question->fractions()->create([
                        'type' => $validatedData['fraction_type'],

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

        return response()->json([
            'question' => $question,
            'options' => $options,
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
        // dd($request->all());
        $validated = $request->validate([
            'text' => 'required|string',
            'level' => 'required|string',
            'term' => 'required|string',
            'section' => 'required|string',
            'type' => 'required|in:written,options,image',
            'img_name' => 'nullable|image|file|mimes:jpg,png,jpeg|max:2048',
            'code_snippet' => 'nullable',
            'answer_explanation' => 'nullable',
            'more_info_link' => 'nullable|url'
        ], [
            'text.required' => 'Please add Question text',
            'level.required' => 'Please add Question Grade',
            'term.required' => 'Please add Question term',
            'section.required' => 'Please add Question lavel',
            'type.required' => 'Please select the type of answer',
            'type.in' => 'The selected type is invalid',
            'code_snippet.required' => 'Please add Code snippet',
            'answer_explanation.required' => 'Please add Answer Explanation',
            'more_info_link.required' => 'Please add More info link',
            'img_name.image' => 'The file must be an image',
            'img_name.mimes' => 'The image must be a file of type: jpg, png, jpeg',
            'img_name.max' => 'The image may not be greater than 2MB',
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
        } elseif ($validated['type'] === 'image') {
            if ($request->has('imgOptions')) {
                // Validate when 'imgOptions' is present
                $validatedImageOptions = $request->validate([
                    'imgOptions' => 'required|array',
                    'imgOptions.*.file' => 'required|file|mimes:jpg,png,jpeg|max:1024', // 1 MB = 1024 KB
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
        }

        $question = Question::findOrFail($id);


        $question->update([
            'text' => $request->input('text'),
            'code_snippet' => $request->input('code_snippet'),
            'answer_explanation' => $request->input('answer_explanation'),
            'more_info_link' => $request->input('more_info_link'),
            'type' => $validated['type'],
            'written_answer' => $validated['type'] === 'written' ? $validatedWritten['written_answer'] : null,
        ]);
        if ($request->hasFile('img_name')) {
            // Retrieve the existing image file name from the database
            $oldImageName = $question->img_name;

            // Check if the old image exists and delete it
            if ($oldImageName && file_exists(public_path('img_questions/' . $oldImageName))) {
                unlink(public_path('img_questions/' . $oldImageName));
            }

            // Upload the new image
            $imgName = $request->file('img_name');
            if ($imgName instanceof \Illuminate\Http\UploadedFile) {
                $fileName = time() . '_' . uniqid() . '.' . $imgName->getClientOriginalExtension();
                $imgName->move(public_path('img_questions'), $fileName);
                $question->update(['img_name' => $fileName]);
            }
        }

        if ($validated['type'] === 'options') {
            $question->images()->delete();
            $question->options()->delete();
            foreach ($validatedOptions['options'] as $option) {
                $question->options()->create([
                    'text' => $option['text'],
                    'correct' => $option['correct'],
                ]);
            }
        } elseif ($validated['type'] === 'written') {
            $question->options()->delete();
            $question->images()->delete();
        } elseif ($validated['type'] === 'image') {
            $question->options()->delete();

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
        }
        // return redirect()->route('questions')->with('success', 'Question updated successfully!');
        return response()->json(['success' => 'Question updated successfully!']);
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
