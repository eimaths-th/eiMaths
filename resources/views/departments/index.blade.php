@extends('layouts.app')

@section('style')
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
                        {{-- <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Users Management</li> --}}

                        @can('centre-create')
                            <button type="button" class="btn btn-success" id="CreateButton">
                                <i class="fas fa-list-ol"></i> {{ __('centre-management.centres.actions.add') }}</button>
                        @endcan &nbsp;

                        @can('centre-delete')
                            <button type="button" class="btn btn-danger delete_all_button"><i class="fa fa-trash"></i> {{ __('centre-management.centres.actions.archive_selected') }}</button>
                        @endcan
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
                            <h3 class="card-title"><i class="fas fa-list-ol"></i> {{ __('centre-management.centres.title') }}</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="{{ __('centre-management.centres.actions.collapse') }}">
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
                            <form method="post" action="{{ route('departments.destroy_all') }}" name="delete_all"
                                id="delete_all">
                                @csrf
                                @method('POST')
                                <table id="Listview" class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>@can('centre-delete')<input type="checkbox" id="check-all" class="flat">@endcan</th>
                                            <th>{{ __('centre-management.centres.table.name') }}</th>
                                            <th>{{ __('centre-management.centres.table.code') }}</th>
                                            <th>{{ __('centre-management.centres.table.status') }}</th>
                                            <th width="120px">{{ __('centre-management.centres.table.action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </form>
                        </div>

                    </div>



                </div>

            </div>

        </div>

    </section>


    @include('departments.create')

    @include('departments.edit')

    {{--  {!! $data->render() !!} --}}
@endsection

@section('script')
    @include('departments.script')
@endsection
