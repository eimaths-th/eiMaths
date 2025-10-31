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
    $(document).ready(function() {


        function toggleTypeFields(selectedType, res) {
            if (selectedType === 'written') {
                $('.options_type').hide();
                $('.written_type').show();
                $('.image_type').hide();
                $('.fraction_type').hide();
                $('.fraction-button').show();

                if (res) {
                    $('#written').val(res.question.written_answer); // Set written answer if applicable

                }
            } else if (selectedType === 'options') {
                $('.written_type').hide();
                $('.options_type').show();
                $('.image_type').hide();
                $('.fraction_type').hide();
                $('.fraction-button').show();


            } else if (selectedType === 'image') {
                $('.written_type').hide();
                $('.options_type').hide();
                $('.image_type').show();
                $('.fraction_type').hide();
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

        // Hide type-specific fields initially
        $('.written_type').hide();
        $('.options_type').hide();
        $('.image_type').hide();
        $('.fraction_type').hide();


        var token = '{{ csrf_token() }}';


        // Initialize Listview table here only if not already initialized by the page
        if ($('#Listview').length && !$.fn.DataTable.isDataTable('#Listview')) {
            var table = $('#Listview').DataTable({
                ajax: '',
                serverSide: true,
                processing: true,
                drawCallback: function() {
                    if (typeof MathJax !== "undefined") {
                        MathJax.Hub.Queue(["Typeset", MathJax.Hub]);
                    }
                }, // <-- ✅ Added missing comma here
                language: {
                loadingRecords: '&nbsp;',
                processing: `<div class="spinner-border text-primary"></div>`,
                "sProcessing": "Processcing...",
                "sLengthMenu": "Display_MENU_ Row",
                "sZeroRecords": "No Data Fount",
                "sInfo": "Display _START_ To _END_ From _TOTAL_ Records",
                "sInfoEmpty": "Display 0 To 0 From 0 Records",
                "sInfoFiltered": "(Filters _MAX_ Row)",
                "sInfoPostFix": "",
                "sSearch": "Search:",
                "sUrl": "",
                "oPaginate": {
                    "sFirst": "First",
                    "sPrevious": "Previous",
                    "sNext": "Next",
                    "sLast": "Last"
                }
            },
            aaSorting: [
                [0, "desc"]
            ],
            iDisplayLength: 10,
            lengthMenu: [10, 25, 50, 75, 100],
            stateSave: true,
            autoWidth: false,
            responsive: true,
            sPaginationType: "full_numbers",
            dom: 'T<"clear">lfrtip',
            columns: [{
                    data: '#',
                    name: '#',
                    createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-nowrap').css('width', '1%');
                    }
                },
                {
                    data: 'code',
                    name: 'code',
                    createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-nowrap').css('width', '1%');
                    }
                },
                {
                    data: 'text',
                    name: 'text',
                    render: function(data, type, row) {
                        return '<div style="max-width: 750px; word-wrap: break-word; white-space: normal;">' +
                            data + '</div>';
                    }
                },
                {
                    data: 'level',
                    name: 'level'
                },
                {
                    data: 'term',
                    name: 'term'
                },
                {
                    data: 'section',
                    name: 'section'
                },
                {
                    data: 'type',
                    name: 'type'
                },
                {
                    data: 'option',
                    name: 'option'
                },
                // {
                //     data: 'type_option',
                //     name: 'type_option'
                // },
                // {
                //     data: 'term',
                //     name: 'term'
                // },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-nowrap').css('width', '1%');
                    }
                }
            ],
                createdRow: function(row, data, dataIndex) {
                    // Sequential numbering
                    var info = table.page.info();
                    $('td:eq(0)', row).html(info.start + dataIndex + 1);
                }
            });
        }
        if ($.fn.DataTable.isDataTable('#Listview')) {
            $('#Listview').DataTable().clear().draw();
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
                            <button type="button" class="btn btn-danger btn-sm remove-option"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                `);
            optionIndex++;
        }

        function addOption(text = '', correct = 0) {
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

        // imageQuestion on change
        $(document).on('change', '#imageQuestion', function() {
            showQuestionImg(this);
        });
        $(document).on('change', '#answer_explanation_image', function() {
            showAnswerExplanationImgCreate(this);
        });

        function showQuestionImg(input) {

            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-image-question').src = e.target.result;
                    document.getElementById('show-image-question-container').style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function showAnswerExplanationImgCreate(input) {

            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-image-answer-explanation').src = e.target.result;
                    document.getElementById('show-image-answer-explanation-container').style.display =
                        'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        $(document).on('change', 'input[type="file"]', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).siblings('.file-name').text(fileName);
        });

        $(document).on('click', '.remove-option-img', function() {
            $(this).closest('.img-option-item').remove();
        });
        $(document).on('click', '.remove-option-img-show', function() {
            $(this).closest('.show-img-option-item').remove();
        });

        $('#add-option').on('click', function() {
            addOption();
        });

        $('#add-fraction-option').on('click', function() {
            addFractionOption();
        });
        $('#add-fraction-container').on('click', '.remove-option', function() {
            $(this).closest('.fraction-item').remove();
        });

        $(document).on('change', '#answer_type_fraction', function() {
            var selectedFractionType = $(this).val();
            console.log(selectedFractionType);

            toggleTypeFractionFields(selectedFractionType);
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

        $(document).on('click', '.delete-button', function() {
            var el = $(this); // The clicked delete button
            var questionId = el.data('id');
            var url = '{{ route('questions.destroy', ':id') }}';
            url = url.replace(':id', questionId);

            if (confirm('Are you sure you want to delete this question?')) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(data) {
                        if (data.success) {
                            toastr.success(data.message, {
                                timeOut: 5000
                            });

                            table.row(el.parents('tr')).remove().draw();
                        } else {
                            toastr.error(data.message, {
                                timeOut: 5000
                            });
                        }
                    },
                    error: function(err) {
                        alert('Something went wrong. Please try again.');
                    }
                });
            }
        });

        $(document).on('click', '#CreateButton', function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();

            $('.form').trigger('reset');
            $('#options-container').html('');
            optionIndex = 0;
            $('#question-title').text('Create Question');

            var btn =
                '<button type="button" class="btn btn-success" id="SubmitCreateForm"> <i class="fas fa-download"></i> Save  </button>';
            $('#btn-submit').html(btn);
            //how to clear image preview
            $('#show-image-question-container').hide();
            $('#add-image-question-container').html(addImgHtml).show();
            $('#show-image-answer-explanation-container').hide();
            $('#add-image-answer-explanation-container').html(addAnswerExplanationImgHtml).show();


            $('#CreateModal').modal('show');
        });

        //for img

        $(document).on('click', '#SubmitCreateForm', function(e) {
            e.preventDefault();
            $('.alert-danger').html('').hide();
            $('.alert-success').html('').hide();

            // Initialize formData as a FormData object
            var formData = new FormData();
            formData.append('_token', token);
            formData.append('text', $('#text').val());
            formData.append('level', $('#level').val());
            formData.append('term', $('#term').val());
            formData.append('section', $('#section').val());
            formData.append('code_snippet', $('#code_snippet').val());
            formData.append('answer_explanation', $('#answer_explanation').val());
            formData.append('more_info_link', $("#more_info_link").val());

            // Append main question image
            var fileInput = document.getElementById('imageQuestion');
            var file = fileInput.files[0];


            // Append the file to FormData
            if (file) {
                formData.append('img_name', file);
            }

            // Append answer explanation image
            var answerExplanationImageInput = document.getElementById('answer_explanation_image');
            var file = answerExplanationImageInput.files[0];
            if (file) {
                formData.append('answer_explanation_image', file);
            }

            var written = $('#written').val();
            var selectedType = $('#type').val();

            if (selectedType === 'written') {
                formData.append('type', 'written');
                formData.append('written_answer', written);
            } else if (selectedType === 'options') {
                formData.append('type', 'options');
                $('#options-container .option-item').each(function(index, element) {
                    var optionText = $(element).find('input[type="text"]').val();
                    var isCorrect = $(element).find('input[type="checkbox"]').is(':checked') ?
                        1 : 0;
                    formData.append('options[' + index + '][text]', optionText);
                    formData.append('options[' + index + '][correct]', isCorrect);
                });
            } else if (selectedType === 'image') {
                formData.append('type', 'image');
                $('#image-options-container .img-option-item').each(function(index, element) {
                    var imgFileInput = $(element).find('input[type="file"]')[0];
                    var imgOptionFile = imgFileInput.files[0];
                    var isCorrect = $(element).find('input[type="checkbox"]').is(':checked') ?
                        1 : 0;

                    if (imgOptionFile) {
                        formData.append('imgOptions[' + index + '][file]', imgOptionFile);
                    }
                    formData.append('imgOptions[' + index + '][correct]', isCorrect);
                });
            } else if (selectedType === 'fraction') {
                formData.append('type', 'fraction');
                var fractionType = $('#answer_type_fraction').val();
                var answer_type = $('#answer_type').val();
                formData.append('fraction_type', fractionType);
                formData.append('answer_type', answer_type);
                if (fractionType === 'written') {
                    var numerator = $('#answer_numerator').val();
                    var denominator = $('#answer_denominator').val();
                    var answerType = $('#answer_type').val();
                    formData.append('numerator', numerator);
                    formData.append('denominator', denominator);
                    formData.append('answer_type', answerType);
                } else if (fractionType === 'options') {
                    $('#add-fraction-container .fraction-item').each(function(index, element) {
                        var numerator = $(element).find('input[name="answer_numerator[' +
                            index + ']"]').val();
                        var denominator = $(element).find('input[name="answer_denominator[' +
                            index + ']"]').val();
                        var answerType = $(element).find('select[name="answer_type[' + index +
                            ']"]').val();
                        var isCorrect = $(element).find('input[type="checkbox"]').is(
                            ':checked') ? 1 : 0;

                        formData.append('options[' + index + '][numerator]', numerator);
                        formData.append('options[' + index + '][denominator]', denominator);
                        formData.append('options[' + index + '][answer_type]', answerType);
                        formData.append('options[' + index + '][correct]', isCorrect);
                    });

                }
                // $('#add-fraction-container .fraction-item').each(function(index, element) {
                //     var numerator = $(element).find('input[name$="[numerator]"]').val();
                //     var denominator = $(element).find('input[name$="[denominator]"]').val();
                //     var answerType = $(element).find('select[name$="[answer_type]"]').val();
                //     var isCorrect = $(element).find('input[type="checkbox"]').is(':checked') ?
                //         1 : 0;
                //     formData.append('options[' + index + '][numerator]', numerator);
                //     formData.append('options[' + index + '][denominator]', denominator);
                //     formData.append('options[' + index + '][answer_type]', answerType);
                //     formData.append('options[' + index + '][correct]', isCorrect);
                // });

            }
            $('#loadingi').show();

            $.ajax({
                url: "{{ route('questions.store') }}",
                method: 'post',
                data: formData,
                processData: false, // Important for sending FormData
                contentType: false, // Important for sending FormData
                success: function(result) {
                    $('#loadingi').hide();

                    if (result.errors) {
                        console.log(result.errors);
                        $('.alert-danger').html('').show().append('<strong><li>' + result
                            .errors.join('</li><li>') + '</li></strong>');
                    } else {
                        $('.alert-danger').hide();
                        $('.alert-success').show().append('<strong><li>' + result.success +
                            '</li></strong>');
                        toastr.success(result.success, {
                            timeOut: 5000
                        });
                        $('#Listview').DataTable().ajax.reload();
                        $("#AddDepartment").val(null).trigger("change");
                        $("#AddPosition").val(null).trigger("change");
                        $('.form').trigger('reset');
                        $('#CreateModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    $('#loadingi').hide();

                    // if (xhr.status === 422 || xhr.status === 400) {
                    var errors = xhr.responseJSON.errors || [xhr.responseJSON.errors];
                    var errorList = '';
                    $.each(errors, function(key, value) {
                        errorList += '<li>' + value + '</li>';
                    });
                    $('.alert-danger').show().html('<strong>' + errorList +
                        '</strong>');
                    toastr.error(errorList, {
                        timeOut: 5000
                    });
                    // }
                }
            });
            // $('#loadingi').hide();

        });

        let id
        let questionId

        let addImgHtml = `
    <input type="file" id="imageQuestion" name="imageQuestion" class="form-control mb-2">
    <input type="hidden" id="old_img_name" name="old_img_name" value="">
        `;

        let addAnswerExplanationImgHtml = `
    <input type="file" id="answer_explanation_image" name="answer_explanation_image" class="form-control mb-2">
    <input type="hidden" id="old_answer_explanation_img_name" name="old_answer_explanation_img_name" value="">
        `;

        // Load data for editing
        $(document).on('click', '#getEditData', function(e) {
            e.preventDefault();

            // Reset alerts
            $('.alert-danger, .alert-success').html('').hide();

            var btn =
                '<button type="button" class="btn btn-success" id="SubmitEditForm"> <i class="fas fa-download"></i> Save  </button>';
            $('#btn-submit').html(btn);
            $('#question-title').text('Edit Question');
            id = $(this).data('id');
            questionId = $(this).data('id');
            var url = '{{ route('questions.edit', ':id') }}';
            url = url.replace(':id', questionId);
            $.ajax({
                url: url,
                method: 'GET',
                success: function(res) {
                    console.log(res);

                    $('#text').val(res.question.text);
                    $('#code_snippet').val(res.question.code_snippet);
                    $('#answer_explanation').val(res.question.answer_explanation);
                    $('#more_info_link').val(res.question.more_info_link);
                    $('#type').val(res.question.type);
                    $('#level').val(res.question.level).trigger('change');
                    $('#term').val(res.question.term).trigger('change');
                    $('#section').val(res.question.section).trigger('change');

                    toggleTypeFields(res.question.type, res);

                    $('#options-container').html('');
                    optionIndex = 0;
                    res.options.forEach(option => addOption(option.text, option.correct));
                    // Handle image preview
                    if (res.question.img_name === null) {
                        $('#show-image-question-container').hide();
                        $('#add-image-question-container').html(addImgHtml).show();
                    } else {
                        showImg1(res.question.img_name);
                    }

                    // Handle answer explanation image preview
                    if (res.question.answer_explanation_image === null) {
                        $('#show-image-answer-explanation-container').hide();
                        $('#add-image-answer-explanation-container').html(
                            addAnswerExplanationImgHtml).show();
                    } else {
                        showAnswerExplanationImg(res.question.answer_explanation_image);
                    }

                    if (res.question.type === 'fraction') {
                        $('#add-fraction-container').html('');

                        $('#answer_type_fraction').val(res.fractions[0].type).trigger(
                            'change');
                    }
                    if (res.fractionsTypeAnswer === 'written') {
                        $('#answer_numerator').val(res.fractions[0].numerator);
                        $('#answer_denominator').val(res.fractions[0].denominator);

                        // $('#answer_correct').val(res.fractions[0].correct);
                    } else if (res.fractionsTypeAnswer === 'options') {

                        res.fractions.forEach(fraction => addFractionOption(
                            fraction.numerator,
                            fraction.denominator,
                            fraction.answer_type,
                            fraction.correct
                        ));

                    }

                    $('#image-options-container').html('');
                    res.image_options.forEach(imgOption => showImg2(imgOption));

                    $('#CreateModal').modal('show');
                }
            });
        });
        $(document).on('click', '#getEditQuestionData', function(e) {
            e.preventDefault();

            // Reset alerts
            $('.alert-danger, .alert-success').html('').hide();

            var btn =
                '<button type="button" class="btn btn-success" id="SubmitEditQuestionForm"> <i class="fas fa-download"></i> Save  </button>';
            $('#btn-submit').html(btn);
            $('#question-title').text('Edit Question');
            id = $(this).data('id');
            questionId = $(this).data('id');
            var url = '{{ route('questions.edit', ':id') }}';
            url = url.replace(':id', questionId);
            $.ajax({
                url: url,
                method: 'GET',
                success: function(res) {
                    console.log(res);

                    $('#text').val(res.question.text);
                    $('#code_snippet').val(res.question.code_snippet);
                    $('#answer_explanation').val(res.question.answer_explanation);
                    $('#more_info_link').val(res.question.more_info_link);
                    $('#type').val(res.question.type);
                    $('#level').val(res.question.level).trigger('change');
                    $('#term').val(res.question.term).trigger('change');
                    $('#section').val(res.question.section).trigger('change');

                    toggleTypeFields(res.question.type, res);

                    $('#options-container').html('');
                    optionIndex = 0;
                    res.options.forEach(option => addOption(option.text, option.correct));
                    // Handle image preview
                    if (res.question.img_name === null) {
                        $('#show-image-question-container').hide();
                        $('#add-image-question-container').html(addImgHtml).show();
                    } else {
                        showImg1(res.question.img_name);
                    }

                    // Handle answer explanation image preview
                    if (res.question.answer_explanation_image === null) {
                        $('#show-image-answer-explanation-container').hide();
                        $('#add-image-answer-explanation-container').html(
                            addAnswerExplanationImgHtml).show();
                    } else {
                        showAnswerExplanationImg(res.question.answer_explanation_image);
                    }

                    if (res.question.type === 'fraction') {
                        $('#add-fraction-container').html('');

                        $('#answer_type_fraction').val(res.fractions[0].type).trigger(
                            'change');
                    }
                    if (res.fractionsTypeAnswer === 'written') {
                        $('#answer_numerator').val(res.fractions[0].numerator);
                        $('#answer_denominator').val(res.fractions[0].denominator);

                        // $('#answer_correct').val(res.fractions[0].correct);
                    } else if (res.fractionsTypeAnswer === 'options') {

                        res.fractions.forEach(fraction => addFractionOption(
                            fraction.numerator,
                            fraction.denominator,
                            fraction.answer_type,
                            fraction.correct
                        ));

                    }

                    $('#image-options-container').html('');
                    res.image_options.forEach(imgOption => showImg2(imgOption));

                    $('#CreateModal').modal('show');
                }
            });
        });

        // Show image preview and hide file input
        function showImg1(filename) {
            const imageUrl = `/img_questions/${filename}`;
            $('#preview-image-question').attr('src', imageUrl);
            $('#show-image-question-container').show();
            $('#add-image-question-container').html(
                `<input type="hidden" id="old_img_name" name="old_img_name" value="${filename}">`);
        }

        // Handle "Remove Image" button
        $(document).on('click', '#remove-show-image-question-container', function() {
            $('#show-image-question-container').hide();
            $('#add-image-question-container').html(addImgHtml).show();
        });

        // Show answer explanation image preview and hide file input
        function showAnswerExplanationImg(filename) {
            if (!filename) {
                return;
            }
            const imageUrl = `/img_questions/${filename}`;
            $('#preview-image-answer-explanation').attr('src', imageUrl);
            $('#show-image-answer-explanation-container').show();
            $('#add-image-answer-explanation-container').html(
                `<input type="hidden" id="old_answer_explanation_img_name" name="old_answer_explanation_img_name" value="${filename}">`
            );
        }

        // Handle "Remove Answer Explanation Image" button
        $(document).on('click', '#remove-show-image-answer-explanation-container', function() {
            $('#show-image-answer-explanation-container').hide();
            $('#add-image-answer-explanation-container').html(addAnswerExplanationImgHtml).show();
            $('#preview-image-answer-explanation').attr('src', '');

            //clear input file
            $('#answer_explanation_image').val('');
        });

        // function showImg1(imgName) {
        //     var imgUrl = '/img_questions/' + imgName;
        //     var imgElement = `
        //             <div class="col-xs-2 col-sm-2 col-md-2">
        //                 <img src="` + imgUrl + `" alt="Question Image" class="img-thumbnail">
        //             </div>
        //             <div class="col-auto text-nowrap">
        //                 <button type="button" class="btn btn-danger btn-sm" id="remove-show-image-question-container">
        //                     <i class="fa fa-trash"></i>
        //                 </button>
        //             </div>

        //     `;

        //     $('#show-image-question-container').html(imgElement);
        // }


        $('#add-option-img').on('click', function() {
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
        var assetUrl = "{{ asset('images_options/') }}";

        function showImg2(imgOption) {
            var index = $('.show-img-option-item').length; // Generate unique index
            var html = '<div class="show-img-option-item mt-2 form-row align-items-center">';
            html += '<div class="col-xs-2 col-sm-2 col-md-2">';
            html += '<img src="' + imgOption.file_path + '" class="img-thumbnail" alt="Image option" />';
            html += '<input type="hidden" name="image_options[' + index + '][file_id]" value="' + imgOption
                .file_id + '" />'; // Storing file_id
            html += '<input type="hidden" name="image_options[' + index + '][file_path]" value="' + imgOption
                .file_path + '" />';
            html += '</div>';
            html += '<div class="col-auto text-nowrap">';
            html += '<input type="hidden" name="image_options[' + index + '][correct]" value="' + (imgOption
                .correct ? '1' : '0') + '">';
            html += '<input type="checkbox" class="text-left" name="image_options[' + index +
                '][correct]" value="1" ' + (imgOption.correct ? 'checked' : '') + '> Correct&nbsp;';
            html +=
                '<button type="button" class="btn btn-danger btn-sm remove-option-img-show">&nbsp;&nbsp;<i class="fa fa-trash"></i>&nbsp;&nbsp;</button>';
            html += '</div>';
            html += '</div>';

            $('#image-options-container').append(html);
        }

        $(document).on('click', '#SubmitEditForm', function(e) {
            e.preventDefault();

            $('.alert-danger, .alert-success').html('').hide();
            $('#loadingi').show();
            var selectedType = $('#type').val();

            const formData1 = new FormData();
            formData1.append('text', $('#text').val());
            formData1.append('level', $('#level').val());
            formData1.append('term', $('#term').val());
            formData1.append('section', $('#section').val());
            formData1.append('code_snippet', $('#code_snippet').val());
            formData1.append('answer_explanation', $('#answer_explanation').val());

            formData1.append('more_info_link', $('#more_info_link').val());
            // formData1.append('type', selectedType);
            formData1.append('_token', token); // your CSRF token

            // Append either file or old image name
            const fileInput = document.getElementById('imageQuestion');
            if (fileInput && fileInput.files.length > 0) {
                formData1.append('img_name', fileInput.files[0]);
            } else {
                const oldImage = $('#old_img_name').val();
                if (oldImage) {
                    formData1.append('old_img_name', oldImage);
                }
            }

            const answerExplanationImageInput = document.getElementById('answer_explanation_image');
            if (answerExplanationImageInput && answerExplanationImageInput.files.length > 0) {
                formData1.append('answer_explanation_image', answerExplanationImageInput.files[0]);
            } else {
                const oldAnswerExplanationImage = $('#old_answer_explanation_img_name').val();
                if (oldAnswerExplanationImage) {
                    formData1.append('old_answer_explanation_image', oldAnswerExplanationImage);
                }
                formData1.append('answer_explanation_image', '');
            }


            // formData1.append('_method', 'PUT');

            if (selectedType === 'written') {
                formData1.append('type', 'written');
                formData1.append('written_answer', $('#written').val());
            } else if (selectedType === 'options') {
                formData1.append('type', 'options');
                $('#options-container .option-item').each(function(index, element) {
                    var optionText = $(element).find('input[type="text"]').val();
                    var isCorrect = $(element).find('input[type="checkbox"]').is(':checked') ?
                        1 : 0;
                    formData1.append('options[' + index + '][text]', optionText);
                    formData1.append('options[' + index + '][correct]', isCorrect);
                });
            } else if (selectedType === 'image') {
                formData1.append('type', 'image');
                $('#image-options-container .img-option-item').each(function(index, element) {
                    var fileInput = $(element).find('input[type="file"]')[0];
                    var isCorrect = $(element).find('input[type="checkbox"]').is(':checked') ?
                        1 : 0;
                    formData1.append('imgOptions[' + index + '][file]', fileInput.files[0]);
                    formData1.append('imgOptions[' + index + '][correct]', isCorrect);
                });
                $('#image-options-container .show-img-option-item').each(function(index, element) {
                    var fileId = $(element).find('input[name$="[file_id]"]').val();
                    var filePath = $(element).find('input[name$="[file_path]"]').val();
                    var isCorrect = $(element).find('input[type="checkbox"]').is(':checked') ?
                        1 : 0;
                    formData1.append('imgOptionsShow[' + index + '][file_id]', fileId);
                    formData1.append('imgOptionsShow[' + index + '][file_path]', filePath);
                    formData1.append('imgOptionsShow[' + index + '][correct]', isCorrect);
                });
            } else if (selectedType === 'fraction') {
                formData1.append('type', 'fraction');
                var fractionType = $('#answer_type_fraction').val();
                var answer_type = $('#answer_type').val();
                formData1.append('fraction_type', fractionType);
                formData1.append('answer_type', answer_type);
                if (fractionType === 'written') {
                    var numerator = $('#answer_numerator').val();
                    var denominator = $('#answer_denominator').val();
                    var answerType = $('#answer_type').val();
                    formData1.append('numerator', numerator);
                    formData1.append('denominator', denominator);
                    formData1.append('answer_type', answerType);
                } else if (fractionType === 'options') {
                    $('#add-fraction-container .fraction-item').each(function(index, element) {
                        var numerator = $(element).find('input[name="answer_numerator[' +
                            index + ']"]').val();
                        var denominator = $(element).find('input[name="answer_denominator[' +
                            index + ']"]').val();
                        var answerType = $(element).find('select[name="answer_type[' + index +
                            ']"]').val();
                        var isCorrect = $(element).find('input[type="checkbox"]').is(
                            ':checked') ? 1 : 0;

                        formData1.append('options[' + index + '][numerator]', numerator);
                        formData1.append('options[' + index + '][denominator]', denominator);
                        formData1.append('options[' + index + '][answer_type]', answerType);
                        formData1.append('options[' + index + '][correct]', isCorrect);
                    });
                }
            }

            const url = '{{ route('questions.update', ':id') }}'.replace(':id', questionId);
            $('#loadingi').show();
            $.ajax({
                url: url,
                method: 'POST',
                data: formData1,
                processData: false,
                contentType: false,
                success: function(res) {
                    $('#loadingi').hide();
                    if (res.errors) {
                        const errorMessages = Array.isArray(res.errors) ? res.errors.join(
                            '<br>') : res.errors;
                        $('.alert-danger').html(
                            `<strong><li>${errorMessages}</li></strong>`).show();
                        toastr.error(errorMessages);
                    } else {
                        $('.alert-success').html(`<strong><li>${res.success}</li></strong>`)
                            .show();
                        toastr.success(res.success);
                        $('#Listview').DataTable().ajax.reload();
                        $('#CreateModal').modal('hide');
                        $('.form').trigger('reset');
                    }
                },
                error: function(xhr) {
                    $('#loadingi').hide();
                    const errors = xhr.responseJSON.errors || [];
                    let errorMessages = '';
                    $.each(errors, (key, value) => {
                        errorMessages += `<li>${value}</li>`;
                    });
                    $('.alert-danger').html(`<strong>${errorMessages}</strong>`).show();
                    toastr.error(errorMessages);
                }
            });
        });
        $(document).on('click', '#SubmitEditQuestionForm', function(e) {
            e.preventDefault();

            $('.alert-danger, .alert-success').html('').hide();
            $('#loadingi').show();
            var selectedType = $('#type').val();

            const formData1 = new FormData();
            formData1.append('text', $('#text').val());
            formData1.append('level', $('#level').val());
            formData1.append('term', $('#term').val());
            formData1.append('section', $('#section').val());
            formData1.append('code_snippet', $('#code_snippet').val());
            formData1.append('answer_explanation', $('#answer_explanation').val());

            formData1.append('more_info_link', $('#more_info_link').val());
            // formData1.append('type', selectedType);
            formData1.append('_token', token); // your CSRF token

            // Append either file or old image name
            const fileInput = document.getElementById('imageQuestion');
            if (fileInput && fileInput.files.length > 0) {
                formData1.append('img_name', fileInput.files[0]);
            } else {
                const oldImage = $('#old_img_name').val();
                if (oldImage) {
                    formData1.append('old_img_name', oldImage);
                }
            }

            // Append answer explanation image file or old image name
            const answerExplanationImageInput = document.getElementById('answer_explanation_image');
            if (answerExplanationImageInput && answerExplanationImageInput.files.length > 0) {
                formData1.append('answer_explanation_image', answerExplanationImageInput.files[0]);
            } else {
                const oldAnswerExplanationImage = $('#old_answer_explanation_img_name').val();
                if (oldAnswerExplanationImage) {
                    formData1.append('old_answer_explanation_image', oldAnswerExplanationImage);
                }
                formData1.append('answer_explanation_image', '');

            }
            // Append the remove flag for the answer explanation image

            // formData1.append('_method', 'PUT');

            if (selectedType === 'written') {
                formData1.append('type', 'written');
                formData1.append('written_answer', $('#written').val());
            } else if (selectedType === 'options') {
                formData1.append('type', 'options');
                $('#options-container .option-item').each(function(index, element) {
                    var optionText = $(element).find('input[type="text"]').val();
                    var isCorrect = $(element).find('input[type="checkbox"]').is(':checked') ?
                        1 : 0;
                    formData1.append('options[' + index + '][text]', optionText);
                    formData1.append('options[' + index + '][correct]', isCorrect);
                });
            } else if (selectedType === 'image') {
                formData1.append('type', 'image');
                $('#image-options-container .img-option-item').each(function(index, element) {
                    var fileInput = $(element).find('input[type="file"]')[0];
                    var isCorrect = $(element).find('input[type="checkbox"]').is(':checked') ?
                        1 : 0;
                    formData1.append('imgOptions[' + index + '][file]', fileInput.files[0]);
                    formData1.append('imgOptions[' + index + '][correct]', isCorrect);
                });
                $('#image-options-container .show-img-option-item').each(function(index, element) {
                    var fileId = $(element).find('input[name$="[file_id]"]').val();
                    var filePath = $(element).find('input[name$="[file_path]"]').val();
                    var isCorrect = $(element).find('input[type="checkbox"]').is(':checked') ?
                        1 : 0;
                    formData1.append('imgOptionsShow[' + index + '][file_id]', fileId);
                    formData1.append('imgOptionsShow[' + index + '][file_path]', filePath);
                    formData1.append('imgOptionsShow[' + index + '][correct]', isCorrect);
                });
            } else if (selectedType === 'fraction') {
                formData1.append('type', 'fraction');
                var fractionType = $('#answer_type_fraction').val();
                var answer_type = $('#answer_type').val();
                formData1.append('fraction_type', fractionType);
                formData1.append('answer_type', answer_type);
                if (fractionType === 'written') {
                    var numerator = $('#answer_numerator').val();
                    var denominator = $('#answer_denominator').val();
                    var answerType = $('#answer_type').val();
                    formData1.append('numerator', numerator);
                    formData1.append('denominator', denominator);
                    formData1.append('answer_type', answerType);
                } else if (fractionType === 'options') {
                    $('#add-fraction-container .fraction-item').each(function(index, element) {
                        var numerator = $(element).find('input[name="answer_numerator[' +
                            index + ']"]').val();
                        var denominator = $(element).find('input[name="answer_denominator[' +
                            index + ']"]').val();
                        var answerType = $(element).find('select[name="answer_type[' + index +
                            ']"]').val();
                        var isCorrect = $(element).find('input[type="checkbox"]').is(
                            ':checked') ? 1 : 0;

                        formData1.append('options[' + index + '][numerator]', numerator);
                        formData1.append('options[' + index + '][denominator]', denominator);
                        formData1.append('options[' + index + '][answer_type]', answerType);
                        formData1.append('options[' + index + '][correct]', isCorrect);
                    });
                }
            }

            const url = '{{ route('questions.update', ':id') }}'.replace(':id', questionId);
            $('#loadingi').show();
            $.ajax({
                url: url,
                method: 'POST',
                data: formData1,
                processData: false,
                contentType: false,
                success: function(res) {
                    $('#loadingi').hide();
                    if (res.errors) {
                        const errorMessages = Array.isArray(res.errors) ? res.errors.join(
                            '<br>') : res.errors;
                        $('.alert-danger').html(
                            `<strong><li>${errorMessages}</li></strong>`).show();
                        toastr.error(errorMessages);
                    } else {
                        $('.alert-success').html(`<strong><li>${res.success}</li></strong>`)
                            .show();
                        toastr.success(res.success);
                        $('#Listview').DataTable().ajax.reload();
                        $('#CreateModal').modal('hide');
                        $('.form').trigger('reset');
                    }
                },
                error: function(xhr) {
                    $('#loadingi').hide();
                    const errors = xhr.responseJSON.errors || [];
                    let errorMessages = '';
                    $.each(errors, (key, value) => {
                        errorMessages += `<li>${value}</li>`;
                    });
                    $('.alert-danger').html(`<strong>${errorMessages}</strong>`).show();
                    toastr.error(errorMessages);
                }
            });
        });

    });
</script>
