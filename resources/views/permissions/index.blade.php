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

                        @can('add-permission')
                            <button type="button" class="btn btn-success" id="CreateButton">
                                <i class="fas fa-user-lock"></i> {{ __('user-access.permissions.actions.add') }}</button>
                        @else
                            <span class="d-inline-block" tabindex="0" data-toggle="tooltip" data-placement="bottom"
                                title="{{ __('user-access.messages.no_permission') }}">
                                <button type="button" class="btn btn-success disabled">
                                    <i class="fas fa-user-lock"></i> {{ __('user-access.permissions.actions.add') }}</button>
                            </span>
                        @endcan &nbsp;
                            <button type="button" class="btn btn bg-gradient-warning "
                                                    id="resetSearchButton"><i class="fa-solid fa-rotate"></i></button>

                        @can('delete-permission')
                            <button type="button" class="btn btn-danger delete_all_button"><i class="fa fa-trash"></i> {{ __('user-access.permissions.actions.delete_all') }}</button>
                        @else
                            <span class="d-inline-block" tabindex="0" data-toggle="tooltip" data-placement="bottom"
                                title="{{ __('user-access.messages.no_permission') }}">
                                <button type="button" class="btn btn-danger disabled"><i class="fa fa-trash"></i>
                                    {{ __('user-access.permissions.actions.delete_all') }}</button>
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
                            <h3 class="card-title"><i class="fa fa-user-lock"></i> {{ __('user-access.permissions.title') }}</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="{{ __('user-access.permissions.actions.collapse') }}">
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
                            <form method="post" action="#" name="delete_all"
                                id="delete_all">
                                @csrf
                                @method('POST')
                                <table id="Listview" class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th width="5%"><input type="checkbox" id="check-all" class="flat"></th>
                                            <th width="90%">{{ __('user-access.permissions.table.name') }}</th>
                                            <th>{{ __('user-access.permissions.table.action') }}</th>
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

    @include('permissions.create')

    @include('permissions.edit')

    {{--  {!! $data->render() !!} --}}
@endsection

@section('script')
    @include('permissions.script')
@endsection
