@extends('layouts.app')

@section('style')
    @include('users.style')
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
@endsection

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    {{-- <h4>Users & Roles Management</h4> --}}
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">

                        <button type="button" class="btn btn-success " id="AssignButton">
                            <i class="fas fa-user"></i> Assign Quiz </button>&nbsp;
                        {{-- <button type="button" class="btn btn-success" id="CreateButton">
                            <i class="fas fa-user"></i> Create Quiz </button> --}}

                    </ol>

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
                            <h3 class="card-title"><i class="fa fa-user"></i> Assigned List</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            @if ($message = Session::get('success'))
                                <script>
                                    toastr.success('{{ $message }}', {
                                        timeOut: 5000
                                    });
                                </script>
                            @endif
                            <table id="Listview2" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th> # </th>
                                        <th> Student </th>
                                        <th> Quiz </th>
                                        <th> Status </th>
                                        <th> Assign AT </th>

                                        <th> </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($assignments as $assignment)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $assignment->student->code }} ( {{ $assignment->student->nickname }} ) {{ $assignment->student->name }}</td>
                                            <td>{{ $assignment->quiz->title }}</td>
                                            <td>{{ ucfirst($assignment->status) }}</td>
                                            <td>{{ $assignment->assigned_at }}</td>
                                            <td>
                                                <!-- Add buttons like Edit/Delete here -->
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>

    {{-- @include('question.question-form', ['editing' => false, 'question' => null, 'options' => []]) --}}
    @include('quiz.quiz-form', ['editing' => false, 'quiz' => null, 'options' => []])
    @include('quiz.quiz-assign')

    {{-- @include('question.edit') --}}

    {{--  {!! $data->render() !!} --}}
@endsection

@section('script')
    @include('quiz.scripts')
@endsection
