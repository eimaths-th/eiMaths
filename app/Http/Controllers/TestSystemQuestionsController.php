<?php

namespace App\Http\Controllers;

use App\Models\TestSystemQuestions;
use Illuminate\Http\Request;

class TestSystemQuestionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $questions = TestSystemQuestions::paginate(5); // Paginate to split pages
        return view('question.testq.index', compact('questions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // dd('create');
        return view('question.testq.create');
    }
    public function emptyView()
    {
        // dd('create');
        return view('question.testq.show');
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'question_text' => 'required|string',
            'answer_numerator' => 'required|integer',
            'answer_denominator' => 'required|integer|min:1', // Prevent division by zero
            'answer_type' => 'required|in:frac,mixed',
        ]);

        TestSystemQuestions::create([
            'question_text' => $request->question_text,
            'answer_numerator' => $request->answer_numerator,
            'answer_denominator' => $request->answer_denominator,
            'answer_type' => $request->answer_type,
        ]);

        return redirect()->back()->with('success', 'Question added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }


    public function view()
    {
        $questions = TestSystemQuestions::all();
        $html = '';
        $count = 1; // Manual counter instead of $loop->iteration

        foreach ($questions as $question) {
            $question_text = $this->replaceMixedFractions($question->question_text);
            $html .= "<p>" . $count . ". {$question_text}</p>";
            // $html .= "<p>Answer: " . $this->toMixedFraction($question->answer_numerator, $question->answer_denominator) . "</p>";
            // $html .= "<p>Answer2: " . $this->toFraction($question->answer_numerator, $question->answer_denominator) . "</p>";
            //how to input the fraction answer

            $answer_Numerator = "<input type='text' name='fraction_{$count}_numerator' placeholder='Numerator' style='width: 30px;'>";
            $answer_Denominator = "<input type='text' name='fraction_{$count}_denominator' placeholder='Denominator' style='width: 30px;'>";
            $textTest = 'text';
            $textTest2 = 'test';
            $inputText =  $this->textFraction($textTest, $textTest2);
            // $html .= "test text {$inputText}";

            if ($question->answer_type === 'frac') {
                # code...
                $html .= "
                <p>Enter your answer:</p>
                <!-- {$inputText} <br> <br> -->
                <!--
                    <input type='text' name='mixed_{$count}_whole' placeholder='Whole Number' class='mixed-whole' />
                -->
                <div class='frac'>
                    <span>
                        <input type='text' name='fraction_{$count}_numerator' placeholder='Numerator' class='fraction-numerator'>
                    </span>
                    <span class='symbol'>/</span>
                    <span class='bottom'>  
                        <input type='text' name='fraction_{$count}_denominator' placeholder='Denominator' class='fraction-denominator'>
                    </span>
                </div>
                <br><br>
            ";
            } else {
                # code...
                $html .= "
                <p>Enter your answer:</p>
                <!-- {$inputText} <br> <br> -->
                <input type='text' name='mixed_{$count}_whole' placeholder='Whole Number' class='mixed-whole' />
                <div class='frac'>
                    <span>
                        <input type='text' name='fraction_{$count}_numerator' placeholder='Numerator' class='fraction-numerator'>
                    </span>
                    <span class='symbol'>/</span>
                    <span class='bottom'>  
                        <input type='text' name='fraction_{$count}_denominator' placeholder='Denominator' class='fraction-denominator'>
                    </span>
                </div>
                <br><br>
            ";
            }


            // For mixed fraction input (whole number, numerator, and denominator)
            // $html .= "
            //     <div class='mixed-fraction-input'>
            //         <input type='text' name='mixed_{$count}_whole' placeholder='Whole Number' class='mixed-whole'>
            //         <div class='fraction-input'>
            //             <input type='text' name='mixed_{$count}_numerator' placeholder='Numerator' class='fraction-numerator'>
            //                 <span>/</span>
            //             <input type='text' name='mixed_{$count}_denominator' placeholder='Denominator' class='fraction-denominator'>
            //     </div>
            //     </div>    
            // ";

            $html .= "<hr>";

            $count++;
        }

        return view('question.testq.view', compact('html'));
    }
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
