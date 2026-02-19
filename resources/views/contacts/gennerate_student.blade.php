  @extends('layouts.app')

@section('style')
    @include('users.style')
    {{-- <style>
        .notification {
            visibility: hidden;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            border-radius: 2px;
            padding: 16px;
            position: fixed;
            z-index: 1;
            left: 50%;
            bottom: 30px;
            font-size: 17px;
            transform: translateX(-50%);
        }

        .notification.show {
            visibility: visible;
            animation: fadein 0.5s, fadeout 0.5s 2.5s;
        }

        @keyframes fadein {
            from { bottom: 0; opacity: 0; }
            to { bottom: 30px; opacity: 1; }
        }

        @keyframes fadeout {
            from { bottom: 30px; opacity: 1; }
            to { bottom: 0; opacity: 0; }
        }
    </style> --}}
@endsection

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <button type="button" class="btn btn-success" data-toggle="modal"
                            data-target="#modal-generate"><i class="fas fa-key"></i>
                            Generate Password</button>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fa fa-user"></i> Generate - Password Student</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <form method="GET" action="{{ route('generate.student') }}">
                                <div class="row mb-3">
                                    <div class="col-md-6 d-flex align-items-center">
                                        Display 
                                        <select name="per_page" id="per_page" class="form-control form-control-sm mx-2" style="width: auto; display: inline-block;" onchange="this.form.submit()">
                                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                            <option value="25" {{ request('per_page') == 25 || !request('per_page') ? 'selected' : '' }}>25</option>
                                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                        </select> 
                                        Row
                                    </div>
                                    <div class="col-md-6 d-flex justify-content-end align-items-center">
                                        Search: 
                                        <input type="text" name="search" class="form-control form-control-sm ml-2" style="width: 200px; display: inline-block;" value="{{ request('search') }}" placeholder="">
                                        <button type="submit" class="btn btn-sm btn-default ml-1"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                            </form>
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Created Password At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($gennerateStudent as $student)
                                        <tr>
                                            <td>{{ $student->code }} - {{ $student->name }}</td>
                                            <td>{{ $student->password_generated_at ? \Carbon\Carbon::parse($student->password_generated_at)->format('d/m/Y H:i') : '-' }}
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button"
                                                        data-toggle="dropdown">
                                                        Select
                                                    </button>

                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item btn-reset-password" href="#" data-student-id="{{ $student->id }}"><i class="fas fa-edit"></i> Reset
                                                            Password</a>
                                                        <a class="dropdown-item text-danger btn-delete-password" href="#" data-student-id="{{ $student->id }}"><i class="fas fa-trash"></i>
                                                            Delete</a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                             <br>
                            {!! $gennerateStudent->appends(Request::all())->render() !!}
                        </div>

                    </div>
                </div>
            </div>

        </div>

    </section>

    <!-- Modal -->
    <div class="modal fade" id="modal-generate" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Generate Password</h5>
                    <button type="button" class="close" data-dismiss="modal"  data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @if ($message = Session::get('success'))
                        {{--  <div class="alert alert-success">
                                    <p>{{ $message }}</p>
                                </div> --}}
                        <script>
                            toastr.success('{{ $message }}', {
                                timeOut: 5000
                            });
                        </script>
                    @endif
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
                    {!! Form::open(['method' => 'POST', 'class' => 'form']) !!}
                    <div class="row">
                        <input type="hidden" name="action" id="action" value="generate">
                        <div class="col-xs-8 col-sm-8 col-md-8">
                            <div class="form-group">
                                <strong><i class="fas fa-user"></i> Students:</strong>
                                <select name="student_id" id="selectStudent" class="form-control" style="width: 100%">
                                    <option value="">Select Student</option>
                                    @foreach ($students as $student)
                                        <option value="{{ $student->id }}">{{ $student->code }} -
                                            {{ $student->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-8 col-sm-8 col-md-8">
                            <div class="form-group">
                                <strong><i class="fas fa-key"></i> Password:</strong>
                                {!! Form::text('name', null, ['id' => 'AddPassword', 'placeholder' => 'Password', 'class' => 'form-control']) !!}
                            </div>
                        </div>
                        <div class="col-xs-4 col-sm-4 col-md-4 mt-4">
                            <button type="button" class="btn btn-warning" id="ranCreateForm1"><i class="fas fa-dice"></i>
                                Random</button>
                        </div>

                    </div>
                    {!! Form::close() !!}
                    <div class="row mt-2">
                        <div class="col-xs-8 col-sm-8 col-md-8">
                            <div class="form-group">
                                <strong><i class="fas fa-user"></i> Generated:</strong>
                                <textarea name="generate" id="generate" cols="30" rows="2" class="form-control" placeholder="generate"></textarea>
                                <p id="copyMessage" style="color: green; display: none;">Password copied to
                                    clipboard!</p>
                            </div>

                        </div>
                        <div class="col-xs-4 col-sm-4 col-md-4 mt-4">
                            <div class="form-group">

                            </div>

                            <button onclick="copyToClipboard()" id="copyButton" class="btn btn-info"><i
                                    class="fas fa-clipboard"></i> Copy</button>

                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="SubmitCreateForm1"><i class="fas fa-download"></i>
                        Save</button>
                </div>
            </div>
        </div>
    </div>
            </div>

        </div>

        </div>

    </section>

     <!-- Modal Delete -->
     <div class="modal fade" id="modal-delete" tabindex="-1" role="dialog" aria-labelledby="modelTitleId" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete Password</h5>
                    <button type="button" class="close" data-dismiss="modal"  data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this student's password?
                    <input type="hidden" id="delete_student_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" id="btn-confirm-delete">Delete</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        var token = ''
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        }); 
            $('#selectStudent').select2({
                placeholder: "Select Student",
                allowClear: true,
                dropdownParent: $('#modal-generate')
            });
        $('#ranCreateForm1').click(function(e) {
            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();
            $.ajax({
                url: "{{ route('generate.student.save') }}",
                method: 'post',
                data: {
                    action: 'random',
                    student_id: $('#selectStudent').val(),
                    password: $('#AddPassword').val(),
                    _token: token,
                },
                success: function(result) {
                    // console.log(result);
                    if (result.errors) {
                        $('.alert-danger').html('');
                        // $('.alert-danger').append('<strong><li>' + result.error + '</li></strong>');
                        var errors = Array.isArray(result.errors) ? result.errors : [result.errors];
                        $.each(result.errors, function(key, value) {
                            $('.alert-danger').show();
                            $('.alert-danger').append('<strong><li>' + value +
                                '</li></strong>');
                        });

                        toastr.error(result.errors, {
                            timeOut: 5000
                        });
                    } else {
                        $('.alert-danger').hide();
                        /* $('.alert-success').show();
                        $('.alert-success').append('<strong><li>' + result.success + '</li></strong>');
                        toastr.success(result.success, {
                            timeOut: 5000
                        }); */
                        $("#AddPassword").val(result.password);
                        $("#generate").val(result.password);
                        // copyToClipboard()
                        var student = result.student;

                       var row = '<tr>' + 
                            '<td>' + student.code + ' - ' + (student.name ? student.name : '') + '</td>' + 
                            '<td>' + result.created_at + '</td>' +  
                            '</tr>';
                        $('table tbody').append(row);
                }
            }});
        });
        $('#SubmitCreateForm1').click(function(e) {
            if (!confirm("Confirm save password for student?")) return;

            e.preventDefault();
            $('.alert-danger').html('');
            $('.alert-danger').hide();
            $('.alert-success').html('');
            $('.alert-success').hide();
            $.ajax({
                url: "{{ route('generate.student.save') }}",
                method: 'post',
                data: {
                    action: $('#action').val(),
                    student_id: $('#selectStudent').val(),
                    password: $('#AddPassword').val(),
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
                        toastr.error(result.errors, {
                            timeOut: 5000
                        });
                    } else {
                        $('.alert-danger').hide();
                        $('.alert-success').show();
                        $('.alert-success').append('<strong><li>' + result.success +
                            '</li></strong>');
                        toastr.success(result.success, {
                            timeOut: 5000
                        });
                        $("#AddPassword").val(result.password);
                        $("#generate").val(result.password);
                        // copyToClipboard()
                        var student = result.student;
                        
                        var row = '<tr>' + 
                            '<td>' + student.code + ' - ' + (student.name ? student.name : '') + '</td>' + 
                            '<td>' + result.created_at + '</td>' +   
                            '<td>' + result.created_at + '</td>' +   
                            '</tr>';
                        $('table tbody').append(row);

                        setTimeout(function() {
                             location.reload();
                        }, 1000);  

                    }
                }
            });
        });

        // Handle Reset Password click
        $(document).on('click', '.btn-reset-password', function(e) {
            e.preventDefault();
            var studentId = $(this).data('student-id');
            
            // Set the value in Select2 and trigger change event
            $('#selectStudent').val(studentId).trigger('change');
            
            // Show the modal
            $('#modal-generate').modal('show');
        });

        // Clear form when modal is closed
        $('#modal-generate').on('hidden.bs.modal', function () {
            // Optional: clear selection if not manually triggered
             $('#selectStudent').val('').trigger('change');
             $('#AddPassword').val('');
             $('#generate').val('');
             $('.alert').hide();
        });

         // Handle Delete Password click
         $(document).on('click', '.btn-delete-password', function(e) {
            e.preventDefault();
            var studentId = $(this).data('student-id');
            $('#delete_student_id').val(studentId);
            $('#modal-delete').modal('show');
        });

        // Handle Confirm Delete click
        $('#btn-confirm-delete').click(function() {
            var studentId = $('#delete_student_id').val();
            
            $.ajax({
                url: "{{ route('generate.student.remove') }}",
                method: 'post',
                data: {
                    student_id: studentId,
                    _token: token,
                },
                success: function(result) {
                    if (result.errors) {
                         var errors = Array.isArray(result.errors) ? result.errors : [result.errors];
                         toastr.error(errors.join('<br>'), { timeOut: 5000 });
                    } else {
                        toastr.success(result.success, { timeOut: 5000 });
                         $('#modal-delete').modal('hide');
                        setTimeout(function() {
                             location.reload();
                        }, 1000);
                    }
                },
                error: function(xhr) {
                    toastr.error('Something went wrong', { timeOut: 5000 });
                    $('#modal-delete').modal('hide');
                }
            });
        });

        function copyToClipboard() {

            var copyText = document.getElementById("generate");

            copyText.select();
            copyText.setSelectionRange(0, 9999999);
            document.execCommand("copy");

            // alert("Copied the text: " + copyText.value);
            // var notification = document.getElementById("notification");
            // notification.classList.add("show");
            // setTimeout(function() {
            //     notification.classList.remove("show");
            // }, 5000);

            var copyButton = document.getElementById("copyButton");
            // copyButton.innerText = "Copied!";
            copyButton.innerHTML = '<i class="fas fa-clipboard-check"></i> Copied!';
            setTimeout(function() {
                copyButton.innerHTML = '<i class="fas fa-clipboard"></i> Copy';
            }, 5000);

            var copyMessage = document.getElementById("copyMessage");
            copyMessage.style.display = "block";
            setTimeout(function() {
                copyMessage.style.display = "none";
            }, 5000);
        }
    </script>
@endsection
