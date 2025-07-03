{{-- <div class="modal fade" id="CreateModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title"><i class="fas fa-user"></i> Create Question</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="display: none;">
                    <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;">
                    <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>


                <form class="form" action="#" method="POST">

                    @csrf
                    <div class="mb-3">
                        <label for="text" class="form-label">Question text</label>
                        <textarea id="text" class="form-control" name="text">{{ old('text') }}</textarea>
                        @error('text')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="options" class="form-label">Question options</label>
                        <br>
                        <button type="button" class="btn btn-sm btn-primary mt-2" id="add-option">Add</button>
                        <div id="options-container">
                            @if (old('options', $options ?? []))
                                @foreach (old('options', $options ?? []) as $index => $option)
                                    <div class="option-item mt-2 form-row align-items-center">
                                        <div class="col-xs-10 col-sm-10 col-md-10 text-center">
                                            <input type="text" class="form-control"
                                                name="options[${optionIndex}][text]" id="options_${optionIndex}"
                                                autocomplete="off" />
                                        </div>
                                        <div class="col-auto text-nowrap">
                                            <input type="hidden" name="options[${optionIndex}][correct]"
                                                value="0">
                                            <input type="checkbox" class="text-left"
                                                name="options[${optionIndex}][correct]" value="1"> Correct&nbsp
                                            <button type="button"
                                                class="btn btn-danger btn-sm  remove-option">&nbsp&nbsp<i
                                                    class="fa fa-trash"></i>&nbsp&nbsp</button>
                                        </div>
                                    </div>
                                    @error('options.' . $index . '.text')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
                                @endforeach
                            @endif
                        </div>
                        @error('options')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="code_snippet" class="form-label">Code snippet</label>
                        <textarea id="code_snippet" class="form-control" name="code_snippet">{{ old('code_snippet') }}</textarea>
                        @error('code_snippet')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="answer_explanation" class="form-label">Answer explanation</label>
                        <textarea id="answer_explanation" class="form-control" name="answer_explanation">{{ old('answer_explanation') }}</textarea>
                        @error('answer_explanation')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="more_info_link" class="form-label">More info link</label>
                        <input type="text" id="more_info_link" class="form-control" name="more_info_link"
                            value="{{ old('more_info_link') }}" />
                        @error('more_info_link')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="SubmitCreateForm"><i class="fas fa-download"></i>
                    Save</button>
                <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal"><i
                        class="fas fa-door-closed"></i> Close</button>
            </div>
        </div>
    </div>
</div> --}}
{{-- <div class="modal fade" id="CreateModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title">
                    <i class="fas fa-user"></i>
                    {{ $editing ? 'Edit Question' : 'Create Question' }}
                </h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="display: none;">
                    <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;">
                    <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>

                <form class="form" action="{{ $editing ? route('questions.update', $question->id) : route('questions.store') }}" method="POST">
                    @csrf
                    @if ($editing)
                        @method('PUT')
                    @endif

                    <div class="mb-3">
                        <label for="text" class="form-label">Question text</label>
                        <textarea id="text" class="form-control" name="text">{{ old('text', $question->text ?? '') }}</textarea>
                        @error('text')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="options" class="form-label">Question options</label>
                        <br>
                        <button type="button" class="btn btn-sm btn-primary mt-2" id="add-option">Add</button>
                        <div id="options-container">
                            @foreach (old('options', $options) as $index => $option)
                                <div class="option-item mt-2 form-row align-items-center">
                                    <div class="col-xs-10 col-sm-10 col-md-10 text-center">
                                        <input type="text" class="form-control" name="options[{{ $index }}][text]" value="{{ $option['text'] }}" autocomplete="off" />
                                    </div>
                                    <div class="col-auto text-nowrap">
                                        <input type="hidden" name="options[{{ $index }}][correct]" value="0">
                                        <input type="checkbox" class="text-left" name="options[{{ $index }}][correct]" value="1" {{ $option['correct'] ? 'checked' : '' }}> Correct&nbsp
                                        <button type="button" class="btn btn-danger btn-sm remove-option">&nbsp&nbsp<i class="fa fa-trash"></i>&nbsp&nbsp</button>
                                    </div>
                                </div>
                                @error('options.' . $index . '.text')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                            @endforeach
                        </div>
                        @error('options')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="code_snippet" class="form-label">Code snippet</label>
                        <textarea id="code_snippet" class="form-control" name="code_snippet">{{ old('code_snippet', $question->code_snippet ?? '') }}</textarea>
                        @error('code_snippet')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="answer_explanation" class="form-label">Answer explanation</label>
                        <textarea id="answer_explanation" class="form-control" name="answer_explanation">{{ old('answer_explanation', $question->answer_explanation ?? '') }}</textarea>
                        @error('answer_explanation')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="more_info_link" class="form-label">More info link</label>
                        <input type="text" id="more_info_link" class="form-control" name="more_info_link" value="{{ old('more_info_link', $question->more_info_link ?? '') }}" />
                        @error('more_info_link')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-success">{{ $editing ? 'Update' : 'Save' }}</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal">
                    <i class="fas fa-door-closed"></i> Close
                </button>
            </div>
        </div>
    </div>
</div> --}}
<div class="modal fade" id="CreateModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title">
                    <i class="fas fa-user"></i>
                    <span id="question-title"></span>
                    {{-- {{ $editing ? 'Edit Question' : 'Create Question' }} --}}
                </h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="display: none;">
                    <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;">
                    <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>

                {{-- <form class="form" id="questionForm">
                    @csrf
                    <input type="hidden" id="formMethod" name="_method" value="POST">

                    <div class="mb-3">
                        <label for="text" class="form-label">Question text</label>
                        <textarea id="text" class="form-control" name="text">{{ $question->text ?? old('text') }}</textarea>
                        @error('text')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <select name="type" id="type" class="form-control">
                            <option value="">select type answer</option>
                            <option value="options">Options</option>
                            <option value="written">Written</option>
                        </select>
                    </div>

                    <div class="mb-3 wrtiten_type">
                        <label for="options" class="form-label">Question Wrtiten</label>
                        <br>
                        <div class="wrtiten-container">
                            <input type="text" class="form-control" id="written" placeholder="Enter answer correct">
                        </div>
                    </div>

                    <div class="mb-3 options_type">
                        <label for="options" class="form-label">Question options</label>
                        <br>
                        <button type="button" class="btn btn-sm btn-primary mt-2" id="add-option">Add</button>
                        <div id="options-container">
                            @if (old('options', $options ?? []))
                                @foreach (old('options', $options ?? []) as $index => $option)
                                    <div class="option-item mt-2 form-row align-items-center">
                                        <div class="col-xs-10 col-sm-10 col-md-10 text-center">
                                            <input type="text" class="form-control"
                                                name="options[{{ $index }}][text]"
                                                value="{{ $option['text'] ?? '' }}" autocomplete="off" />
                                        </div>
                                        <div class="col-auto text-nowrap">
                                            <input type="hidden" name="options[{{ $index }}][correct]"
                                                value="0">
                                            <input type="checkbox" class="text-left"
                                                name="options[{{ $index }}][correct]" value="1"
                                                {{ isset($option['correct']) && $option['correct'] ? 'checked' : '' }}>
                                            Correct&nbsp
                                            <button type="button"
                                                class="btn btn-danger btn-sm remove-option">&nbsp&nbsp<i
                                                    class="fa fa-trash"></i>&nbsp&nbsp</button>
                                        </div>
                                    </div>
                                    @error('options.' . $index . '.text')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
                                @endforeach
                            @endif
                        </div>
                        @error('options')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="code_snippet" class="form-label">Hint</label>
                        <textarea id="code_snippet" class="form-control" name="code_snippet">{{ $question->code_snippet ?? old('code_snippet') }}</textarea>
                        @error('code_snippet')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="answer_explanation" class="form-label">Answer explanation</label>
                        <textarea id="answer_explanation" class="form-control" name="answer_explanation">{{ $question->answer_explanation ?? old('answer_explanation') }}</textarea>
                        @error('answer_explanation')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="more_info_link" class="form-label">More info link</label>
                        <input type="text" id="more_info_link" class="form-control" name="more_info_link"
                            value="{{ $question->more_info_link ?? old('more_info_link') }}" />
                        @error('more_info_link')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </form> --}}
                <form class="form" id="questionForm">
                    @csrf
                    <input type="hidden" id="formMethod" name="_method" value="POST">

                    <div class="mb-3">
                        <label for="text" class="form-label">Question text</label>
                        <textarea id="text" class="form-control" name="text">{{ $question->text ?? old('text') }}</textarea>
                        @error('text')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                    @php
                        // dd($question);
                    @endphp
                    <div class="mb-3">
                        <select name="type" id="type" class="form-control">
                            <option value="">Select type answer</option>
                            <option value="options">Options</option>
                            <option value="written">Written</option>
                        </select>
                    </div>

                    <div class="mb-3 written_type">
                        <label for="written" class="form-label">Question Written</label>
                        <br>
                        <div class="wrtiten-container">
                            <input type="text" class="form-control" id="written" name="written"
                                placeholder="Enter correct answer">
                        </div>
                    </div>

                    <div class="mb-3 options_type">
                        <label for="options" class="form-label">Question options</label>
                        <br>
                        <button type="button" class="btn btn-sm btn-primary mt-2" id="add-option">Add</button>
                        <div id="options-container">
                            @if (old('options', $options ?? []))
                                @foreach (old('options', $options ?? []) as $index => $option)
                                    <div class="option-item mt-2 form-row align-items-center">
                                        <div class="col-xs-10 col-sm-10 col-md-10 text-center">
                                            <input type="text" class="form-control"
                                                name="options[{{ $index }}][text]"
                                                value="{{ $option['text'] ?? '' }}" autocomplete="off" />
                                        </div>
                                        <div class="col-auto text-nowrap">
                                            <input type="hidden" name="options[{{ $index }}][correct]"
                                                value="0">
                                            <input type="checkbox" class="text-left"
                                                name="options[{{ $index }}][correct]" value="1"
                                                {{ isset($option['correct']) && $option['correct'] ? 'checked' : '' }}>
                                            Correct&nbsp
                                            <button type="button"
                                                class="btn btn-danger btn-sm remove-option">&nbsp&nbsp<i
                                                    class="fa fa-trash"></i>&nbsp&nbsp</button>
                                        </div>
                                    </div>
                                    @error('options.' . $index . '.text')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
                                @endforeach
                            @endif
                        </div>
                        @error('options')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="mt-4">
                                <label for="level" class=" form-label">Grade</label>
                                <select id="level" name="level" class="form-control">
                                    <option value="" selected>Select term
                                    </option>
                                    <option value="K1">K1</option>
                                    <option value="K2">K2</option>
                                    <option value="P1">P1</option>
                                    <option value="P2">P2</option>
                                    <option value="P3">P3</option>
                                    <option value="P4">P4</option>
                                    <option value="P5">P5</option>
                                    <option value="P6">P6</option>
                                </select>

                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mt-4">
                                <label for="term" class=" form-label">Term</label>
                                <select id="term" name="term" class="form-control">
                                    <option value="" selected>Select term
                                    </option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                </select>

                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mt-4">
                                <label for="section" class=" form-label">Level</label>
                                <select id="section" name="section" class="form-control">
                                    <option value="" selected>Select Level
                                    </option>
                                    @for ($i = 1; $i <= 20; $i++)
                                        <option value="{{ $i }}">
                                            {{ $i }}</option>
                                    @endfor

                                </select>

                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="code_snippet" class="form-label">Hint</label>
                        <textarea id="code_snippet" class="form-control" name="code_snippet">{{ $question->code_snippet ?? old('code_snippet') }}</textarea>
                        @error('code_snippet')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="answer_explanation" class="form-label">Answer explanation</label>
                        <textarea id="answer_explanation" class="form-control" name="answer_explanation">{{ $question->answer_explanation ?? old('answer_explanation') }}</textarea>
                        @error('answer_explanation')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="more_info_link" class="form-label">More info link</label>
                        <input type="text" id="more_info_link" class="form-control" name="more_info_link"
                            value="{{ $question->more_info_link ?? old('more_info_link') }}" />
                        @error('more_info_link')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </form>

            </div>
            <div class="modal-footer">
                <span id="btn-submit">
                    {{-- <button type="button" class="btn btn-success" id="SubmitCreateForm">
                        <i class="fas fa-download"></i> Save
                    </button> --}}
                </span>

                <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal">
                    <i class="fas fa-door-closed"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>
