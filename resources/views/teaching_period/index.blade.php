@extends('layouts.app')

@section('style')
    @include('users.style')
@endsection

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Teaching Period Management</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        {{-- <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Users Management</li> --}}

                    </ol>

                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-8">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fa fa-user"></i>Teaching Period Management</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                                {{-- <button type="button" class="btn btn-tool" data-card-widget="remove" title="Remove">
                                    <i class="fas fa-times"></i>
                                </button> --}}
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
                            <table class="table table-bordered table-striped" id="teaching_period_table main-table">
                                <thead>
                                    <tr>
                                        <th>Department</th>
                                        <th>Day</th>

                                        <th>Teaching Period</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="teaching_period_table">
                                    @foreach ($teaching_periods as $teaching_period)
                                        <tr>
                                            <td>{{ $teaching_period->department->name }}</td>
                                            <td>{{ $teaching_period->day }}</td>
                                            <td> {{ $teaching_period->period }} </td>
                                            <td>
                                                <form action="{{ route('TeachingPeriod.destroy', $teaching_period->id) }}"
                                                    method="POST">
                                                    {{-- <a class="btn btn-primary" href="{{ route('TeachingPeriod.edit', $teaching_period->id) }}">Edit</a> --}}
                                                    @csrf
                                                    @method('DELETE')
                                                    {{-- <button type="submit" class="btn btn-danger">Delete</button> --}}
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>

                            </table>
                            {{-- <table class="table table-bordered table-striped" id="teaching_period_table">
                                <thead>
                                    <tr>
                                        <th>Department</th>
                                        <th>Day</th>
                                        <th>Teaching Period</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- This will be populated by the AJAX response -->
                                </tbody>
                            </table> --}}

                        </div>

                    </div>

                </div>
                <div class="col-4">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fa fa-user"></i>Teaching Period Find</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                                    <i class="fas fa-minus"></i>
                                </button>

                                {{-- <button type="button" class="btn btn-tool" data-card-widget="remove" title="Remove">
                                    <i class="fas fa-times"></i>
                                </button> --}}
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="find-preiod" id="find-preiod">
                                <select name="department" id="department" class="form-control mb-3">
                                    <option value="">Select department</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}
                                            ({{ $department->code }})
                                        </option>
                                    @endforeach
                                </select>
                                <select name="day" id="day" class="form-control mb-3">
                                    <option value="">Select day</option>
                                    @foreach ($days as $day)
                                        <option value="{{ $day }}">{{ $day }}</option>
                                    @endforeach
                                </select>

                                <button type="button" id="btn-find-preiod" class="btn btn-primary">Find</button>

                                <ul id="period-list"></ul> <!-- This is where the results will be displayed -->
                            </div>
                        </div>
                    </div>


                </div>

            </div>

    </section>
@endsection

@section('script')
    @include('teaching_period.script')
@endsection
