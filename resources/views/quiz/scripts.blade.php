<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Select Questions",
            allowClear: true,
            overflow: 'auto',
        });
        $('.select-std').select2({
            placeholder: "Select Student",
            allowClear: true
        });
        $('.select-quiz').select2({
            placeholder: "Select Quiz",
            allowClear: true
        });

        // Store all quizzes initially
        var allQuizzes = [
            @foreach ($quizzes as $quiz)
                {
                    id: "{{ $quiz->id }}",
                    title: "{{ $quiz->title }}",
                    level: "{{ $quiz->level }}",
                    term: "{{ $quiz->term }}",
                    section: "{{ $quiz->section }}"
                },
            @endforeach
        ];

        function filterQuizzes() {
            var selectedGrade = $('#filter_grade').val();
            var selectedTerm = $('#assign_term').val();       

            var filteredQuizzes = allQuizzes.filter(function(quiz) {
                return (!selectedGrade || quiz.level == selectedGrade) &&
                       (!selectedTerm || quiz.term == selectedTerm) ;
                       
            });

            var $quizSelect = $('#quiz_id');
            $quizSelect.empty();

            $.each(filteredQuizzes, function(index, quiz) {
                $quizSelect.append(new Option(quiz.title, quiz.id));
            });
            
            // Auto-select quizzes if Grade AND Term are selected        
            if(selectedGrade && selectedTerm){
                var allIds = filteredQuizzes.map(function(q){  return q.id;});
                $quizSelect.val(allIds);                
            }

            $quizSelect.trigger('change');
        }

        $('#filter_grade, #assign_term, #filter_level').on('change', function() {
            filterQuizzes();
        });

        // $('#level').select2({

        //     placeholder: "Select Term",
        //     maximumSelectionLength: 1,
        //     allowClear: true
        // });
        // $('#term').select2({
        //     placeholder: "Select Term",
        //     maximumSelectionLength: 1,
        //     allowClear: true
        // });


        var token = "{{ csrf_token() }}";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).on('click', '.delete-button', function() {
            var el = $(this); // The clicked delete button
            var questionId = el.data('id');
            var url = '{{ route('quizzes.destroy', ':id') }}';
            url = url.replace(':id', questionId);

            if (confirm('Are you sure you want to delete this Quiz?')) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(data) {
                        console.log(data);
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
                        console.log(err);
                        alert('Something went wrong. Please try again.');
                    }
                });
            }
        });

        var table = $('#Listview').DataTable({
            ajax: "{{ route('quizzes') }}",
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
                [1, "desc"]
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
                    orderable: false,
                    searchable: false,
                    createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-nowrap').css('width', '1%');
                    }
                },
                {
                    data: 'id',
                    name: 'id',
                    createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-nowrap').css('width', '1%');
                    }
                },
                {
                    data: 'title',
                    name: 'title'
                },
                {
                    data: 'slug',
                    name: 'slug'
                },
                {
                    data: 'description',
                    name: 'description'
                },
                {
                    data: 'questions_count',
                    name: 'questions_count',
                    createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-nowrap').css('width', '1%');
                    }
                },
                // {
                //     data: 'published',
                //     name: 'published',
                //     createdCell: function(td, cellData, rowData, row, col) {
                //         $(td).addClass('text-nowrap').css('width', '1%');
                //     }
                // },
                {
                    data: 'public',
                    name: 'public',
                    createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-nowrap').css('width', '1%');
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

        $('#Listview2').DataTable({
            language: {
                "sLengthMenu": "Display_MENU_ Row",
                "sZeroRecords": "No Data Found",
                "sInfo": "Display _START_ To _END_ From _TOTAL_ Records",
                "sInfoEmpty": "Display 0 To 0 From 0 Records",
                "sInfoFiltered": "(Filters _MAX_ Row)",
                "sSearch": "Search:",
                "oPaginate": {
                    "sFirst": "First",
                    "sPrevious": "Previous",
                    "sNext": "Next",
                    "sLast": "Last"
                }
            },
            aaSorting: [
                [0, "asc"]
            ],
            iDisplayLength: 10,
            lengthMenu: [10, 25, 50, 75, 100],
            stateSave: true,
            autoWidth: false,
            responsive: true,
            sPaginationType: "full_numbers"
        });
$('#btnClear').on('click', function () {

    // reset form ปกติ
    $('#quizForm')[0].reset();

    // reset select2
    $('#student_id').val(null).trigger('change');
    $('#quiz_id').val(null).trigger('change');

    // reset filter
    $('#filter_grade').val('').trigger('change');
    $('#assign_term').val('').trigger('change');
    $('#status').val('').trigger('change');

    // โหลด quiz ทั้งหมดกลับมา
    var $quizSelect = $('#quiz_id');
    $quizSelect.empty();

    $.each(allQuizzes, function(index, quiz) {
        $quizSelect.append(new Option(quiz.title, quiz.id));
    });

    $quizSelect.trigger('change');
});

        $(document).on('click', '#CreateButton', function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();
            $('.form').trigger('reset');
            $('#question-title').text('Create Quiz');

            var btn =
                '<button type="button" class="btn btn-success" id="SubmitCreateForm"> <i class="fas fa-download"></i> Save  </button>';
            $('#btn-submit').html(btn);

            $.ajax({
                url: '{{ route('questions.list') }}', // Adjust the route to match your setup
                method: 'GET',
                success: function(res) {
                    // console.log(res);
                    var options = res.questions;
                    $.each(options, function(id, question) {
                        // console.log(id);

                        var selected = ({{ json_encode(old('questions', [])) }})
                            .includes(question.id) ? 'selected' : '';
                        $('#questions').append('<option value="' + question.id +
                            '" ' + selected + '>' + question.code + ' ' +
                            question.text + '</option>');
                    });

                    // Refresh Select2
                    $('#questions').trigger('change');
                }
            });

            $('#CreateModal').modal('show');
        });
        $(document).on('click', '#AssignButton', function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();
            $('.form').trigger('reset');
            $('#assign-title').text('Assign Quiz');

            var btn =
                '<button type="button" class="btn btn-success" id="SubmitAssignForm"> <i class="fas fa-download"></i> Save  </button>';
            $('#btn-assign-submit').html(btn);
            $('#AssignModal').modal('show');
        });

        function checkSelectionsAndFetchData() {
            const level = $('#level').val();
            const term = $('#term').val();
            const section = $('#section').val();

            if (level && term && section) {
                // All 3 are selected, now fetch data
                $.ajax({
                    url: '{{ route('questions.list') }}', // Adjust the route to match your setup
                    method: 'GET',
                    data: {
                        level: level,
                        term: term,
                        section: section
                    },
                    success: function(res) {
                        console.log(res);
                        var options = res.questions;
                        $('#questions').empty();
                        $.each(options, function(id, question) {
                            // console.log(id);
                            var selected = 'selected';
                            $('#questions').append('<option value="' + question.id +
                                '" ' + selected + '>' + question.code + ' ' +
                                question.text + '</option>');
                        });
                        // Refresh Select2
                        $('#questions').trigger('change');
                    }
                });

            }
        }

        $('#level, #term, #section').on('change', function() {
            checkSelectionsAndFetchData();
        });

        $(document).on('keyup', '#title', function(e) {
            e.preventDefault();
            var val = $('#title').val();
            var slug = val.toLowerCase().replace(/\s+/g, '-');
            $('#slug').val(slug);
        });

        $(document).on('click', '#SubmitCreateForm', function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();
            // console.log('test');
            $.ajax({
                url: "{{ route('quizzes.store') }}",
                method: 'post',
                data: {
                    title: $('#title').val(),
                    slug: $('#slug').val(),
                    description: $('#description').val(),
                    level: $('#level').val(),
                    term: $('#term').val(),
                    section: $('#section').val(),
                    questions: $('#questions').val(),
                    // published: $('#published').is(':checked') ? 1 : 0,
                    public: $('#public').is(':checked') ? 1 : 0,
                    _token: token,
                },
                success: function(result) {
                    if (result.errors) {
                        $('.alert-danger').html('');
                        $.each(result.errors, function(key, value) {
                            $('.alert-danger').show();
                            $('.alert-danger').append('<strong><li>' + value +
                                '</li></strong>');
                        });
                    } else {
                        $('.alert-danger').hide();
                        $('.alert-success').show();
                        $('.alert-success').append('<strong><li>' + result.success +
                            '</li></strong>');
                        toastr.success(result.success, {
                            timeOut: 5000
                        });
                        $('#Listview').DataTable().ajax.reload();
                        $('.form').trigger('reset');
                        $('#CreateModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    $('.alert-danger').html('');
                    $.each(errors, function(key, value) {
                        $('.alert-danger').show();
                        $('.alert-danger').append('<strong><li>' + value +
                            '</li></strong>');
                    });
                }
            });
        });
        $(document).on('click', '#SubmitAssignForm', function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();
            // console.log('test');
            $.ajax({
                url: "{{ route('assignQuiz') }}",
                method: 'post',
                data: {
                    student_id: $('#student_id').val(),
                    quiz_id: $('#quiz_id').val(),
                    term: $('#assign_term').val(),
                    status: $('#status').val(),
                    _token: token,
                },
                success: function(result) {
                    if (result.errors) {
                        $('.alert-danger').html('');
                        $.each(result.errors, function(key, value) {
                            $('.alert-danger').show();
                            $('.alert-danger').append('<strong><li>' + value +
                                '</li></strong>');
                        });
                    } else {
                        // Hide and clear alerts
                        $('.alert-danger').hide().html('');
                        $('.alert-success').show().html('<strong><li>' + result.success +
                            '</li></strong>');

                        // Show toast
                        toastr.success(result.success, {
                            timeOut: 5000
                        });

                        // Reload DataTable
                        $('#Listview').DataTable().ajax.reload();

                        // Reset the form
                        $('#quizForm')[0].reset();

                        // Clear multiple select values
                        $('#student_id').val(null).trigger('change');
                        $('#quiz_id').val(null).trigger('change');
                        $('#status').val('').trigger('change');

                        // Reset filters and reload quiz list
                        $('#filter_grade').val('').trigger('change');
                        $('#assign_term').val('').trigger('change');
                        $('#filter_level').val('').trigger('change');

                        // Hide the modal
                        // $('#AssignModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    $('.alert-danger').html('');
                    $.each(errors, function(key, value) {
                        $('.alert-danger').show();
                        $('.alert-danger').append('<strong><li>' + value +
                            '</li></strong>');
                    });
                }
            });
        });

        let quiz_id;

        $(document).on('click', '#getEditData', function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();
            var btn =
                '<button type="button" class="btn btn-success" id="SubmitEditForm"> <i class="fas fa-download"></i> Save  </button>';
            $('#btn-submit').html(btn);
            $('#question-title').text('Edit Quiz');
            quiz_id = $(this).data('id'); // Use the outer quiz_id variable
            var url = '{{ route('quizzes.edit', ':id') }}';
            url = url.replace(':id', quiz_id);

            $.ajax({
                url: url,
                method: 'GET',
                success: function(res) {
                    console.log(res);
                    $('#title').val(res.quiz.title);
                    $('#slug').val(res.quiz.slug);
                    $('#description').val(res.quiz.description);

                    $('#questions').html(res.html_questions_select);
                    $('#level').val(res.quiz.level);
                    $('#term').val(res.quiz.term);
                    $('#section').val(res.quiz.section);

                    // $('#published').prop('checked', res.quiz.published);
                    $('#public').prop('checked', res.quiz.public);

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

            var url = '{{ route('quizzes.update', ':id') }}';
            url = url.replace(':id', quiz_id); // Use the outer quiz_id variable
            console.log(quiz_id);
            $.ajax({
                url: url,
                method: 'PUT',
                data: {
                    title: $('#title').val(),
                    slug: $('#slug').val(),
                    description: $('#description').val(),
                    level: $('#level').val(),
                    term: $('#term').val(),
                    section: $('#section').val(),
                    questions: $('#questions').val(), // No need to add [] here
                    // published: $('#published').is(':checked') ? 1 : 0,
                    public: $('#public').is(':checked') ? 1 : 0,
                    _token: token,
                },
                success: function(result) {
                    if (result.errors) {
                        $('.alert-danger').html('');
                        $.each(result.errors, function(key, value) {
                            $('.alert-danger').show();
                            $('.alert-danger').append('<strong><li>' + value +
                                '</li></strong>');
                        });
                    } else {
                        $('.alert-danger').hide();
                        $('.alert-success').show();
                        $('.alert-success').append('<strong><li>' + result.success +
                            '</li></strong>');
                        toastr.success(result.success, {
                            timeOut: 5000
                        });
                        $('#Listview').DataTable().ajax.reload();
                        $('.form').trigger('reset');
                        $('#CreateModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    $('.alert-danger').html('');
                    $.each(errors, function(key, value) {
                        $('.alert-danger').show();
                        $('.alert-danger').append('<strong><li>' + value +
                            '</li></strong>');
                    });
                }
            });
        });

        $(document).on('click', '.edit-assign', function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();
            $('.form').trigger('reset'); // This might clear selects, need to re-populate
            $('#assign-title').text('Edit Assign Quiz');

            var studentId = $(this).data('student-id');
            var btn =
                '<button type="button" class="btn btn-success" id="SubmitUpdateAssignForm" data-student-id="' +
                studentId + '"> <i class="fas fa-save"></i> Update </button>';
            $('#btn-assign-submit').html(btn);

            // Fetch existing assignments
            var url = '{{ url('assign-quiz/get-assignments') }}/' + studentId;
            $.ajax({
                url: url,
                method: 'GET',
                success: function(res) {
                    var quizIds = res.quiz_ids;
                    
                    // Set Student (Single)
                    $('#student_id').val([studentId]).trigger('change');
                    
                    // Set Quizzes (Multiple)
                    // Ensure all quizzes are available in the dropdown (might be filtered out)
                    // For now, we assume the initial list contains them or we reload.
                    // If filter is active, some might be hidden. Ideally we should reset filters.
                     $('#filter_grade').val('').trigger('change');
                     $('#assign_term').val('').trigger('change');

                    $('#quiz_id').val(quizIds).trigger('change');
                    
                    $('#AssignModal').modal('show');
                }
            });
        });

        $(document).on('click', '#SubmitUpdateAssignForm', function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();
            
            var studentId = $(this).data('student-id');

            $.ajax({
                url: "{{ url('assign-quiz/update-assignments') }}",
                method: 'post',
                data: {
                    student_id: [studentId], // Send as array to match controller expectation
                    quiz_id: $('#quiz_id').val(),
                    status: $('#status').val(),
                    _token: token,
                },
                success: function(result) {
                    if (result.errors) {
                        $('.alert-danger').html('');
                        $.each(result.errors, function(key, value) {
                            $('.alert-danger').show();
                            $('.alert-danger').append('<strong><li>' + value +
                                '</li></strong>');
                        });
                    } else {
                        $('.alert-danger').hide();
                        $('.alert-success').show();
                        $('.alert-success').append('<strong><li>' + result.success +
                            '</li></strong>');
                        toastr.success(result.success, {
                            timeOut: 5000
                        });
                        
                        setTimeout(function(){
                           window.location.reload(); // Reload to refresh the list since it's not a DataTable ajax
                        }, 1000);
                        
                        $('#AssignModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    $('.alert-danger').html('');
                    $.each(errors, function(key, value) {
                        $('.alert-danger').show();
                        $('.alert-danger').append('<strong><li>' + value +
                            '</li></strong>');
                    });
                }
            });
        });
    });
</script>
