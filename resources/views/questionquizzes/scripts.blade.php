<script>
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

    $(document).ready(function() {
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

        function toggleTypeFields(selectedType, res) {
            if (selectedType === 'written') {
                $('.options_type').hide();
                $('.written_type').show();
                $('.image_type').hide();
                $('.fraction_type').hide();
                $('.fraction-button').hide();

                if (res) {
                    $('#written').val(res.question.written_answer); // Set written answer if applicable

                }
            } else if (selectedType === 'options') {
                $('.written_type').hide();
                $('.options_type').show();
                $('.image_type').hide();
                $('.fraction_type').hide();
                $('.fraction-button').hide();


            } else if (selectedType === 'image') {
                $('.written_type').hide();
                $('.options_type').hide();
                $('.image_type').show();
                $('.fraction_type').hide();
                $('.fraction-button').hide();


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
                            <input type="file" id="imageQuestion_${FormIndex}" name="imageQuestion_${FormIndex}" class="form-control">
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
        }

        let optionFormIndex = 0; // Initialize optionIndex

        $(document).on('change', function(event) {
            optionFormIndex = 0;
            if (event.target.id.startsWith('2-type-')) {
                let formIndex = event.target.id.split('-').pop(); // Get the index from the ID
                var selectedType = $(event.target).val(); // Use event.target instead of this
                // console.log(selectedType);
                // console.log(formIndex);
                toggleTypeFields2(selectedType, formIndex); // Pass formIndex if needed

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

        function toggleTypeFields2(selectedType) {
            if (selectedType === 'written') {
                $(`.options_type_${FormIndex}`).hide();
                $(`.written_type_${FormIndex}`).show();
                $(`.image_type_${FormIndex}`).hide();
                $(`#image-options-container-${FormIndex}`).html('');
                $(`#options-container-${FormIndex}`).html('');
                $(`.fraction_type_${FormIndex}`).hide();
                $(`.fraction-button-${FormIndex}`).hide();

            } else if (selectedType === 'options') {
                $(`.options_type_${FormIndex}`).show();
                $(`.written_type_${FormIndex}`).hide();
                $(`.image_type_${FormIndex}`).hide();
                $(`#image-options-container-${FormIndex}`).html('');
                $(`#written_${FormIndex}`).html('');
                $(`.fraction_type_${FormIndex}`).hide();
                $(`.fraction-button-${FormIndex}`).hide();

            } else if (selectedType === 'image') {
                $(`.options_type_${FormIndex}`).hide();
                $(`.written_type_${FormIndex}`).hide();
                $(`.image_type_${FormIndex}`).show();
                $(`#options-container-${FormIndex}`).html('');
                $(`#written_${FormIndex}`).html('');
                $(`.fraction_type_${FormIndex}`).hide();
                $(`.fraction-button-${FormIndex}`).hide();

            } else if (selectedType === 'fraction') {
                $(`.options_type_${FormIndex}`).hide();
                $(`.written_type_${FormIndex}`).hide();
                $(`.image_type_${FormIndex}`).hide();
                $(`#options-container-${FormIndex}`).html('');
                $(`#written_${FormIndex}`).html('');
                $(`#image-options-container-${FormIndex}`).html('');
                $(`.fraction_type_${FormIndex}`).show();
                $(`.fraction-button-${FormIndex}`).show();

            } else {
                $(`.options_type_${FormIndex}`).hide();
                $(`.written_type_${FormIndex}`).hide();
                $(`.image_type_${FormIndex}`).hide();
                $(`.fraction_type_${FormIndex}`).hide();
                $(`.fraction-button-${FormIndex}`).hide();

            }
        }

        function toggleTypeFractionFields2(selectedFractionType, res) {
            console.log(selectedFractionType);
            console.log(res);

            if (selectedFractionType === 'written') {
                $(`.options_type_fraction_${FormIndex}`).hide();
                $(`.written_type_fraction_${FormIndex}`).show();

            } else if (selectedFractionType === 'options') {
                $(`.written_type_fraction_${FormIndex}`).hide();
                $(`.options_type_fraction_${FormIndex}`).show();


            } else {

                $(`.written_type_fraction_${FormIndex}`).hide();
                $(`.options_type_fraction_${FormIndex}`).hide();
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
            FormIndex--;
            $(this).closest('.question-form').remove();

        });

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
                        quizData.append('questions[' + index + '][answer_type_fraction]', $(element).find(`select[name="answer_type_fraction${prefixType}"]`).val());
                        //ปัญหาอยู่ตรงนี้
                        if ($(element).find(`select[name="answer_type_fraction${prefixType}"]`).val() === 'written') {
                            quizData.append('questions[' + index + '][answer_numerator]', $(element).find(`input[name="answer_numerator${prefix}"]`).val());
                            quizData.append('questions[' + index + '][answer_denominator]', $(element).find(`input[name="answer_denominator${prefix}"]`).val());
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
                    quizData.append('questions[' + index + '][more_info_link]', $(element).find(
                        `input[name="more_info_link${prefix}"]`).val());

                    // console.log('formIndex' + formIndex);
                    // console.log('prefix' + prefix);

                    // console.log('answer_numerator' + prefix + ' :' + $(element).find(`input[name="answer_numerator${prefix}"]`).val());
                    // console.log('answer_denominator' + prefix + ' :' + $(element).find(`input[name="answer_denominator${prefix}"]`).val());
                    // console.log('answer_type' + prefix + ' :' + $(element).find(`select[name="answer_type${prefixType}"]`).val());
                });



                $.ajax({
                    url: "{{ route('questions-quizzes.store') }}",
                    method: 'post',
                    data: quizData,
                    processData: false, // Prevent jQuery from processing the data
                    contentType: false, // Prevent jQuery from setting the content type
                    success: function(result) {

                        if (result.errors) {
                            $('.alert-danger').html('').show().append('<strong><li>' +
                                result
                                .errors.join('</li><li>') + '</li></strong>');
                        } else {
                            $('.alert-danger').hide();
                            $('.alert-success').show().append('<strong><li>' + result
                                .success +
                                '</li></strong>');
                            toastr.success(result.success, {
                                timeOut: 5000
                            });
                            $('.SelectQuiz').val('').trigger('change');
                            $('.SelectType').val('').trigger('change');
                            $('.questionsId').val('').trigger('change');
                            $('.answer_type_fraction').val('').trigger('change');
                            // $('.question-form').remove();
                            $('.form').trigger('reset');

                            location.reload();
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
                    }
                });
            }
        });

        // $(document).on('click', '#SubmitQuestionForm', function(e) {
        //     e.preventDefault();
        //     $('.alert-danger').html('').hide();
        //     $('.alert-success').html('').hide();

        //     var confirmed = confirm("Are you sure you want to submit the form?");
        //     if (!confirmed) return;

        //     var quizData = new FormData();
        //     var errors = [];

        //     // Collect Quiz Data
        //     quizData.append('type', SelectQuiz);
        //     quizData.append('quiz_id', SelectQuiz === 'create' ? null : SelectQuiz);
        //     quizData.append('title', $('#title').val());
        //     quizData.append('slug', $('#slug').val());
        //     quizData.append('description', $('#description').val());
        //     quizData.append('level', levelQuiz);
        //     quizData.append('term', termQuiz);
        //     quizData.append('section', sectionQuiz);
        //     quizData.append('public', $('#public').is(':checked') ? 1 : 0);

        //     if (!$('#title').val().trim()) errors.push("Title is required.");
        //     if (!$('#slug').val().trim()) errors.push("Slug is required.");

        //     // Loop through questions
        //     $('.question-form .question-form-index').each(function(index, element) {
        //         var prefix = index > 0 ? `_${index + 1}` : '';
        //         var prefixType = index > 0 ? `-${index + 1}` : '';

        //         var questionText = $(element).find(`textarea[name="text${prefix}"]`).val()
        //         .trim();
        //         var questionType = $(element).find(`select[name="type${prefixType}"]`).val();

        //         if (!questionText) errors.push(`Question ${index + 1} text is required.`);
        //         if (!questionType) errors.push(`Question ${index + 1} type is required.`);

        //         quizData.append(`questions[${index}][text]`, questionText);
        //         quizData.append(`questions[${index}][type]`, questionType);

        //         // Handle Image Upload
        //         var fileInput = $(element).find('input[name="imageQuestion"]')[0];
        //         if (fileInput && fileInput.files.length > 0) {
        //             quizData.append(`questions[${index}][img_name]`, fileInput.files[0]);
        //         }

        //         // Handle Question Types
        //         if (questionType === 'options') {
        //             $(element).find('.option-item').each(function(optIndex, optElement) {
        //                 var optionText = $(optElement).find('input[type="text"]').val()
        //                     .trim();
        //                 if (!optionText) errors.push(
        //                     `Option ${optIndex + 1} for Question ${index + 1} is required.`
        //                     );
        //                 quizData.append(
        //                     `questions[${index}][options][${optIndex}][text]`,
        //                     optionText);
        //                 quizData.append(
        //                     `questions[${index}][options][${optIndex}][correct]`, $(
        //                         optElement).find('input[type="checkbox"]').is(
        //                         ':checked') ? 1 : 0);
        //             });
        //         } else if (questionType === 'fraction') {
        //             var answerType = $(element).find(
        //                 `select[name="answer_type_fraction${prefixType}"]`).val();
        //             quizData.append(`questions[${index}][answer_type_fraction]`, answerType);

        //             if (!answerType) errors.push(
        //                 `Answer type for fraction Question ${index + 1} is required.`);

        //             if (answerType === 'written') {
        //                 var numerator = $(element).find(
        //                     `input[name="answer_numerator${prefix}"]`).val().trim();
        //                 var denominator = $(element).find(
        //                     `input[name="answer_denominator${prefix}"]`).val().trim();
        //                 if (!numerator || !denominator) errors.push(
        //                     `Fraction Question ${index + 1} requires both numerator and denominator.`
        //                     );
        //                 quizData.append(`questions[${index}][answer_numerator]`, numerator);
        //                 quizData.append(`questions[${index}][answer_denominator]`, denominator);
        //             } else if (answerType === 'options') {
        //                 $(element).find('.fraction-item').each(function(optIndex, optElement) {
        //                     var num = $(optElement).find(
        //                         `input[name^="answer_numerator"]`).val().trim();
        //                     var den = $(optElement).find(
        //                         `input[name^="answer_denominator"]`).val().trim();
        //                     if (!num || !den) errors.push(
        //                         `Fraction option ${optIndex + 1} for Question ${index + 1} requires both numerator and denominator.`
        //                         );
        //                     quizData.append(
        //                         `questions[${index}][options][${optIndex}][answer_numerator]`,
        //                         num);
        //                     quizData.append(
        //                         `questions[${index}][options][${optIndex}][answer_denominator]`,
        //                         den);
        //                     quizData.append(
        //                         `questions[${index}][options][${optIndex}][correct]`,
        //                         $(optElement).find('input[type="checkbox"]').is(
        //                             ':checked') ? 1 : 0);
        //                 });
        //             }
        //         }
        //     });

        //     if (errors.length > 0) {
        //         $('.alert-danger').html('<strong>' + errors.join('<br>') + '</strong>').show();
        //         toastr.error("Please fix the errors before submitting.", {
        //             timeOut: 5000
        //         });
        //         return;
        //     }

        //     $.ajax({
        //         url: "{{ route('questions-quizzes.store') }}",
        //         method: 'post',
        //         data: quizData,
        //         processData: false,
        //         contentType: false,
        //         success: function(result) {
        //             if (result.errors) {
        //                 $('.alert-danger').html('<strong>' + result.errors.join('<br>') +
        //                     '</strong>').show();
        //             } else {
        //                 $('.alert-danger').hide();
        //                 $('.alert-success').show().append('<strong>' + result.success +
        //                     '</strong>');
        //                 toastr.success(result.success, {
        //                     timeOut: 5000
        //                 });
        //                 $('.form').trigger('reset');
        //             }
        //         },
        //         error: function(xhr) {
        //             var errors = xhr.responseJSON.errors;
        //             var errorList = '';
        //             $.each(errors, function(key, messages) {
        //                 $.each(messages, function(index, message) {
        //                     errorList += '<li>' + message + '</li>';
        //                 });
        //             });
        //             $('.alert-danger').show().html('<strong>' + errorList + '</strong>');
        //             toastr.error(errorList, {
        //                 timeOut: 5000
        //             });
        //         }
        //     });
        // });


    });
</script>
