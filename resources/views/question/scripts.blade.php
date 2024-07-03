<script>
    $(document).ready(function() {
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

        // Hide type-specific fields initially
        $('.written_type').hide();
        $('.options_type').hide();

        var token = '{{ csrf_token() }}';

        var table = $('#Listview').DataTable({
            ajax: '',
            serverSide: true,
            processing: true,
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
                    name: 'text'
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

        $('#add-option').on('click', function() {
            addOption();
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

            $('#CreateModal').modal('show');
        });

        $(document).on('click', '#SubmitCreateForm', function(e) {
            e.preventDefault();
            $('.alert-danger').html('').hide();
            $('.alert-success').html('').hide();

            var formData = {
                text: $('#text').val(),
                code_snippet: $('#code_snippet').val(),
                answer_explanation: $('#answer_explanation').val(),
                more_info_link: $("#more_info_link").val(),
                _token: token
            };

            var written = $('#written').val();
            var selectedType = $('#type').val();

            console.log(written);
            if (selectedType === 'written') {
                formData['type'] = 'written';
                formData['written_answer'] = written;
            } else if (selectedType === 'options') {
                formData['type'] = 'options';
                var options = [];
                $('#options-container .option-item').each(function(index, element) {
                    var optionText = $(element).find('input[type="text"]').val();
                    var isCorrect = $(element).find('input[type="checkbox"]').is(':checked') ?
                        1 : 0;
                    options.push({
                        text: optionText,
                        correct: isCorrect
                    });
                });
                formData['options'] = options;
            }

            $.ajax({
                url: "{{ route('questions.store') }}",
                method: 'post',
                data: formData,
                success: function(result) {
                    if (result.errors) {
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
                    if (xhr.status === 422 || xhr.status === 400) {
                        var errors = xhr.responseJSON.errors || [xhr.responseJSON.errors];
                        var errorList = '';
                        $.each(errors, function(key, value) {
                            errorList += '<li>' + value + '</li>';
                        });
                        $('.alert-danger').show().html('<strong>' + errorList +
                            '</strong>');
                    }
                }
            });
        });

        // $(document).on('click', '#SubmitCreateForm', function(e) {
        //     // $('#SubmitCreateForm').click(function(e) {
        //     // console.log('work');
        //     e.preventDefault();
        //     $('.alert-danger').html('');
        //     $('.alert-danger').hide();
        //     $('.alert-success').html('');
        //     $('.alert-success').hide();
        //     var options = [];
        //     $('#options-container .option-item').each(function(index, element) {
        //         var optionText = $(element).find('input[type="text"]').val();
        //         var isCorrect = $(element).find('input[type="checkbox"]').is(':checked') ? 1 :
        //             0;
        //         options.push({
        //             text: optionText,
        //             correct: isCorrect
        //         });
        //     });
        //     $.ajax({
        //         url: "{{ route('questions.store') }}",
        //         method: 'post',
        //         data: {
        //             text: $('#text').val(),
        //             options: options,
        //             code_snippet: $('#code_snippet').val(),
        //             answer_explanation: $('#answer_explanation').val(),
        //             more_info_link: $("#more_info_link").val(),

        //             _token: token,
        //         },
        //         success: function(result) {
        //             // console.log(result);
        //             if (result.errors) {
        //                 $('.alert-danger').html('');
        //                 $('.alert-danger').show();
        //                 $('.alert-danger').append('<strong><li>' + result.errors +
        //                     '</li></strong>');

        //             } else {
        //                 $('.alert-danger').hide();
        //                 $('.alert-success').show();
        //                 $('.alert-success').append('<strong><li>' + result.success +
        //                     '</li></strong>');
        //                 toastr.success(result.success, {
        //                     timeOut: 5000
        //                 });
        //                 $('#Listview').DataTable().ajax.reload();
        //                 $("#AddDepartment").val(null).trigger("change")
        //                 $("#AddPosition").val(null).trigger("change")
        //                 $('.form').trigger('reset');
        //                 $('#CreateModal').modal('hide');

        //             }
        //         },
        //         error: function(xhr) {
        //             if (xhr.status === 422 || xhr.status === 400) {
        //                 var errors = xhr.responseJSON.errors || [xhr.responseJSON.errors];
        //                 var errorList = '';
        //                 $.each(errors, function(key, value) {
        //                     errorList += '<li>' + value + '</li>';
        //                 });
        //                 $('.alert-danger').show().html('<strong>' + errorList +
        //                     '</strong>');
        //             }
        //         }
        //     });
        // });

        let id
        let questionId
        $(document).on('click', '#getEditData', function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();
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

                    toggleTypeFields(res.question.type, res);

                    $('#options-container').html('');
                    optionIndex = 0;

                    res.options.forEach(function(option) {
                        addOption(option.text, option.correct);
                    });
                    // $('#questionForm').attr('action','{{ route('questions.update', ':id') }}'.replace(':id',questionId));
                    // $('#questionForm').attr('method', 'POST');
                    // $('#questionForm').append('<input type="hidden" name="_method" value="PUT">');
                    $('#CreateModal').modal('show');
                }
            });
        });


        $(document).on('click', '#SubmitEditForm', function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();

            var options = [];
            $('#options-container .option-item').each(function(index, element) {
                var optionText = $(element).find('input[type="text"]').val();
                var isCorrect = $(element).find('input[type="checkbox"]').is(':checked') ? 1 :
                    0;
                options.push({
                    text: optionText,
                    correct: isCorrect
                });
            });

            // var questionId = $(this).data('id');
            var url = '{{ route('questions.update', ':id') }}';
            url = url.replace(':id', questionId);
            console.log(questionId);
            console.log(id);
            $.ajax({
                url: url,
                method: 'PUT',
                data: {
                    text: $('#text').val(),
                    options: options,
                    code_snippet: $('#code_snippet').val(),
                    answer_explanation: $('#answer_explanation').val(),
                    more_info_link: $("#more_info_link").val(),

                    _token: token,
                },
                success: function(result) {
                    // console.log(result);
                    if (result.errors) {
                        $('.alert-danger').html('');
                        $('.alert-danger').show();
                        $('.alert-danger').append('<strong><li>' + result.errors +
                            '</li></strong>');

                    } else {
                        $('.alert-danger').hide();
                        $('.alert-success').show();
                        $('.alert-success').append('<strong><li>' + result.success +
                            '</li></strong>');
                        toastr.success(result.success, {
                            timeOut: 5000
                        });
                        $('#Listview').DataTable().ajax.reload();
                        $("#AddDepartment").val(null).trigger("change")
                        $("#AddPosition").val(null).trigger("change")
                        $('.form').trigger('reset');
                        $('#CreateModal').modal('hide');

                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422 || xhr.status === 400) {
                        var errors = xhr.responseJSON.errors || [xhr.responseJSON.errors];
                        var errorList = '';
                        $.each(errors, function(key, value) {
                            errorList += '<li>' + value + '</li>';
                        });
                        $('.alert-danger').show().html('<strong>' + errorList +
                            '</strong>');
                    }
                }
            });
        });

        // $('#SubmitEditForm').click(function(e) {
        //     if (!confirm("Confirm Save ?")) return;
        //     e.preventDefault();

        //     $('.alert-danger').html('');
        //     $('.alert-danger').hide();
        //     $('.alert-success').html('');
        //     $('.alert-success').hide();

        //     var questionId = $(this).data('id');
        //     var url = '{{ route('questions.update', ':id') }}';
        //     url = url.replace(':id', questionId);

        //     $.ajax({
        //         url: url,
        //         method: 'PUT',
        //         data: {
        //             name: $('#editName').val(),
        //             password: $('#EditPassword').val(),
        //             password_confirmation: $('#EditPasswordC').val(),
        //             email: $('#editEmail').val(),
        //             role: $('#editRole').val(),
        //             department: $("#EditDepartment").val()[0],
        //             position: $("#EditPosition").val()[0],
        //             disable: esstatus

        //         },

        //         success: function(result) {
        //             //console.log(result);
        //             if (result.errors) {
        //                 $('.alert-danger').html('');
        //                 $.each(result.errors, function(key, value) {
        //                     $('.alert-danger').show();
        //                     $('.alert-danger').append('<strong><li>' + value +
        //                         '</li></strong>');
        //                 });
        //             } else {
        //                 $('.alert-danger').hide();
        //                 $('.alert-success').show();
        //                 $('.alert-success').append('<strong><li>' + result.success +
        //                     '</li></strong>');
        //                 $('#EditModal').modal('hide');
        //                 toastr.success(result.success, {
        //                     timeOut: 5000
        //                 });
        //                 $('#Listview').DataTable().ajax.reload();
        //                 //setTimeout(function() {
        //                 //$('.alert-success').hide();

        //                 //}, 10000);

        //             }
        //         }
        //     });
        // });


    });


    // document.addEventListener('DOMContentLoaded', function() {
    //     let optionIndex = {{ count(old('options', $options ?? [])) }};

    //     document.getElementById('add-option').addEventListener('click', function() {
    //         let optionsContainer = document.getElementById('options-container');

    //         let newOption = `
    //             <div class="option-item mt-2 form-row align-items-center">
    //                 <div class="col-xs-10 col-sm-10 col-md-10 text-center">
    //                     <input type="text" class="form-control" name="options[${optionIndex}][text]" id="options_${optionIndex}" autocomplete="off" />
    //                 </div>
    //                 <div class="col-auto text-nowrap">
    //                     <input type="hidden" name="options[${optionIndex}][correct]" value="0">
    //                     <input type="checkbox" class="text-left" name="options[${optionIndex}][correct]" value="1"> Correct&nbsp
    //                     <button type="button" class="btn btn-danger btn-sm  remove-option">&nbsp&nbsp<i class="fa fa-trash"></i>&nbsp&nbsp</button>
    //                 </div>
    //             </div>
    //         `;
    //         optionsContainer.insertAdjacentHTML('beforeend', newOption);
    //         optionIndex++;
    //     });

    //     document.addEventListener('click', function(e) {
    //         if (e.target.classList.contains('remove-option')) {
    //             e.target.closest('.row').remove();
    //         }
    //     });
    // });
</script>
