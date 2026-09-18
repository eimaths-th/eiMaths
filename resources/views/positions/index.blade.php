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

                        @can('department-create')
                            <button type="button" class="btn btn-success" id="CreateButton">
                                <i class="fas fa-list-ol"></i> {{ __('centre-management.departments.actions.add') }}</button>
                        @else
                            <span class="d-inline-block" tabindex="0" data-toggle="tooltip" data-placement="bottom"
                                title="{{ __('centre-management.messages.no_permission') }}">
                                <button type="button" class="btn btn-success disabled">
                                    <i class="fas fa-list-ol"></i> {{ __('centre-management.departments.actions.add') }}</button>
                            </span>
                        @endcan &nbsp;

                        @can('department-delete')
                            <button type="button" class="btn btn-danger delete_all_button"><i class="fa fa-trash"></i> {{ __('centre-management.departments.actions.delete_all') }}</button>
                        @else
                            <span class="d-inline-block" tabindex="0" data-toggle="tooltip" data-placement="bottom"
                                title="{{ __('centre-management.messages.no_permission') }}">
                                <button type="button" class="btn btn-danger disabled"><i class="fa fa-trash"></i>
                                    {{ __('centre-management.departments.actions.delete_all') }}</button>
                            </span>
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
                            <h3 class="card-title"><i class="fas fa-list-ol"></i> {{ __('centre-management.departments.title') }}</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="{{ __('centre-management.departments.actions.collapse') }}">
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
                            <form method="post" action="{{ route('positions.destroy_all') }}" name="delete_all"
                                id="delete_all">
                                @csrf
                                @method('POST')
                                <table id="Listview" class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="check-all" class="flat"></th>
                                            <th>{{ __('centre-management.departments.table.department') }}</th>
                                            <th>{{ __('centre-management.departments.table.centre') }}</th>
                                            <th>{{ __('centre-management.departments.table.status') }}</th>
                                            <th width="120px">{{ __('centre-management.departments.table.action') }}</th>
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


    @include('positions.create')

    @include('positions.edit')

    {{--  {!! $data->render() !!} --}}
@endsection

@section('script')
    @include('positions.script')
@endsection
