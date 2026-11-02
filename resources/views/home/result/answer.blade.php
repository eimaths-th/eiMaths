@php
    $question = $answer->question;
    $answer_type = $question->type;
    $fraction_answer = $answer_type === 'fraction' ? $question->fractions->first() : null;
    $has_choices = in_array($answer_type, ['options', 'image'], true) || ($answer_type === 'fraction' && $fraction_answer?->type === 'options');
    $submitted_answer = match ($answer_type) {
        'options' => $answer->option_id, 'image' => $answer->image_id,
        'written' => $answer->written_answer, 'fraction' => $answer->fraction, default => null,
    };
    $is_unanswered = $submitted_answer === null || $submitted_answer === '';
    $status = $is_unanswered ? 'unanswered' : ($answer->correct ? 'correct' : 'incorrect');
    $status_label = $is_unanswered ? 'ยังไม่ได้ตอบ' : ($answer->correct ? 'ตอบถูก' : 'ตอบไม่ถูก');
    $solution_video_url = $can_view_videos ? $question->answer_explanation_video_embed_url : null;
    $choices = match ($answer_type) {
        'options' => $question->options, 'image' => $question->images,
        'fraction' => $has_choices ? $question->fractions : collect(), default => collect(),
    };
@endphp
<article class="result-question" aria-labelledby="result-question-{{ $question_number }}">
    <div class="result-question-heading">
        <h3 id="result-question-{{ $question_number }}">ข้อ <span class="result-question-number">{{ $question_number }}</span></h3>
        <span class="result-status result-status-{{ $status }}"><span aria-hidden="true">{{ $is_unanswered ? '—' : ($answer->correct ? '✓' : '×') }}</span> {{ $status_label }}</span>
    </div>
    <div class="result-question-text">
        @if ($answer_type === 'fraction')
            {{ replaceMixedFractions($question->text ?? '') }}
        @else
            {!! nl2br(replaceMixedFractions($question->text ?? '')) !!}
        @endif
    </div>
    @if ($has_choices)
        <dl class="result-answer-summary">
            <div><dt>คำตอบของคุณ</dt><dd>{{ $is_unanswered ? 'ยังไม่ได้ตอบ' : 'ดูตัวเลือกที่ระบุด้านล่าง' }}</dd></div>
        </dl>
        <ul class="result-options" aria-label="ตัวเลือกคำตอบ">
            @foreach ($choices as $choice)
                @php $is_selected = !$is_unanswered && $submitted_answer == $choice->id; @endphp
                <li @class(['result-option', 'result-option-selected' => $is_selected, 'result-option-correct' => $choice->correct])>
                    <span class="result-option-letter">{{ chr(65 + $loop->index) }}</span>
                    <div class="result-option-content">
                        @if ($answer_type === 'image')
                            @if ($choice->img_name)
                                <img src="{{ asset('images_options/' . $choice->img_name) }}" alt="ตัวเลือก {{ chr(65 + $loop->index) }} ของข้อ {{ $question_number }}" loading="lazy">
                            @endif
                        @elseif ($answer_type === 'fraction')
                            {{ $choice->answer_type === 'mixed' ? toMixedFraction($choice->numerator, $choice->denominator, null) : toFraction($choice->numerator, $choice->denominator) }}
                        @else
                            {{ replaceMixedFractions($choice->text ?? '') }}
                        @endif
                    </div>
                    <div class="result-option-labels">
                        @if ($is_selected)<span>คำตอบของคุณ</span>@endif
                        @if ($choice->correct)<span class="result-correct-label">✓ คำตอบที่ถูก</span>@endif
                    </div>
                </li>
            @endforeach
        </ul>
    @else
        <dl class="result-answer-summary result-answer-pair">
            <div><dt>คำตอบของคุณ</dt><dd>{{ $is_unanswered ? 'ยังไม่ได้ตอบ' : replaceMixedFractions((string) $submitted_answer) }}</dd></div>
            <div><dt>คำตอบที่ถูก</dt><dd>
                @if ($answer_type === 'written')
                    {{ replaceMixedFractions($question->written_answer ?? '') }}
                @elseif ($fraction_answer)
                    {{ $fraction_answer->answer_type === 'mixed' ? toMixedFraction($fraction_answer->numerator, $fraction_answer->denominator, null) : toFraction($fraction_answer->numerator, $fraction_answer->denominator) }}
                @else
                    —
                @endif
            </dd></div>
        </dl>
    @endif
    @if ($question->answer_explanation || $question->answer_explanation_image || $solution_video_url)
        <section class="result-explanation" aria-labelledby="result-explanation-{{ $question_number }}">
            <h4 id="result-explanation-{{ $question_number }}">วิธีทำ</h4>
            @if ($question->answer_explanation)
                <p>{!! nl2br(e($question->answer_explanation)) !!}</p>
            @endif
            @if ($question->answer_explanation_image)
                <img class="result-explanation-image" src="{{ asset('img_questions/' . $question->answer_explanation_image) }}" alt="ภาพอธิบายคำตอบข้อ {{ $question_number }}" loading="lazy">
            @endif
            @if ($solution_video_url)
                <details class="result-video">
                    <summary>ดูวิดีโอวิธีทำ</summary>
                    <iframe src="{{ $solution_video_url }}" title="วิดีโอวิธีทำข้อ {{ $question_number }}" loading="lazy" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>
                </details>
            @endif
        </section>
    @endif
</article>
