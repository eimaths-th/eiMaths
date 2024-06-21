
@php
    $secondsPerQuestion = 0;
@endphp
<div x-data="{ secondsLeft: 0 }"
     x-init="setInterval(() => {
        secondsLeft++;
     }, 1000)">

    <div class="mb-2">
        Time elapsed:
        <span
            x-text="
            (Math.floor(secondsLeft / 3600) < 10 ? '0' : '') + Math.floor(secondsLeft / 3600) + ':' +
            (Math.floor(secondsLeft % 3600 / 60) < 10 ? '0' : '') + Math.floor(secondsLeft % 3600 / 60) + ':' +
            (secondsLeft % 60 < 10 ? '0' : '') + secondsLeft % 60"
            :class="{ 'font-bold': secondsLeft >= 60, 'font-bold': secondsLeft < 60 }"
            >
        </span>
        sec.
    </div>

    <span class="text-bold">Question {{ $currentQuestionIndex + 1 }} of {{ $this->questionsCount }}:</span>
    <h2 class="mb-4 text-2xl">{{ $currentQuestion->text }}</h2>

    @if ($currentQuestion->code_snippet)
        <pre class="mb-4 border-2 border-solid bg-gray-50 p-2">{{ $currentQuestion->code_snippet }}</pre>
    @endif

    @foreach ($currentQuestion->options as $option)
        <div>
            <label for="option.{{ $option->id }}">
                <input type="radio" id="option.{{ $option->id }}"
                    wire:model.defer="answersOfQuestions.{{ $currentQuestionIndex }}"
                    name="answersOfQuestions.{{ $currentQuestionIndex }}" value="{{ $option->id }}">
                {{ $option->text }}
            </label>
        </div>
    @endforeach

    @if ($currentQuestionIndex < $this->questionsCount - 1)
        <div class="mt-4">
            <x-secondary-button
                x-on:click="secondsLeft = {{ config('quiz.secondsPerQuestion') }}; $wire.nextQuestion();">
                Next question
            </x-secondary-button>
        </div>
    @else
        <div class="mt-4">
            <x-primary-button x-on:click="$wire.submit();">Submit</x-primary-button>
        </div>
    @endif
</div>
