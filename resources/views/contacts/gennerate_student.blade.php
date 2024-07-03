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

            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-6">
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
                            <div class="alert alert-danger alert-dismissible fade show" role="alert"
                                style="display: none;">
                                <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="alert alert-success alert-dismissible fade show" role="alert"
                                style="display: none;">

                                <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true"></span>
                                </button>
                            </div>
                            {!! Form::open(['method' => 'POST', 'class' => 'form']) !!}
                            <div class="row">
                                <input type="hidden" name="action" id="action" value="generate">
                                <div class="col-xs-6 col-sm-6 col-md-6">
                                    <div class="form-group">
                                        <strong><i class="fas fa-user"></i> Stuents:</strong>
                                        <select name="student_id" id="selectStudent" class="form-control">
                                            <option value="">Select Student</option>
                                            @foreach ($students as $student)
                                                <option value="{{ $student->id }}">{{ $student->code }} -
                                                    {{ $student->nickname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-xs-6 col-sm-6 col-md-6">
                                    <div class="form-group">
                                        <strong><i class="fas fa-user"></i> Password:</strong>
                                        {!! Form::text('name', null, ['id' => 'AddPassword', 'placeholder' => 'Password', 'class' => 'form-control']) !!}
                                    </div>
                                </div>
                                <div class="col-xs-6 col-sm-6 col-md-6 mt-4">
                                    <button type="button" class="btn btn-warning" id="ranCreateForm1"><i
                                            class="fas fa-dice"></i> Random</button>
                                </div>

                            </div>
                            {!! Form::close() !!}
                            <div class="row">
                                <div class="col-xs-6 col-sm-6 col-md-6">
                                    <div class="form-group">
                                        <strong><i class="fas fa-user"></i> Generated:</strong>
                                        <textarea name="generate" id="generate" cols="30" rows="2" class="form-control" placeholder="generate"></textarea>
                                        <p id="copyMessage" style="color: green; display: none;">Password copied to
                                            clipboard!</p>
                                    </div>

                                </div>
                                <div class="col-xs-6 col-sm-6 col-md-6 mt-3">
                                    <div class="form-group">

                                    </div>

                                    <button onclick="copyToClipboard()" id="copyButton" class="btn btn-info"><i
                                            class="fas fa-clipboard"></i> Copy</button>

                                </div>


                            </div>
                        </div>

                        <div class="modal-footer {{-- justify-content-between --}}">
                            <button type="button" class="btn btn-success" id="SubmitCreateForm1"><i
                                    class="fas fa-download"></i>
                                Save</button>
                        </div>

                    </div>

                </div>
            </div>

        </div>

        </div>

    </section>
@endsection

@section('script')
    <script>
        var token = ''
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
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
                        $('.alert-success').show();
                        $('.alert-success').append('<strong><li>' + result.success + '</li></strong>');
                        toastr.success(result.success, {
                            timeOut: 5000
                        });
                        $("#AddPassword").val(result.password);
                        $("#generate").val(result.password);
                        // copyToClipboard()

                    }
                }
            });
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

                    }
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
