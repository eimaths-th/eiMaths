<script>
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
        var token = '{{ csrf_token() }}';
        let FormIndex = 1;
        let SelectQuiz;


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

                var url = '{{ route('questions-quizzes.selected', ':id') }}';
                url = url.replace(':id', SelectQuiz);

                $.ajax({
                    // url: "selected/" + SelectQuiz,
                    url: url,
                    method: 'get',
                    success: function(res) {
                        $('#questions_id').html(res
                            .html_questions_select); // Update the select options
                    },
                    error: function(xhr) {
                        console.error('Failed to fetch questions:', xhr.responseText);
                    }
                });

                $('#quizCrate').hide();

            }
        });


        function toggleTypeFields(selectedType, res) {
            if (selectedType === 'written') {
                $('.options_type').hide();
                $('.written_type').show();
                if (res) {
                    $('#written').val(res.question.written_answer); // Set written answer if applicable

                }
            } else if (selectedType === 'options') {
                $('.written_type').hide();
                $('.options_type').show();
            } else {
                $('.written_type').hide();
                $('.options_type').hide();
            }
        }

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

        $('.addOption2').on('click', function() {
            addOption2();
        });

        $(document).on('change', '#type', function() {
            var selectedType = $(this).val();
            toggleTypeFields(selectedType);
        });

        $(document).on('click', '.remove-option', function() {
            $(this).closest('.option-item').remove();
        });

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
        });

        function addForm2() {
            FormIndex++;

            let newForm = `
                <div class="question-form">
                    <div class="question-form-index">
                    <h4 class="mb-4">Create Question #${FormIndex}</h4>
                    <div class="mb-3">
                        <label for="text_${FormIndex}" class="form-label">Question text</label>
                        <textarea id="text_${FormIndex}" class="form-control" name="text_${FormIndex}"></textarea>
                    </div>
                    <div class="mb-3">
                        <select name="type-${FormIndex}" id="type-${FormIndex}" class="form-control">
                            <option value="">Select type answer</option>
                            <option value="options">Options</option>
                            <option value="written">Written</option>
                        </select>
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

        $(`#type-${FormIndex}`).on('change', function() {

            var selectedType = $(this).val();
            toggleTypeFields2(selectedType);
        });
        $(`#type-${FormIndex}`).change(function() {
            var selectedType = $(this).val();
            toggleTypeFields2(selectedType);
        });

        $(document).on('change', `#type-${FormIndex}`, function() {
            var selectedType = $(this).val();
            toggleTypeFields2(selectedType);
        });

        $(document).on('change', function(event) {
            if (event.target.id.startsWith('type-')) {
                let formIndex = event.target.id.split('-').pop(); // Get the index from the ID
                var selectedType = $(event.target).val(); // Use event.target instead of this
                toggleTypeFields2(selectedType, formIndex); // Pass formIndex if needed
            }
        });

        function toggleTypeFields2(selectedType) {
            if (selectedType === 'written') {
                $(`.options_type_${FormIndex}`).hide();
                $(`.written_type_${FormIndex}`).show();
            } else if (selectedType === 'options') {
                $(`.options_type_${FormIndex}`).show();
                $(`.written_type_${FormIndex}`).hide();
            } else {
                $(`.options_type_${FormIndex}`).hide();
                $(`.written_type_${FormIndex}`).hide();
            }
        }

        let optionFormIndex = 0; // Initialize optionIndex

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

        $(document).on('click', function(event) {
            if (event.target.id.startsWith('add-option-')) {
                let formIndex = event.target.id.split('-').pop();
                optionFormIndex++;
                addOption3(formIndex, optionFormIndex);
            }
        });

        $(document).on('click', '.DeleteQuestionForm', function() {
            FormIndex--;
            $(this).closest('.question-form').remove();

        });

        $(document).on('click', '#SubmitQuestionForm', function(e) {
            e.preventDefault();
            $('.alert-danger').html('').hide();
            $('.alert-success').html('').hide();
            console.log(SelectQuiz);

            var confirmed = confirm("Are you sure you want to submit the form?");

            if (confirmed) {
                if (SelectQuiz === 'create') {
                    var quizData = {
                        type: '1',
                        title: $('#title').val(),
                        slug: $('#slug').val(),
                        description: $('#description').val(),
                        level: $('#level').val(),
                        term: $("#term").val(),
                        section: $("#section").val(),
                        published: $('#published').is(':checked') ? 1 : 0,
                        public: $('#public').is(':checked') ? 1 : 0,
                        _token: token
                    };
                } else {
                    var quizData = {
                        type: '0',
                        quiz_id: SelectQuiz,
                        _token: token
                    };
                }
                var questions = [];

                $('.question-form .question-form-index').each(function(index, element) {
                    var formIndex = index + 1;
                    var prefix = formIndex > 1 ? `_${formIndex}` : '';
                    var prefixType = formIndex > 1 ? `-${formIndex}` : '';

                    var questionText = $(element).find(`textarea[name="text${prefix}"]`).val();
                    var questionType = $(element).find(`select[name="type${prefixType}"]`)
                        .val();
                    var writtenAnswer = $(element).find(`input[name="written${prefix}"]`).val();
                    var options = [];

                    $(element).find(`.option-item`).each(function(optIndex, optElement) {
                        var optionText = $(optElement).find('input[type="text"]').val();
                        var isCorrect = $(optElement).find('input[type="checkbox"]').is(
                            ':checked') ? 1 : 0;
                        options.push({
                            text: optionText,
                            correct: isCorrect
                        });
                    });

                    questions.push({
                        text: questionText,
                        type: questionType,
                        written: writtenAnswer,
                        options: options,
                        code_snippet: $(element).find(
                            `textarea[name="code_snippet${prefix}"]`).val(),
                        answer_explanation: $(element).find(
                            `textarea[name="answer_explanation${prefix}"]`).val(),
                        more_info_link: $(element).find(
                            `input[name="more_info_link${prefix}"]`).val()
                    });
                });

                var questions_id = $('#questions_id').val();

                quizData['questions'] = questions;
                quizData['questions_id'] = questions_id;
                console.log(quizData);

                $.ajax({
                    url: "{{ route('questions-quizzes.store') }}",
                    method: 'post',
                    data: quizData,
                    success: function(result) {
                        console.log(result);

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
                            $('.form').trigger('reset');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 || xhr.status === 400) {
                            var errors = xhr.responseJSON.errors || [xhr.responseJSON
                                .errors
                            ];
                            var errorList = '';
                            $.each(errors, function(key, value) {
                                errorList += '<li>' + value + '</li>';
                            });
                            $('.alert-danger').show().html('<strong>' + errorList +
                                '</strong>');
                        }
                    }
                });
            }
        });



    });
</script>
