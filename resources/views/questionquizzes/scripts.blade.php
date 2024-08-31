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
        $('.image_type').hide();
        var token = '{{ csrf_token() }}';
        let FormIndex = 1;
        let SelectQuiz = '';
        let levelQuiz = '';
        let termQuiz = '';
        let sectionQuiz = '';
        // console.log(SelectQuiz);
        $(document).on('change', '#level', function() {
            levelQuiz = $(this).val(); // Get selected quiz ID
        });
        $(document).on('change', '#section', function() {
            sectionQuiz = $(this).val(); // Get selected quiz ID
        });
        $(document).on('change', '#term', function() {
            termQuiz = $(this).val(); // Get selected quiz ID
        });


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
                $('.image_type').hide();

                if (res) {
                    $('#written').val(res.question.written_answer); // Set written answer if applicable
                }
            } else if (selectedType === 'options') {
                $('.written_type').hide();
                $('.options_type').show();
                $('.image_type').hide();
                $('#image-options-container').html('');


            } else if (selectedType === 'image') {
                $('.written_type').hide();
                $('.options_type').hide();
                $('.image_type').show();
                $('#options-container').html('');


            } else {
                $('.written_type').hide();
                $('.options_type').hide();
                $('.image_type').hide();

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

        $(document).on('change', '#SelectType', function() {
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
            $(`.image_type_${FormIndex}`).hide();

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
                        <label for="imageQuestion_${FormIndex}" class="form-label">Question Image</label>
                        <div id="add-image-question-container_${FormIndex}">
                            <input type="file" id="imageQuestion_${FormIndex}" name="imageQuestion_${FormIndex}" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <select name="type-${FormIndex}" id="2-type-${FormIndex}" class="form-control">
                            <option value="">Select type answer</option>
                            <option value="options">Options</option>
                            <option value="written">Written</option>
                            <option value="image">Image</option>
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
                    <div class="mb-3 image_type_${FormIndex}">
                        <label for="image_${FormIndex}" class="form-label">Question Image Options</label> <br>
                        <button type="button" class="btn btn-sm btn-primary mt-2" id="add-img-option-${FormIndex}">Add</button>
                        <div id="image-options-container-${FormIndex}">

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

                toggleTypeFields2(selectedType, formIndex); // Pass formIndex if needed

            }

        });

        function toggleTypeFields2(selectedType) {
            if (selectedType === 'written') {
                $(`.options_type_${FormIndex}`).hide();
                $(`.written_type_${FormIndex}`).show();
                $(`.image_type_${FormIndex}`).hide();
                $(`#image-options-container-${FormIndex}`).html('');
                $(`#options-container-${FormIndex}`).html('');

            } else if (selectedType === 'options') {
                $(`.options_type_${FormIndex}`).show();
                $(`.written_type_${FormIndex}`).hide();
                $(`.image_type_${FormIndex}`).hide();
                $(`#image-options-container-${FormIndex}`).html('');
                $(`#written_${FormIndex}`).html('');

            } else if (selectedType === 'image') {
                $(`.options_type_${FormIndex}`).hide();
                $(`.written_type_${FormIndex}`).hide();
                $(`.image_type_${FormIndex}`).show();
                $(`#options-container-${FormIndex}`).html('');
                $(`#written_${FormIndex}`).html('');


            } else {
                $(`.options_type_${FormIndex}`).hide();
                $(`.written_type_${FormIndex}`).hide();
                $(`.image_type_${FormIndex}`).hide();
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
                // console.log(optionFormIndex + ' : opt');


            }
        });
        $(document).on('click', function(event) {
            if (event.target.id.startsWith('add-img-option-')) {
                let formIndex = event.target.id.split('-').pop();
                optionFormIndex++;
                addOptionImg2(formIndex, optionFormIndex);
                // console.log(optionFormIndex + ' : img');

            }
        });

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
            // console.log(SelectQuiz);

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
                    var fileInput = document.getElementById('imageQuestion');
                    var file = fileInput.files[
                        0]; // Get the first file (if multiple, you can loop)

                    // Append the file to FormData

                    // Append question text, type, and other fields to FormData
                    quizData.append('questions[' + index + '][text]', $(element).find(
                        `textarea[name="text${prefix}"]`).val());
                    quizData.append('questions[' + index + '][type]', $(element).find(
                        `select[name="type${prefixType}"]`).val());
                    quizData.append('questions[' + index + '][written]', $(element).find(
                        `input[name="written${prefix}"]`).val());
                    if (file) {
                        quizData.append('questions[' + index + '][img_name]', file);

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
                    }

                    quizData.append('questions[' + index + '][code_snippet]', $(element).find(
                        `textarea[name="code_snippet${prefix}"]`).val());
                    quizData.append('questions[' + index + '][answer_explanation]', $(element)
                        .find(`textarea[name="answer_explanation${prefix}"]`).val());
                    quizData.append('questions[' + index + '][more_info_link]', $(element).find(
                        `input[name="more_info_link${prefix}"]`).val());
                });


                $.ajax({
                    url: "{{ route('questions-quizzes.store') }}",
                    method: 'post',
                    data: quizData,
                    processData: false, // Prevent jQuery from processing the data
                    contentType: false, // Prevent jQuery from setting the content type
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

    });
</script>
