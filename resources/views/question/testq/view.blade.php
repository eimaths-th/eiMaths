@extends('layouts.app')

@section('style')
    <style>
        .pagination {
            justify-content: center;
        }
    </style>

    <style>
        /* Style for regular fraction inputs */
        .fraction-input {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .fraction-numerator,
        .fraction-denominator {
            width: 40px;
            text-align: center;
        }

        .fraction-input span {
            font-size: 18px;
            padding: 0 5px;
        }

        /* Style for mixed fraction inputs */
        .mixed-fraction-input {
            display: flex;
            gap: 5px;
            align-items: center;
        }

        .mixed-whole {
            width: 40px;
            text-align: center;
        }

        /* Optional: Adjust overall input appearance */
        input[type="text"] {
            font-size: 16px;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
    </style>
    <style>
        .frac {
            display: inline-block;
            position: relative;
            vertical-align: middle;
            letter-spacing: 0.001em;
            text-align: center;
        }

        .frac>span {
            display: block;
            padding: 0.1em;
        }

        .frac span.bottom {
            border-top: thin solid black;
        }

        .frac span.symbol {
            display: none;
        }
    </style>
    <script type="text/javascript" async
        src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.7/MathJax.js?config=TeX-MML-AM_CHTML"></script>
@endsection

@section('content')
    <div class="container">
        <h2>Exam Questions</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        {!! $html !!} {{-- Render the pre-generated HTML --}}

        {{-- @php
            function toMixedFraction($numerator, $denominator)
            {
                if ($numerator % $denominator == 0) {
                    return (string) ($numerator / $denominator); // Whole number
                }

                $whole = intdiv($numerator, $denominator);
                $remainder = $numerator % $denominator;

                return $whole > 0
                    ? "{$whole} \\frac{ {$remainder} }{ {$denominator} }"
                    : "\\frac{ {$remainder} }{ {$denominator} }";
            }
        @endphp

        @foreach ($questions as $question)
            <p>{{ $loop->iteration }}. {{ $question->question_text }}</p>
            <p>Answer: \( {{ toMixedFraction($question->answer_numerator, $question->answer_denominator) }} \)</p>
        @endforeach
        @foreach ($questions as $question)
            <p>{{ $loop->iteration }}. {{ $question->question_text }}</p>
            <p>Answer: \( \frac{ {{ $question->answer_numerator }} }{ {{ $question->answer_denominator }} } \)</p>

            <label for="user_numerator_{{ $question->id }}">Your Answer:</label>
            <input type="number" name="user_numerator[{{ $question->id }}]" required> /
            <input type="number" name="user_denominator[{{ $question->id }}]" required>
        @endforeach --}}

        <!-- Pagination -->
        {{-- <div class="pagination">
            {{ $questions->links() }}
        </div> --}}

    </div>
@endsection

@section('script')
    {{-- <script>
        // MathJax
        MathJax.Hub.Config({
            tex2jax: {
                inlineMath: [['\\(', '\\)']],
                displayMath: [['\\[', '\\]']],
                processEscapes: true,
                processEnvironments: true,
                skipTags: ['script', 'noscript', 'style', 'textarea', 'pre'],
                TeX: {
                    equationNumbers: { autoNumber: 'AMS' },
                    extensions: ['AMSmath.js', 'AMSsymbols.js', 'color.js'],
                },
            },
            messageStyle: 'none',
        });
    </script> --}}
@endsection
