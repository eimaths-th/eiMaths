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
                <form class="form" id="questionForm">
                    @csrf
                    <input type="hidden" id="formMethod" name="_method" value="POST">
                    <div class="mb-3">
                        <select name="type" id="type" class="form-control">
                            <option value="">Select type answer</option>
                            <option value="options">Options</option>
                            <option value="written">Written</option>
                            <option value="image">image</option>
                            <option value="fraction">fraction</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="text" class="form-label">Question text</label>
                        <textarea id="text" class="form-control" name="text">{{ $question->text ?? old('text') }}</textarea>
                        @error('text')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                        <div class="row mt-2 fraction-button" id="fraction-button" style="display: none;">
                            <div class="col-lg-2">
                                <button type="button" class="btn btn-primary" onclick="insertFrac()">
                                    {{-- เศษส่วน --}}
                                    Fraction
                                </button>
                            </div>
                            <div class="col-lg-2">
                                <button type="button" class="btn btn-primary" onclick="insertMixed()">
                                    {{-- จำนวนคละ --}}
                                    MixFrac
                                </button>
                            </div>
                        </div>
                    </div>




                    <div class="mb-3">
                        <label for="imageQuestion" class="form-label">Question Image</label>
                        <!-- File Upload Input (Initially visible if no image) -->
                        <div id="add-image-question-container">
                            <input type="file" id="imageQuestion" name="imageQuestion" class="form-control mb-2"
                                accept="image/*">
                            <input type="hidden" id="old_img_name" name="old_img_name" value="">
                        </div>

                        <!-- Image Preview -->
                        <div id="show-image-question-container" style="display:none;">
                            <div class="mb-2">
                                <img id="preview-image-question" src="" alt="Question Image"
                                    style="max-width: 200px;" class="img-thumbnail">
                                <button type="button" class="btn btn-danger btn-sm"
                                    id="remove-show-image-question-container">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>



                        @error('imageQuestion')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
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

                    <div class="mb-3 image_type">
                        <label for="image" class="form-label">Question Image Options</label>
                        <br>
                        <button type="button" class="btn btn-sm btn-primary mt-2 addOption2"
                            id="add-option-img">Add</button>

                        <div id="image-options-container">

                            @if (old('image_options', $image_options ?? []))

                                @foreach (old('image_options', $image_options ?? []) as $index => $option)
                                    <div class="img-option-item mt-2 form-row align-items-center">
                                        <div class="col-xs-10 col-sm-10 col-md-10 text-center">
                                            <input class="form-control" type="file"
                                                name="image_options[{{ $index }}][file]"
                                                value="{{ old('image_options.' . $index . '.file', $option['file'] ?? '') }}"
                                                autocomplete="off" />
                                        </div>
                                        <div class="col-auto text-nowrap">
                                            <input type="hidden" name="image_options[{{ $index }}][correct]"
                                                value="0">
                                            <input type="checkbox" class="text-left"
                                                name="image_options[{{ $index }}][correct]" value="1"
                                                {{ isset($option['correct']) && $option['correct'] ? 'checked' : '' }}>
                                            Correct&nbsp
                                            <button type="button"
                                                class="btn btn-danger btn-sm remove-option-img">&nbsp&nbsp<i
                                                    class="fa fa-trash"></i>&nbsp&nbsp</button>
                                        </div>
                                    </div>
                                    @error('image_options.' . $index . '.file')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
                                @endforeach
                            @endif
                        </div>
                        @error('image_options')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 fraction_type">
                        <hr>
                        <h2>answers fraction</h2>
                        <div class="mb-3">
                            <label for="answer_type" class="form-label">Type Answer</label>
                            <select name="answer_type_fraction" id="answer_type_fraction" class="form-control">
                                <option value="">select type answer fraction</option>
                                <option value="written">written</option>
                                <option value="options">options</option>
                            </select>
                        </div>
                        <div class="written_type_fraction">
                            <div class="mb-3">
                                <label for="answer_numerator" class="form-label">Numerator</label>
                                <input type="number" class="form-control" name="answer_numerator"
                                    id="answer_numerator" placeholder="numerator" required>
                            </div>

                            <div class="mb-3">
                                <label for="answer_denominator" class="form-label">Denominator</label>
                                <input type="number" class="form-control" name="answer_denominator" required
                                    id="answer_denominator" placeholder="denominator" min="1">
                            </div>

                            <div class="mb-3">
                                <label for="answer_type" class="form-label">Type Fraction</label>
                                <select name="answer_type" id="answer_type" class="form-control">
                                    <option value="frac">Fraction</option>
                                    <option value="mixed" selected>Mixed Fraction</option>
                                </select>
                            </div>
                        </div>

                        <div class="options_type_fraction">
                            <button type="button" class="btn btn-sm btn-primary mt-2" id="add-fraction-option">Add
                                Fraction</button>
                            <div id="add-fraction-container">

                            </div>

                        </div>


                        <hr>
                    </div>

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="mt-4">
                                <label for="level" class=" form-label">Grade</label>
                                <select id="level" name="level" class="form-control" required>
                                    <option value="" selected>Select Grade</option>
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
                                <select id="term" name="term" class="form-control" required>
                                    <option value="" selected>Select Term</option>
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
                                <select id="section" name="section" class="form-control" required>
                                    <option value="" selected>Select Level</option>
                                    @for ($i = 1; $i <= 20; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                    <!-- Hint -->
                    <div class="mb-3">
                        <label for="code_snippet" class="form-label">Hint</label>
                        <textarea id="code_snippet" class="form-control" name="code_snippet">{{ $question->code_snippet ?? old('code_snippet') }}</textarea>
                        @error('code_snippet')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- Answer explanation -->
                    <div class="mb-3">
                        <label for="answer_explanation" class="form-label">Answer explanation</label>
                        <textarea id="answer_explanation" class="form-control" name="answer_explanation">{{ $question->answer_explanation ?? old('answer_explanation') }}</textarea>
                        @error('answer_explanation')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Answer explanation image -->
                    <div class="mb-3">
                        <label for="answer_explanation_image" class="form-label">Answer Explanation Image</label>

                        <!-- File Upload Input (Initially visible if no image) -->
                        <div id="add-image-answer-explanation-container">
                            <input type="file" id="answer_explanation_image" name="answer_explanation_image"
                                class="form-control mb-2">
                            <input type="hidden" id="old_answer_explanation_img_name"
                                name="old_answer_explanation_img_name" value="">
                        </div>
                        <!-- Image Preview -->
                        <div id="show-image-answer-explanation-container" style="display:none;">
                            <div class="mb-2">
                                <img id="preview-image-answer-explanation" src=""
                                    alt="Answer Explanation Image" style="max-width: 200px;" class="img-thumbnail">
                                <button type="button" class="btn btn-danger btn-sm"
                                    id="remove-show-image-answer-explanation-container">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </div>

                        @error('answer_explanation_image')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- More info link -->
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
                    <button type="button" class="btn btn-success" id="SubmitCreateForm">
                        <i class="fas fa-download"></i> Save
                    </button>
                </span>

                <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal">
                    <i class="fas fa-door-closed"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>
