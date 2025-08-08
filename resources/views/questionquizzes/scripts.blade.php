<script>
    // Answer Explanation Image functions
    function showAnswerExplanationImg(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('answer_explanation_img_thumbnail').src = e.target.result;
                document.getElementById('answer_explanation_img_preview').style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function showQuestionImg(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('question_img_thumbnail').src = e.target.result;
                document.getElementById('question_img_preview').style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeAnswerExplanationImg() {
        document.getElementById('answer_explanation_image').value = '';
        document.getElementById('answer_explanation_img_preview').style.display = 'none';
        document.getElementById('answer_explanation_img_thumbnail').src = '';
        // Set hidden field to indicate image should be removed
        document.querySelector('input[name="old_answer_explanation_image"]').value = '';
    }

    function removeQuestionImg() {
        document.getElementById('imageQuestion').value = '';
        document.getElementById('question_img_preview').style.display = 'none';
        document.getElementById('question_img_thumbnail').src = '';
        // Set hidden field to indicate image should be removed
        document.querySelector('input[name="old_question_img"]').value = '';
    }

    // Dynamic Answer Explanation Image functions for dynamically added forms
    function showAnswerExplanationImgDynamic(input, formIndex) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('answer_explanation_img_thumbnail_' + formIndex).src = e.target.result;
                document.getElementById('answer_explanation_img_preview_' + formIndex).style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function showQuestionImgDynamic(input, formIndex) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('question_img_thumbnail_' + formIndex).src = e.target.result;
                document.getElementById('question_img_preview_' + formIndex).style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeAnswerExplanationImgDynamic(formIndex) {
        document.getElementById('answer_explanation_image_' + formIndex).value = '';
        document.getElementById('answer_explanation_img_preview_' + formIndex).style.display = 'none';
        document.getElementById('answer_explanation_img_thumbnail_' + formIndex).src = '';
    }

    function removeQuestionImgDynamic(formIndex) {

        document.getElementById('imageQuestion_' + formIndex).value = '';
        document.getElementById('question_img_preview_' + formIndex).style.display = 'none';
        document.getElementById('question_img_thumbnail_' + formIndex).src = '';
    }

    function insertFrac() {
        let question_text = document.querySelector('#text');
        if (question_text) {
            question_text.value += ' Frac(เศษ,ส่วน) ';
        }
    }

    function insertMixed() {
        let question_text = document.querySelector('#text');
        if (question_text) {
            question_text.value += ' MixFrac(เศษ,ส่วน) ';
        }
    }

    function insertFracIndex(index) {
        let textarea = document.getElementById(`text_${index}`);
        if (textarea) {
            textarea.value += ' Frac(เศษ,ส่วน) '; // Example fraction format
            textarea.focus();
        }
    }

    function insertMixedIndex(index) {
        let textarea = document.getElementById(`text_${index}`);
        if (textarea) {
            textarea.value += ' MixFrac(เศษ,ส่วน) '; // Example mixed fraction format
            textarea.focus();
        }
    }

    // Auto-save functionality


    $(document).ready(function() {
        // Initialize auto-save

        $('.select2').select2({
            placeholder: "Select Questions",
            allowClear: true
        });
        $.ajax({
            url: '{{ route('questions.list') }}', // Adjust the route to match your setup
            method: 'GET',
            success: function(res) {
                var options = res.questions;
                $.each(options, function(id, question) {

                    var selected = ({{ json_encode(old('questions', [])) }})
                        .includes(question.id) ? 'selected' : '';
                    $('#questions_id').append('<option value="' + question.id +
                        '" ' + selected + '>' + question.code + ' ' +
                        question.text + '</option>');
                });
                $('#questions_id').trigger('change');
            }
        });
    });

    $(document).ready(function() {
        setupAutoSave();

        $('#quizCrate').hide();
        $('.written_type').hide();
        $('.options_type').hide();
        $('.image_type').hide();
        var token = '{{ csrf_token() }}';
        let FormIndex = 1;
        let SelectQuiz = '';
        let levelQuiz = '';
        let termQuiz = '';
        let sectionQuiz = '';

        $(document).on('keyup', '#title', function(e) {
            e.preventDefault();
            var val = $('#title').val();
            var slug = val.toLowerCase().replace(/\s+/g, '-');
            $('#slug').val(slug);
        });


        $(document).on('change', '#SelectQuiz', function() {
            SelectQuiz = $(this).val(); // Get selected quiz ID

            if (SelectQuiz === 'create') {
                $('#questions_id').val('');
                $('#questions_id').html('');
                $.ajax({
                    url: '{{ route('questions.list') }}', // Adjust the route to match your setup
                    method: 'GET',
                    success: function(res) {
                        var options = res.questions;
                        $.each(options, function(id, question) {

                            var selected = ({{ json_encode(old('questions', [])) }})
                                .includes(question.id) ? 'selected' : '';
                            $('#questions_id').append('<option value="' + question
                                .id +
                                '" ' + selected + '>' + question.code + ' ' +
                                question.text + '</option>');
                        });
                        $('#questions_id').trigger('change');
                    }
                });
                $('#quizCrate').show();

            } else {
                $('.question-grade').val('').trigger('change');
                $('.question-term').val('').trigger('change');
                $('.question-level').val('').trigger('change');

                var url = '{{ route('questions-quizzes.selected', ':id') }}';
                url = url.replace(':id', SelectQuiz);

                $.ajax({
                    // url: "selected/" + SelectQuiz,
                    url: url,
                    method: 'get',
                    success: function(res) {
                        $('#questions_id').html(res
                            .html_questions_select); // Update the select options
                        $('.question-grade').val(res.quizLevel).trigger('change');
                        $('.question-term').val(res.quizTerm).trigger('change');
                        $('.question-level').val(res.quizSection).trigger('change');
                        levelQuiz = res.quizLevel;
                        termQuiz = res.quizTerm;
                        sectionQuiz = res.quizSection;


                    },
                    error: function(xhr) {
                        console.error('Failed to fetch questions:', xhr.responseText);
                    }
                });

                $('#quizCrate').hide();

            }
        });

        $(document).on('change', '#level', function() {
            levelQuiz = $(this).val(); // Get selected quiz ID
        });

        $(document).on('change', '#section', function() {
            sectionQuiz = $(this).val(); // Get selected quiz ID
            checkSelectionsAndFetchData();
        });

        $(document).on('change', '#term', function() {
            termQuiz = $(this).val(); // Get selected quiz ID
        });

        function checkSelectionsAndFetchData() {

            if (levelQuiz && termQuiz && sectionQuiz) {
                // All 3 are selected, now fetch data
                $('.question-grade').val(levelQuiz).trigger('change');
                $('.question-term').val(termQuiz).trigger('change');
                $('.question-level').val(sectionQuiz).trigger('change');

                $.ajax({
                    url: '{{ route('questions.list') }}', // Adjust the route to match your setup
                    method: 'GET',
                    data: {
                        level: levelQuiz,
                        term: termQuiz,
                        section: sectionQuiz
                    },
                    success: function(res) {
                        var options = res.questions;
                        $('#questions_id').empty();
                        $.each(options, function(id, question) {
                            var selected = ({{ json_encode(old('questions', [])) }})
                                .includes(question.id) ? 'selected' : '';
                            $('#questions_id').append('<option value="' + question.id +
                                '" ' + selected + '>' + question.code + ' ' +
                                question.text + '</option>');
                        });
                        // Refresh Select2
                        $('#questions_id').trigger('change');
                    }
                });

            }

        }

        function toggleTypeFields(selectedType) {
            if (selectedType === 'written') {
                $('.written_type').show();
                $('.options_type').hide();
                $('.image_type').hide();
                $('.fraction_type').hide();
                $('.fraction-button').hide();
                $('.fraction-button').show();

            } else if (selectedType === 'options') {
                $('.written_type').hide();
                $('.options_type').show();
                $('.image_type').hide();
                $('.fraction_type').hide();
                // $('.fraction-button').hide();
                $('.fraction-button').show();


            } else if (selectedType === 'image') {
                $('.written_type').hide();
                $('.options_type').hide();
                $('.image_type').show();
                $('.fraction_type').hide();
                $('.fraction-button').hide();
                $('.fraction-button').show();


            } else if (selectedType === 'fraction') {
                $('.written_type').hide();
                $('.options_type').hide();
                $('.image_type').hide();
                $('.fraction_type').show();
                $('.fraction-button').show();


            } else {
                $('.written_type').hide();
                $('.options_type').hide();
                $('.image_type').hide();
                $('.fraction_type').hide();
                $('.fraction-button').hide();
                $('.fraction-button').show();


            }
        }

        function toggleTypeFractionFields(selectedFractionType, res) {

            if (selectedFractionType === 'written') {
                $('.options_type_fraction').hide();
                $('.written_type_fraction').show();

                if (res) {
                    $('#answer_numerator').val(res.question
                        .answer_numerator); // Set written answer if applicable
                    $('#answer_denominator').val(res.question
                        .answer_denominator); // Set written answer if applicable
                    $('#answer_type').val(res.question.answer_type); // Set written answer if applicable

                }
            } else if (selectedFractionType === 'options') {
                $('.written_type_fraction').hide();
                $('.options_type_fraction').show();

            } else {
                $('.written_type_fraction').hide();
                $('.options_type_fraction').hide();
            }
        }

        $('.written_type').hide();
        $('.options_type').hide();
        $('.image_type').hide();
        $('.fraction_type').hide();

        let optionIndex = {{ count(old('options', $options ?? [])) }};

        function addOption2(text = '', correct = 0) {
            $('#options-container').append(`
            <div class="option-item mt-2 form-row align-items-center">
                <div class="col-xs-10 col-sm-10 col-md-10 text-center">
                    <input type="text" class="form-control" name="options[${optionIndex}][text]" value="${text}" autocomplete="off" />
                </div>
                <div class="col-auto text-nowrap">
                    <input type="hidden" name="options[${optionIndex}][correct]" value="0">
                    <input type="checkbox" class="text-left" name="options[${optionIndex}][correct]" value="1" ${correct ? 'checked' : ''}> Correct&nbsp
                    <button type="button" class="btn btn-danger btn-sm remove-option">&nbsp&nbsp<i class="fa fa-trash"></i>&nbsp&nbsp</button>
                </div>
            </div>
        `);
            optionIndex++;
        }

        function addFractionOption(numerator = '', denominator = '', answerType = 'mixed', correct = 0) {
            $('#add-fraction-container').append(`
                    <div class="fraction-item mt-2 form-row align-items-center">
                        <div class="col-xs-3 col-sm-3 col-md-3 text-center">
                            <input type="number" class="form-control" name="answer_numerator[${optionIndex}]" value="${numerator}" placeholder="Numerator" required>
                        </div>
                        <div class="col-xs-3 col-sm-3 col-md-3 text-center">
                            <input type="number" class="form-control" name="answer_denominator[${optionIndex}]" value="${denominator}" placeholder="Denominator" required min="1">
                        </div>
                        <div class="col-xs-3 col-sm-3 col-md-3 text-center">
                            <select name="answer_type[${optionIndex}]" class="form-control">
                                <option value="frac" ${answerType === 'frac' ? 'selected' : ''}>Fraction</option>
                                <option value="mixed" ${answerType === 'mixed' ? 'selected' : ''}>Mixed Fraction</option>
                            </select>
                        </div>
                        <div class="col-auto text-nowrap">
                            <input type="hidden" name="options[${optionIndex}][correct]" value="0">
                            <input type="checkbox" class="text-left" name="options[${optionIndex}][correct]" value="1" ${correct ? 'checked' : ''}> Correct&nbsp
                            <button type="button" class="btn btn-danger btn-sm remove-fraction-option"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                `);
            optionIndex++;
        }

        $('.addOption2').on('click', function() {
            addOption2();
        });
        $(document).on('change', '#answer_type_fraction', function() {
            var selectedFractionType = $(this).val();

            toggleTypeFractionFields(selectedFractionType);
        });

        $(document).on('change', '#SelectType', function() {
            var selectedType = $(this).val();

            toggleTypeFields(selectedType);
        });

        $(document).on('click', '.remove-option', function() {

            $(this).closest('.option-item').remove();
        });
        $('#add-fraction-option').on('click', function() {
            addFractionOption();
        });

        $(document).on('click', '.remove-fraction-option', function() {
            $(this).closest('.fraction-item').remove();
        });
        // $('#add-fraction-container').on('click', '.remove-option', function() {
        //     $(this).closest('.fraction-item').remove();
        // });

        // var token = ''
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#AddQuestionForm').on('click', function() {
            addForm2();
            $(`.options_type_${FormIndex}`).hide();
            $(`.written_type_${FormIndex}`).hide();
            $(`.image_type_${FormIndex}`).hide();
            $(`.fraction_type_${FormIndex}`).hide();
            checkSelectionsAndFetchData();


        });

        function addForm2() {
            return new Promise((resolve) => {
                FormIndex++;
                let options = '';
                for (let i = 1; i <= 20; i++) {
                    options += `<option value="${i}">${i}</option>`;
                }
                // checkSelectionsAndFetchData()
                let newForm = `
                <div class="question-form">
                    <div class="question-form-index">
                    <h4 class="mb-4">Create Question #${FormIndex}</h4>
                    <div class="mb-3">
                        <select name="type-${FormIndex}" id="2-type-${FormIndex}" class="form-control">
                            <option value="">Select type answer</option>
                            <option value="options">Options</option>
                            <option value="written">Written</option>
                            <option value="image">Image</option>
                            <option value="fraction">Fraction</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="text_${FormIndex}" class="form-label">Question text</label>
                        <textarea id="text_${FormIndex}" class="form-control" name="text_${FormIndex}"></textarea>
                        <div class="row mt-2 fraction-button-${FormIndex}" id="fraction-button-${FormIndex}" style="display: none;">
                            <div class="col-lg-2">
                                <button type="button" class="btn btn-primary" onclick="insertFracIndex(${FormIndex})">
                                    Fraction
                                </button>
                            </div>
                            <div class="col-lg-2">
                                <button type="button" class="btn btn-primary" onclick="insertMixedIndex(${FormIndex})">
                                    MixFrac
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="imageQuestion_${FormIndex}" class="form-label">Question Image</label>
                        <div id="add-image-question-container_${FormIndex}">
                            <input type="file" id="imageQuestion_${FormIndex}" class="form-control" name="imageQuestion_${FormIndex}" accept="image/*" onchange="showQuestionImgDynamic(this, ${FormIndex})">
                        </div>
                        <div id="question_img_preview_${FormIndex}" class="mt-2" style="display: none;">
                            <img id="question_img_thumbnail_${FormIndex}" src="" alt="Question Image" style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; border-radius: 4px; padding: 5px;">
                            <br>
                            <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeQuestionImgDynamic(${FormIndex})">Remove Image</button>
                        </div>
                    </div>


                    <div class="mb-3 written_type_${FormIndex}">
                        <label for="written_${FormIndex}" class="form-label">Question Written</label>
                        <input type="text" class="form-control" id="written_${FormIndex}" name="written_${FormIndex}"
                            placeholder="Enter correct answer">
                    </div>
                    <div class="mb-3 options_type_${FormIndex}">
                        <label for="options_${FormIndex}" class="form-label">Question options</label>
                        <br>
                        <button type="button" class="btn btn-sm btn-primary mt-2" id="add-option-${FormIndex}">Add</button>
                        <div id="options-container-${FormIndex}">
                            <!-- Options will be added here -->
                        </div>
                    </div>
                    <div class="mb-3 image_type_${FormIndex}">
                        <label for="image_${FormIndex}" class="form-label">Question Image Options</label> <br>
                        <button type="button" class="btn btn-sm btn-primary mt-2" id="add-img-option-${FormIndex}">Add</button>
                        <div id="image-options-container-${FormIndex}">

                        </div>
                    </div>

                    <div class="mb-3 fraction_type_${FormIndex}">
                        <hr>
                        <h2>answers fraction</h2>
                        <div class="mb-3">
                            <label for="answer_type" class="form-label">Type Answer</label>
                                <select name="answer_type_fraction-${FormIndex}" id="answer_type_fraction-${FormIndex}" class="form-control">
                                    <option value="">select type answer fraction</option>
                                    <option value="written">written</option>
                                    <option value="options">options</option>
                                </select>
                        </div>
                        <div class="written_type_fraction_${FormIndex}" style="display: none;">
                            <div class="mb-3">
                                <label for="answer_numerator_${FormIndex}" class="form-label">Numerator</label>
                                <input type="number" class="form-control" name="answer_numerator_${FormIndex}" id="answer_numerator_${FormIndex}" placeholder="numerator" required>
                            </div>
                            <div class="mb-3">
                                <label for="answer_denominator_${FormIndex}" class="form-label">Denominator</label>
                                <input type="number" class="form-control" name="answer_denominator_${FormIndex}" required id="answer_denominator_${FormIndex}" placeholder="denominator" min="1">
                            </div>

                            <div class="mb-3">
                                <label for="answer_type_${FormIndex}" class="form-label">Type Fraction</label>
                                <select name="answer_type-${FormIndex}" id="answer_type-${FormIndex}" class="form-control">
                                    <option value="frac">Fraction</option>
                                    <option value="mixed" selected>Mixed Fraction</option>
                                </select>
                            </div>
                        </div>

                        <div class="options_type_fraction_${FormIndex}" style="display: none;">
                            <button type="button" class="btn btn-sm btn-primary mt-2" id="add-fraction-option-${FormIndex}">Add Fraction</button>
                                <div id="add-fraction-container_${FormIndex}">
                                    <!-- Options will be added here -->
                                </div>
                        </div>
                        <hr>
                    </div>

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="mt-4">
                                <label for="level" class="form-label">Grade</label>
                                <select id="level_${FormIndex}" name="level_${FormIndex}" class="question-grade question-grade form-control">
                                    <option value="" selected>Select term</option>
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
                                <label for="term" class="form-label">Term</label>
                                <select id="term_${FormIndex}" name="term_${FormIndex}" class="question-term form-control">
                                    <option value="" selected>Select term</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mt-4">
                                <label for="section" class="form-label">Level</label>
                                <select id="section_${FormIndex}" name="section_${FormIndex}" class="question-level form-control">
                                    <option value="" selected>Select Level</option>
                                    ${options}
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="code_snippet_${FormIndex}" class="form-label">Hint</label>
                        <textarea id="code_snippet_${FormIndex}" class="form-control" name="code_snippet_${FormIndex}"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="answer_explanation_${FormIndex}" class="form-label">Answer explanation</label>
                        <textarea id="answer_explanation_${FormIndex}" class="form-control" name="answer_explanation_${FormIndex}"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="answer_explanation_image_${FormIndex}" class="form-label">Answer Explanation Image</label>
                        <input type="file" id="answer_explanation_image_${FormIndex}" class="form-control" name="answer_explanation_image_${FormIndex}" accept="image/*" onchange="showAnswerExplanationImgDynamic(this, ${FormIndex})">
                        
                        <div id="answer_explanation_img_preview_${FormIndex}" class="mt-2" style="display: none;">
                            <img id="answer_explanation_img_thumbnail_${FormIndex}" src="" alt="Answer Explanation Image" style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; border-radius: 4px; padding: 5px;">
                            <br>
                            <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeAnswerExplanationImgDynamic(${FormIndex})">Remove Image</button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="more_info_link_${FormIndex}" class="form-label">More info link</label>
                        <input type="text" id="more_info_link_${FormIndex}" class="form-control"
                            name="more_info_link_${FormIndex}">
                    </div>
                    <div class="modal-footer">
                        <button id="DeleteQuestionForm" class="btn btn-danger DeleteQuestionForm">Delete Question</button>
                        <!-- <button id="ClearQuestionForm" class="btn btn-secondary ClearQuestionForm">Clear</button> -->
                    </div>
                    </div>
                </div>
            `;

                $('#questionCreate').append(newForm);

                // Wait for DOM to be updated, then resolve
                setTimeout(() => {
                    resolve(FormIndex);
                }, 100);
            });
        }

        let optionFormIndex = 0; // Initialize optionIndex

        $(document).on('change', function(event) {
            optionFormIndex = 0;
            if (event.target.id.startsWith('2-type-')) {
                let selectedIndex = event.target.id.split('-').pop(); // Get the index from the ID
                var selectedType = $(event.target).val(); // Use event.target instead of this

                toggleTypeFields2(selectedType, selectedIndex); // Pass formIndex if needed

            }

        });
        $(document).on('change', function(event) {
            optionFormIndex = 0;
            if (event.target.id.startsWith('answer_type_fraction-')) {
                let formIndex = event.target.id.split('-').pop(); // Get the index from the ID
                var selectedFractionType = $(event.target).val(); // Use event.target instead of this

                toggleTypeFractionFields2(selectedFractionType, formIndex); // Pass formIndex if needed


            }

        });

        function toggleTypeFields2(selectedType, selectedIndex) {

            if (selectedType === 'written') {
                $(`.options_type_${selectedIndex}`).hide();
                $(`.written_type_${selectedIndex}`).show();
                $(`.image_type_${selectedIndex}`).hide();
                $(`#image-options-container-${selectedIndex}`).html('');
                $(`#options-container-${selectedIndex}`).html('');
                $(`.fraction_type_${selectedIndex}`).hide();
                $(`.fraction-button-${selectedIndex}`).hide();
                $(`.fraction-button-${selectedIndex}`).show();

            } else if (selectedType === 'options') {
                $(`.options_type_${selectedIndex}`).show();
                $(`.written_type_${selectedIndex}`).hide();
                $(`.image_type_${selectedIndex}`).hide();
                $(`#image-options-container-${selectedIndex}`).html('');
                $(`#written_${selectedIndex}`).html('');
                $(`.fraction_type_${selectedIndex}`).hide();
                $(`.fraction-button-${selectedIndex}`).hide();

                $(`.fraction-button-${selectedIndex}`).show();

            } else if (selectedType === 'image') {
                $(`.options_type_${selectedIndex}`).hide();
                $(`.written_type_${selectedIndex}`).hide();
                $(`.image_type_${selectedIndex}`).show();
                $(`#options-container-${selectedIndex}`).html('');
                $(`#written_${selectedIndex}`).html('');
                $(`.fraction_type_${selectedIndex}`).hide();
                $(`.fraction-button-${selectedIndex}`).hide();
                $(`.fraction-button-${selectedIndex}`).show();


            } else if (selectedType === 'fraction') {
                $(`.options_type_${selectedIndex}`).hide();
                $(`.written_type_${selectedIndex}`).hide();
                $(`.image_type_${selectedIndex}`).hide();
                $(`#options-container-${selectedIndex}`).html('');
                $(`#written_${selectedIndex}`).html('');
                $(`#image-options-container-${selectedIndex}`).html('');
                $(`.fraction_type_${selectedIndex}`).show();
                $(`.fraction-button-${selectedIndex}`).show();

            } else {
                $(`.options_type_${selectedIndex}`).hide();
                $(`.written_type_${selectedIndex}`).hide();
                $(`.image_type_${selectedIndex}`).hide();
                $(`.fraction_type_${selectedIndex}`).hide();
                $(`.fraction-button-${selectedIndex}`).hide();

            }
        }

        function toggleTypeFractionFields2(selectedFractionType, selectedIndex) {

            if (selectedFractionType === 'written') {
                $(`.options_type_fraction_${selectedIndex}`).hide();
                $(`.written_type_fraction_${selectedIndex}`).show();

            } else if (selectedFractionType === 'options') {
                $(`.written_type_fraction_${selectedIndex}`).hide();
                $(`.options_type_fraction_${selectedIndex}`).show();


            } else {

                $(`.written_type_fraction_${selectedIndex}`).hide();
                $(`.options_type_fraction_${selectedIndex}`).hide();
            }
        }


        function addOption3(formIndex, optionFormIndex, text = '', correct = 0) {
            $(`#options-container-${formIndex}`).append(`
                <div class="option-item mt-2 form-row align-items-center">
                    <div class="col-xs-10 col-sm-10 col-md-10 text-center">
                        <input type="text" class="form-control" name="options[${optionFormIndex}][text]" value="${text}" autocomplete="off" />
                    </div>
                <div class="col-auto text-nowrap">
                        <input type="hidden" name="options[${optionFormIndex}][correct]" value="0">
                        <input type="checkbox" class="text-left" name="options[${optionFormIndex}][correct]" value="1" ${correct ? 'checked' : ''}> Correct&nbsp
                        <button type="button" class="btn btn-danger btn-sm remove-option">&nbsp&nbsp<i class="fa fa-trash"></i>&nbsp&nbsp</button>
                    </div>
                </div>
            `);
        }

        function addOptionImg2(formIndex, optionFormIndex, file = '', correct = 0) {
            $(`#image-options-container-${formIndex}`).append(`
                <div class="img-option-item mt-2 form-row align-items-center">
                    <div class="col-xs-10 col-sm-10 col-md-10 text-center">
                        <input  class="form-control" type="file"  name="image_options[${optionIndex}][file]" value="${file}" autocomplete="off" />
                    </div>
                    <div class="col-auto text-nowrap">
                        <input type="hidden" name="image_options[${optionIndex}][correct]" value="0">
                        <input type="checkbox" class="text-left" name="image_options[${optionIndex}][correct]" value="1" ${correct ? 'checked' : ''}> Correct&nbsp
                        <button type="button" class="btn btn-danger btn-sm remove-option-img">&nbsp&nbsp<i class="fa fa-trash"></i>&nbsp&nbsp</button>
                    </div>
                </div>
        `);
        }
        $(document).on('click', function(event) {
            if (event.target.id.startsWith('add-option-')) {
                let formIndex = event.target.id.split('-').pop();
                optionFormIndex++;
                addOption3(formIndex, optionFormIndex);


            }
        });
        $(document).on('click', function(event) {
            if (event.target.id.startsWith('add-img-option-')) {
                let formIndex = event.target.id.split('-').pop();
                optionFormIndex++;
                addOptionImg2(formIndex, optionFormIndex);

            }
        });

        function addFractionOption2(formIndex, optionFormIndex, numerator = '', denominator = '', answerType =
            'mixed', correct = 0) {
            console.log('formIndex: ' + formIndex);
            $(`#add-fraction-container_${formIndex}`).append(`
                <div class="fraction-item mt-2 form-row align-items-center">
                    <div class="col-xs-3 col-sm-3 col-md-3 text-center">
                        <input type="number" class="form-control" name="answer_numerator[${optionFormIndex}]" value="${numerator}" placeholder="Numerator" required>
                    </div>
                    <div class="col-xs-3 col-sm-3 col-md-3 text-center">
                        <input type="number" class="form-control" name="answer_denominator[${optionFormIndex}]" value="${denominator}" placeholder="Denominator" required min="1">
                    </div>
                    <div class="col-xs-3 col-sm-3 col-md-3 text-center">
                        <select name="answer_type[${optionFormIndex}]" class="form-control">
                            <option value="frac" ${answerType === 'frac' ? 'selected' : ''}>Fraction</option>
                            <option value="mixed" ${answerType === 'mixed' ? 'selected' : ''}>Mixed Fraction</option>
                        </select>
                    </div>
                    <div class="col-auto text-nowrap">
                        <input type="hidden" name="options[${optionFormIndex}][correct]" value="0">
                        <input type="checkbox" class="text-left" name="options[${optionFormIndex}][correct]" value="1" ${correct ? 'checked' : ''}> Correct&nbsp;
                        <button type="button" class="btn btn-danger btn-sm remove-fraction-option"><i class="fa fa-trash"></i></button>
                    </div>
                </div>
            `);
        }

        let optionFormIndexes = {};

        // Handle click event for adding fraction options
        $(document).on('click', '[id^="add-fraction-option-"]', function() {
            let formIndex = this.id.split('-').pop();

            if (!optionFormIndexes[formIndex]) {
                optionFormIndexes[formIndex] = 0; // Initialize if undefined
            }

            optionFormIndexes[formIndex]++; // Increment for this form
            addFractionOption2(formIndex, optionFormIndexes[formIndex]);
        });
        // $(document).on('click', function(event) {
        //     if (event.target.id.startsWith('add-fraction-option-')) {
        //         let formIndex = event.target.id.split('-').pop();
        //         optionFormIndex++;
        //         addFractionOption2(formIndex, optionFormIndex);

        //     }
        // });
        // $(document).on('click', '[id^="add-fraction-option-"]', function() {
        //     let formIndex = this.id.split('-').pop(); // Extract form index
        //     optionFormIndex++; // Increment option index
        //     addFractionOption2(formIndex, optionFormIndex);
        // });

        $('#addOptionImg').on('click', function() {
            addOptionImg();
        });

        function addOptionImg(file = '', correct = 0) {
            $('#image-options-container').append(`
                <div class="img-option-item mt-2 form-row align-items-center">
                    <div class="col-xs-10 col-sm-10 col-md-10 text-center">
                        <input  class="form-control" type="file"  name="image_options[${optionIndex}][file]" value="${file}" autocomplete="off" />
                    </div>
                    <div class="col-auto text-nowrap">
                        <input type="hidden" name="image_options[${optionIndex}][correct]" value="0">
                        <input type="checkbox" class="text-left" name="image_options[${optionIndex}][correct]" value="1" ${correct ? 'checked' : ''}> Correct&nbsp
                        <button type="button" class="btn btn-danger btn-sm remove-option-img">&nbsp&nbsp<i class="fa fa-trash"></i>&nbsp&nbsp</button>
                    </div>
                </div>
        `);
            optionIndex++;
        }
        $(document).on('click', '.remove-option-img', function() {
            $(this).closest('.img-option-item').remove();
        });
        $(document).on('click', '.DeleteQuestionForm', function() {
            var questionForm = $(this).closest('.question-form');
            var questionIndex = $('.question-form').index(questionForm);

            // Remove the form
            questionForm.remove();

            // Update FormIndex to match actual number of forms
            FormIndex = $('.question-form').length;

            // Renumber all remaining question forms
            renumberQuestionForms();
        });

        function renumberQuestionForms() {
            $('.question-form').each(function(index, element) {
                var newIndex = index + 1;
                var $form = $(element);

                // Update the question title
                $form.find('h4').text(`Create Question #${newIndex}`);

                // Update all form field names and IDs to match the new index
                updateFormFieldIndices($form, newIndex);
            });
        }

        function updateFormFieldIndices($form, newIndex) {
            var oldPrefix = newIndex > 1 ? `_${newIndex}` : '';
            var oldPrefixType = newIndex > 1 ? `-${newIndex}` : '';

            // Update select type field
            $form.find(`select[name^="type-"]`).attr('name', `type-${newIndex}`).attr('id',
                `2-type-${newIndex}`);

            // Update text area
            $form.find(`textarea[name^="text_"]`).attr('name', `text_${newIndex}`).attr('id',
                `text_${newIndex}`);

            // Update image question field
            $form.find(`input[name^="imageQuestion_"]`).attr('name', `imageQuestion_${newIndex}`).attr('id',
                `imageQuestion_${newIndex}`);

            // Update written field
            $form.find(`input[name^="written_"]`).attr('name', `written_${newIndex}`).attr('id',
                `written_${newIndex}`);

            // Update grade, term, section fields
            $form.find(`select[name^="level_"]`).attr('name', `level_${newIndex}`).attr('id',
                `level_${newIndex}`);
            $form.find(`select[name^="term_"]`).attr('name', `term_${newIndex}`).attr('id', `term_${newIndex}`);
            $form.find(`select[name^="section_"]`).attr('name', `section_${newIndex}`).attr('id',
                `section_${newIndex}`);

            // Update other fields
            $form.find(`textarea[name^="code_snippet_"]`).attr('name', `code_snippet_${newIndex}`).attr('id',
                `code_snippet_${newIndex}`);
            $form.find(`textarea[name^="answer_explanation_"]`).attr('name', `answer_explanation_${newIndex}`)
                .attr('id', `answer_explanation_${newIndex}`);
            $form.find(`input[name^="answer_explanation_image_"]`).attr('name',
                `answer_explanation_image_${newIndex}`).attr('id', `answer_explanation_image_${newIndex}`);
            $form.find(`input[name^="more_info_link_"]`).attr('name', `more_info_link_${newIndex}`).attr('id',
                `more_info_link_${newIndex}`);

            // Update fraction fields
            $form.find(`select[name^="answer_type_fraction-"]`).attr('name', `answer_type_fraction-${newIndex}`)
                .attr('id', `answer_type_fraction-${newIndex}`);
            $form.find(`input[name^="answer_numerator_"]`).attr('name', `answer_numerator_${newIndex}`).attr(
                'id', `answer_numerator_${newIndex}`);
            $form.find(`input[name^="answer_denominator_"]`).attr('name', `answer_denominator_${newIndex}`)
                .attr('id', `answer_denominator_${newIndex}`);
            $form.find(`select[name^="answer_type-"]`).attr('name', `answer_type-${newIndex}`).attr('id',
                `answer_type-${newIndex}`);

            // Update container IDs
            $form.find(`[id^="options-container-"]`).attr('id', `options-container-${newIndex}`);
            $form.find(`[id^="image-options-container-"]`).attr('id', `image-options-container-${newIndex}`);
            $form.find(`[id^="add-fraction-container_"]`).attr('id', `add-fraction-container_${newIndex}`);

            // Update button IDs
            $form.find(`[id^="add-option-"]`).attr('id', `add-option-${newIndex}`);
            $form.find(`[id^="add-img-option-"]`).attr('id', `add-img-option-${newIndex}`);
            $form.find(`[id^="add-fraction-option-"]`).attr('id', `add-fraction-option-${newIndex}`);

            // Update CSS classes for type fields
            $form.find(`[class*="_${newIndex}"]`).each(function() {
                var $this = $(this);
                var classes = $this.attr('class').split(' ');
                var newClasses = classes.map(function(cls) {
                    if (cls.match(/_\d+$/)) {
                        return cls.replace(/_\d+$/, `_${newIndex}`);
                    }
                    return cls;
                });
                $this.attr('class', newClasses.join(' '));
            });

            // Update fraction button onclick attributes
            $form.find(`[onclick*="insertFracIndex"]`).attr('onclick', `insertFracIndex(${newIndex})`);
            $form.find(`[onclick*="insertMixedIndex"]`).attr('onclick', `insertMixedIndex(${newIndex})`);

            // Update image preview elements
            $form.find(`[id*="question_img_preview_"]`).attr('id', `question_img_preview_${newIndex}`);
            $form.find(`[id*="question_img_thumbnail_"]`).attr('id', `question_img_thumbnail_${newIndex}`);
            $form.find(`[id*="answer_explanation_img_preview_"]`).attr('id',
                `answer_explanation_img_preview_${newIndex}`);
            $form.find(`[id*="answer_explanation_img_thumbnail_"]`).attr('id',
                `answer_explanation_img_thumbnail_${newIndex}`);

            // Update onchange attributes for image inputs
            $form.find(`input[onchange*="showQuestionImgDynamic"]`).attr('onchange',
                `showQuestionImgDynamic(this, ${newIndex})`);
            $form.find(`input[onchange*="showAnswerExplanationImgDynamic"]`).attr('onchange',
                `showAnswerExplanationImgDynamic(this, ${newIndex})`);

            // Update onclick attributes for remove image buttons
            $form.find(`button[onclick*="removeQuestionImgDynamic"]`).attr('onclick',
                `removeQuestionImgDynamic(${newIndex})`);
            $form.find(`button[onclick*="removeAnswerExplanationImgDynamic"]`).attr('onclick',
                `removeAnswerExplanationImgDynamic(${newIndex})`);
        }

        // $('#loading').show();

        $(document).on('click', '#SubmitQuestionForm', function(e) {
            e.preventDefault();
            $('.alert-danger').html('').hide();
            $('.alert-success').html('').hide();

            var confirmed = confirm("Are you sure you want to submit the form?");

            if (confirmed) {
                var quizData = new FormData();

                // Add basic quiz information to quizData
                quizData.append('type', SelectQuiz);
                quizData.append('quiz_id', SelectQuiz === 'create' ? null : SelectQuiz);
                quizData.append('title', $('#title').val());
                quizData.append('slug', $('#slug').val());
                quizData.append('description', $('#description').val());
                quizData.append('level', levelQuiz);
                quizData.append('term', termQuiz);
                quizData.append('section', sectionQuiz);
                quizData.append('public', $('#public').is(':checked') ? 1 : 0);

                // Add question data to quizData
                $('.question-form .question-form-index').each(function(index, element) {
                    var formIndex = index + 1;
                    var prefix = formIndex > 1 ? `_${formIndex}` : '';
                    var prefixType = formIndex > 1 ? `-${formIndex}` : '';
                    var fileInput = document.getElementById(`imageQuestion${prefix}`);
                    var file = fileInput?.files?.[0];

                    // Append question text, type, and other fields to FormData
                    quizData.append('questions[' + index + '][text]', $(element).find(
                        `textarea[name="text${prefix}"]`).val());
                    quizData.append('questions[' + index + '][type]', $(element).find(
                        `select[name="type${prefixType}"]`).val());
                    quizData.append('questions[' + index + '][written]', $(element).find(
                        `input[name="written${prefix}"]`).val());

                    if (file) {
                        quizData.append(`questions[${index}][img_name]`, file);
                    }
                    // Handle options or images
                    var questionType = $(element).find(`select[name="type${prefixType}"]`)
                        .val();
                    if (questionType === 'options') {
                        $(element).find(`.option-item`).each(function(optIndex, optElement) {
                            quizData.append('questions[' + index + '][options][' +
                                optIndex + '][text]', $(optElement).find(
                                    'input[type="text"]').val());
                            quizData.append('questions[' + index + '][options][' +
                                optIndex + '][correct]', $(optElement).find(
                                    'input[type="checkbox"]').is(':checked') ? 1 : 0
                            );
                        });
                    } else if (questionType === 'image') {
                        $(element).find(`.img-option-item`).each(function(optIndex,
                            optElement) {
                            var imgFileInput = $(optElement).find('input[type="file"]')[
                                0];
                            var imgOptionFile = imgFileInput.files[0];
                            if (imgOptionFile) {
                                quizData.append('questions[' + index +
                                    '][imgOptions][' + optIndex + '][file]',
                                    imgOptionFile);
                            }
                            quizData.append('questions[' + index + '][imgOptions][' +
                                optIndex + '][correct]',
                                $(optElement).find('input[type="checkbox"]').is(
                                    ':checked') ? 1 : 0
                            );
                        });
                    } else if (questionType === 'fraction') {
                        quizData.append('questions[' + index + '][answer_type_fraction]', $(
                            element).find(
                            `select[name="answer_type_fraction${prefixType}"]`).val());
                        //ปัญหาอยู่ตรงนี้
                        if ($(element).find(`select[name="answer_type_fraction${prefixType}"]`)
                            .val() === 'written') {
                            quizData.append('questions[' + index + '][answer_numerator]', $(
                                    element).find(`input[name="answer_numerator${prefix}"]`)
                                .val());
                            quizData.append('questions[' + index + '][answer_denominator]', $(
                                element).find(
                                `input[name="answer_denominator${prefix}"]`).val());
                            quizData.append('questions[' + index + '][answer_type]', $(element)
                                .find(`select[name="answer_type${prefixType}"]`).val());

                        } else if ($(element).find(
                                `select[name="answer_type_fraction${prefixType}"]`).val() ===
                            'options') {
                            $(element).find(`.fraction-item`).each(function(optIndex,
                                optElement) {
                                quizData.append('questions[' + index + '][options][' +
                                    optIndex + '][answer_numerator]', $(optElement)
                                    .find(`input[name^="answer_numerator"]`).val());
                                quizData.append('questions[' + index + '][options][' +
                                    optIndex + '][answer_denominator]', $(
                                        optElement).find(
                                        `input[name^="answer_denominator"]`).val());
                                quizData.append('questions[' + index + '][options][' +
                                    optIndex + '][answer_type]', $(optElement).find(
                                        `select[name^="answer_type"]`).val());
                                quizData.append('questions[' + index + '][options][' +
                                    optIndex + '][correct]', $(optElement).find(
                                        'input[type="checkbox"]').is(':checked') ?
                                    1 : 0);
                            });
                        }

                    }

                    quizData.append('questions[' + index + '][code_snippet]', $(element).find(
                        `textarea[name="code_snippet${prefix}"]`).val());
                    quizData.append('questions[' + index + '][level]', $(element).find(
                        `select[name="level${prefix}"]`).val());
                    quizData.append('questions[' + index + '][term]', $(element).find(
                        `select[name="term${prefix}"]`).val());
                    quizData.append('questions[' + index + '][section]', $(element).find(
                        `select[name="section${prefix}"]`).val());
                    quizData.append('questions[' + index + '][answer_explanation]', $(element)
                        .find(`textarea[name="answer_explanation${prefix}"]`).val());

                    // Handle answer explanation image
                    var answerExplanationImageInput = document.getElementById(
                        `answer_explanation_image${prefix}`);
                    if (answerExplanationImageInput && answerExplanationImageInput.files &&
                        answerExplanationImageInput.files[0]) {
                        quizData.append(`questions[${index}][answer_explanation_image]`,
                            answerExplanationImageInput.files[0]);
                    }

                    quizData.append('questions[' + index + '][more_info_link]', $(element).find(
                        `input[name="more_info_link${prefix}"]`).val());


                });

                //add loading
                // $('#loading').show();

                $.ajax({
                    url: "{{ route('questions-quizzes.store') }}",
                    method: 'post',
                    data: quizData,
                    processData: false, // Prevent jQuery from processing the data
                    contentType: false, // Prevent jQuery from setting the content type
                    success: function(response) {
                        console.log('Response:', response);
                        if (response.success) {
                            // Clear auto-saved data on successful submission
                            clearAutoSavedData();

                            //how to reload page after 3 seconds

                            toastr.success(response.success, {
                                timeOut: 3000


                            });
                            setTimeout(function() {
                                location.reload();
                            }, 3000);

                        } else {
                            toastr.error(response.message || 'เกิดข้อผิดพลาด', {
                                timeOut: 5000
                            });
                            // location.reload();
                        }
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON.errors;
                        var errorList = '';
                        $.each(errors, function(key, messages) {
                            $.each(messages, function(index, message) {
                                errorList += '<li>' + message + '</li>';
                            });
                        });
                        $('.alert-danger').show().html('<strong>' + errorList +
                            '</strong>');
                        toastr.error(errorList, {
                            timeOut: 5000
                        });
                        // $('#loading').hide();
                    }
                });
            }
        });


        const AUTO_SAVE_KEY = 'questionquiz_autosave_data';
        const AUTO_SAVE_INTERVAL = 2000; // Save every 2 seconds
        let autoSaveTimer;

        // Load saved data on page load
        async function loadAutoSavedData() {
            try {
                const savedData = localStorage.getItem(AUTO_SAVE_KEY);
                if (savedData) {
                    const data = JSON.parse(savedData);
                    console.log('Loading auto-saved data:', data);

                    // Restore basic quiz data
                    if (data.title) $('#title').val(data.title);
                    if (data.slug) $('#slug').val(data.slug);
                    if (data.description) $('#description').val(data.description);
                    if (data.level) $('#level').val(data.level);
                    if (data.term) $('#term').val(data.term);
                    if (data.section) $('#section').val(data.section);
                    if (data.public !== undefined) $('#public').prop('checked', data.public);
                    if (data.SelectQuiz) $('#SelectQuiz').val(data.SelectQuiz);

                    // Restore questions data
                    if (data.questions && data.questions.length > 0) {
                        console.log('Starting to restore questions data...');
                        await restoreQuestionsData(data.questions);
                        console.log('Questions data restoration completed');
                    }

                    toastr.info('ข้อมูลที่บันทึกไว้ถูกกู้คืนแล้ว', {
                        timeOut: 3000
                    });
                }
            } catch (error) {
                console.error('Error loading auto-saved data:', error);
                toastr.error('เกิดข้อผิดพลาดในการโหลดข้อมูลที่บันทึกไว้', {
                    timeOut: 3000
                });
            }
        }

        // Save form data to localStorage
        function saveFormData() {
            try {
                const formData = {
                    title: $('#title').val(),
                    slug: $('#slug').val(),
                    description: $('#description').val(),
                    level: $('#level').val(),
                    term: $('#term').val(),
                    section: $('#section').val(),
                    public: $('#public').is(':checked'),
                    SelectQuiz: $('#SelectQuiz').val(),
                    questions: collectQuestionsData(),
                    timestamp: new Date().toISOString()
                };

                localStorage.setItem(AUTO_SAVE_KEY, JSON.stringify(formData));
                console.log('Form data auto-saved');
            } catch (error) {
                console.error('Error saving form data:', error);
            }
        }

        // Collect all questions data
        function collectQuestionsData() {
            const questions = [];
            $('.question-form .question-form-index').each(function(index, element) {
                const formIndex = index + 1;
                const prefix = formIndex > 1 ? `_${formIndex}` : '';
                const prefixType = formIndex > 1 ? `-${formIndex}` : '';

                const questionData = {
                    text: $(element).find(`textarea[name="text${prefix}"]`).val(),
                    type: $(element).find(`select[name="type${prefixType}"]`).val(),
                    written: $(element).find(`input[name="written${prefix}"]`).val(),
                    level: $(element).find(`select[name="level${prefix}"]`).val(),
                    term: $(element).find(`select[name="term${prefix}"]`).val(),
                    section: $(element).find(`select[name="section${prefix}"]`).val(),
                    code_snippet: $(element).find(`textarea[name="code_snippet${prefix}"]`).val(),
                    answer_explanation: $(element).find(
                        `textarea[name="answer_explanation${prefix}"]`).val(),
                    more_info_link: $(element).find(`input[name="more_info_link${prefix}"]`).val(),
                    options: [],
                    fractionData: {}
                };

                // Collect options
                $(element).find('.option-item').each(function(optIndex, optElement) {
                    questionData.options.push({
                        text: $(optElement).find('input[type="text"]').val(),
                        correct: $(optElement).find('input[type="checkbox"]').is(
                            ':checked')
                    });
                });

                // Collect fraction data
                const questionType = questionData.type;
                if (questionType === 'fraction') {
                    questionData.fractionData = {
                        answer_type_fraction: $(element).find(
                            `select[name="answer_type_fraction${prefixType}"]`).val(),
                        answer_numerator: $(element).find(`input[name="answer_numerator${prefix}"]`)
                            .val(),
                        answer_denominator: $(element).find(
                            `input[name="answer_denominator${prefix}"]`).val(),
                        answer_type: $(element).find(`select[name="answer_type${prefixType}"]`)
                            .val(),
                        fractionOptions: []
                    };

                    $(element).find('.fraction-item').each(function(optIndex, optElement) {
                        questionData.fractionData.fractionOptions.push({
                            answer_numerator: $(optElement).find(
                                    `input[name^="answer_numerator"]`)
                                .val(),
                            answer_denominator: $(optElement).find(
                                `input[name^="answer_denominator"]`).val(),
                            answer_type: $(optElement).find(
                                `select[name^="answer_type"]`).val(),
                            correct: $(optElement).find('input[type="checkbox"]').is(
                                ':checked')
                        });
                    });
                }

                questions.push(questionData);
            });

            return questions;
        }

        // Restore questions data
        async function restoreQuestionsData(questions) {
            // Clear existing questions first (except the first one)
            $('.question-form').not(':first').remove();

            // Reset FormIndex to start from 1
            FormIndex = 1;

            // Reset option form indexes
            optionFormIndex = 0;
            if (typeof optionFormIndexes !== 'undefined') {
                optionFormIndexes = {};
            }

            // Process questions sequentially to ensure proper form creation
            for (let index = 0; index < questions.length; index++) {
                const questionData = questions[index];
                console.log(questions);

                console.log(questionData);

                if (index > 0) {

                    console.log('test');
                    try {
                        await addForm2();
                        console.log(`Form ${FormIndex} created successfully`);
                    } catch (error) {
                        console.error('Error creating form:', error);
                        return;
                    }
                }

                const formIndex = index + 1;
                const prefix = formIndex > 1 ? `_${formIndex}` : '';
                const prefixType = formIndex > 1 ? `-${formIndex}` : '';
                const element = $('.question-form').eq(index).find('.question-form-index');

                // Wait a bit for DOM to be ready
                await new Promise(resolve => setTimeout(resolve, 200));

                // Restore basic question data
                if (questionData.text) element.find(`textarea[name="text${prefix}"]`).val(questionData
                    .text);
                if (questionData.type) {
                    element.find(`select[name="type${prefixType}"]`).val(questionData.type).trigger(
                        'change');

                    // Wait for type change to process, then toggle fields
                    await new Promise(resolve => {
                        setTimeout(() => {
                            if (typeof toggleTypeFields2 === 'function') {
                                toggleTypeFields2(questionData.type, formIndex);
                            }
                            resolve();
                        }, 300);
                    });
                }
                if (questionData.written) element.find(`input[name="written${prefix}"]`).val(questionData
                    .written);
                if (questionData.level) element.find(`select[name="level${prefix}"]`).val(questionData
                    .level);
                if (questionData.term) element.find(`select[name="term${prefix}"]`).val(questionData.term);
                if (questionData.section) element.find(`select[name="section${prefix}"]`).val(questionData
                    .section);
                if (questionData.code_snippet) element.find(`textarea[name="code_snippet${prefix}"]`).val(
                    questionData
                    .code_snippet);
                if (questionData.answer_explanation) element.find(
                    `textarea[name="answer_explanation${prefix}"]`).val(
                    questionData.answer_explanation);
                if (questionData.more_info_link) element.find(`input[name="more_info_link${prefix}"]`).val(
                    questionData
                    .more_info_link);

                // Restore options
                if (questionData.options && questionData.options.length > 0) {
                    // Initialize option index for this form if not exists
                    if (!optionFormIndexes[formIndex]) {
                        optionFormIndexes[formIndex] = 0;
                    }

                    // Wait a bit more for type fields to be ready
                    await new Promise(resolve => setTimeout(resolve, 400));

                    for (let optIndex = 0; optIndex < questionData.options.length; optIndex++) {
                        const option = questionData.options[optIndex];
                        console.log('typeof addOption3 : ' + typeof addOption3);

                        if (index > 0) {
                            optionFormIndexes[formIndex]++;
                            if (typeof addOption3 === 'function') {

                                await addOption3(formIndex, optionFormIndexes[formIndex]);
                                // Wait for option to be added to DOM
                                await new Promise(resolve => setTimeout(resolve, 100));
                                console.log('Option ' + optionFormIndexes[formIndex] +
                                    ' added successfully');
                            } else {
                                console.warn('addOption3 function not available');
                            }
                        } else {
                            optionFormIndexes[formIndex]++;
                            if (typeof addOption2 === 'function') {

                                await addOption2(formIndex, optionFormIndexes[formIndex]);
                                // Wait for option to be added to DOM
                                await new Promise(resolve => setTimeout(resolve, 100));
                                console.log('Option ' + optionFormIndexes[formIndex] +
                                    ' added successfully');
                            } else {
                                console.warn('addOption2 function not available');
                            }

                        }

                        const optionElement = element.find('.option-item').eq(optIndex);
                        optionElement.find('input[type="text"]').val(option.text);
                        optionElement.find('input[type="checkbox"]').prop('checked', option.correct);
                    }
                }

                // Restore fraction data
                if (questionData.fractionData && questionData.type === 'fraction') {
                    // Wait for fraction fields to be ready
                    await new Promise(resolve => setTimeout(resolve, 500));

                    if (questionData.fractionData.answer_type_fraction) {
                        element.find(`select[name="answer_type_fraction${prefixType}"]`).val(questionData
                            .fractionData
                            .answer_type_fraction).trigger('change');
                        if (typeof toggleTypeFractionFields2 === 'function') {
                            toggleTypeFractionFields2(questionData.fractionData.answer_type_fraction,
                                formIndex);
                        }
                    }

                    if (questionData.fractionData.answer_numerator) element.find(
                        `input[name="answer_numerator${prefix}"]`).val(questionData.fractionData
                        .answer_numerator);
                    if (questionData.fractionData.answer_denominator) element.find(
                        `input[name="answer_denominator${prefix}"]`).val(questionData.fractionData
                        .answer_denominator);
                    if (questionData.fractionData.answer_type) element.find(
                            `select[name="answer_type${prefixType}"]`)
                        .val(questionData.fractionData.answer_type);

                    // Restore fraction options
                    if (questionData.fractionData.fractionOptions && questionData.fractionData
                        .fractionOptions.length >
                        0) {
                        for (let optIndex = 0; optIndex < questionData.fractionData.fractionOptions
                            .length; optIndex++) {
                            const fractionOption = questionData.fractionData.fractionOptions[optIndex];
                            console.log('index: ' + index);

                            if (index > 0) {
                                addFractionOption2(index + 1, optIndex);
                                console.log('optIndex2: ' + optIndex);

                            } else {
                                addFractionOption();
                                console.log('optIndex1: ' + optIndex);

                            }

                            await new Promise(resolve => setTimeout(resolve, 100));


                            const fractionElement = element.find('.fraction-item').eq(optIndex);
                            fractionElement.find(`input[name^="answer_numerator"]`).val(fractionOption
                                .answer_numerator);
                            fractionElement.find(`input[name^="answer_denominator"]`).val(fractionOption
                                .answer_denominator);
                            fractionElement.find(`select[name^="answer_type"]`).val(fractionOption
                                .answer_type);
                            fractionElement.find('input[type="checkbox"]').prop('checked', fractionOption
                                .correct);

                        }

                    }
                }
            }
        }

        // Clear auto-saved data
        function clearAutoSavedData() {
            try {
                localStorage.removeItem(AUTO_SAVE_KEY);
                console.log('Auto-saved data cleared');
            } catch (error) {
                console.error('Error clearing auto-saved data:', error);
            }
        }

        // Setup auto-save listeners
        function setupAutoSave() {
            // Save on form field changes
            $(document).on('input change', 'input, textarea, select', function() {
                clearTimeout(autoSaveTimer);
                autoSaveTimer = setTimeout(saveFormData, AUTO_SAVE_INTERVAL);
            });

            // Save when adding/removing options or questions
            $(document).on('click',
                '.addOption2, .remove-option, #AddQuestionForm, .DeleteQuestionForm, #add-fraction-option, .remove-fraction-option',
                function() {
                    setTimeout(saveFormData, 500); // Delay to allow DOM updates
                });

            // Save before page unload
            $(window).on('beforeunload', function() {
                saveFormData();
            });
        }

        // Load auto-saved data after all functions are defined
        setTimeout(function() {
            if (typeof loadAutoSavedData === 'function') {
                loadAutoSavedData();
            }
        }, 2000); // Delay to ensure all functions are loaded

    });
</script>
