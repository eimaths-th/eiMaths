@extends('layouts.app')
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('feedback-management.list.page_title') }}</h1>

                    {{-- <h4>Users & Roles Management</h4> --}}
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <a class="btn btn-success" href="{{ route('feedback.create') }}">{{ __('feedback-management.list.new') }}</a>

                        {{-- <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Users Management</li> --}}


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
                            <h3 class="card-title"><i class="fa fa-user"></i>{{ __('feedback-management.list.card_title') }}</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="{{ __('feedback-management.list.collapse') }}">
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
                            <form>
                                @csrf
                                @method('POST')
                                <table id="Listview" class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>{{ __('feedback-management.list.table.number') }}</th>
                                            <th>{{ __('feedback-management.list.table.title') }}</th>
                                            <th>{{ __('feedback-management.list.table.category') }}</th>
                                            <th>{{ __('feedback-management.list.table.status') }}</th>
                                            <th>{{ __('feedback-management.list.table.priority') }}</th>
                                            <th width="120px">{{ __('feedback-management.list.table.action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $loop = 1;
                                        @endphp
                                        @foreach ($feedbacks as $feedback)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $feedback->title }}</td>
                                                <td>{{ $feedback->category }}</td>
                                                @php
                                                    $class = '';
                                                    if ($feedback->status == 'New') {
                                                        $class = 'badge badge-primary';
                                                    } elseif ($feedback->status == 'In Progress') {
                                                        $class = 'badge badge-warning';
                                                    } elseif ($feedback->status == 'Resolved') {
                                                        $class = 'badge badge-success';
                                                    }
                                                @endphp
                                                <td>
                                                    <span class="{{ $class }}">
                                                        {{ __('feedback-management.status.' . strtolower(str_replace(' ', '_', $feedback->status))) }}
                                                    </span>
                                                </td>
                                                @php
                                                    $class = '';
                                                    if ($feedback->priority == 'Low') {
                                                        $class = 'badge badge-primary';
                                                    } elseif ($feedback->priority == 'Medium') {
                                                        $class = 'badge badge-warning';
                                                    } elseif ($feedback->priority == 'High') {
                                                        $class = 'badge badge-danger';
                                                    }
                                                @endphp
                                                <td>
                                                    <span class="{{ $class }}">
                                                        {{ __('feedback-management.priorities.' . strtolower($feedback->priority)) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <a class="btn btn-sm btn-primary"
                                                        href="{{ route('feedback.show', $feedback->id) }}">
                                                        <i class="fa fa-eye"></i>
                                                        {{ __('feedback-management.list.view') }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </form>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>
@endsection
