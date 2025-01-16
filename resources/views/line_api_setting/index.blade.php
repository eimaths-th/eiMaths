@extends('layouts.app')

@section('style')
    @include('users.style')
    <style>
        #Listview td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 200px;
            /* Adjust width as needed */
        }

        #Listview td {
            word-wrap: break-word;
            max-width: 200px;
            /* Adjust width as needed */
        }

        /* From Uiverse.io by ErzenXz */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            /* Reduced width */
            height: 25px;
            /* Reduced height */
            cursor: pointer;
        }

        .toggle-switch input[type="checkbox"] {
            display: none;
        }

        .toggle-switch-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #ddd;
            border-radius: 15px;
            /* Adjusted for smaller size */
            transition: background-color 0.3s ease-in-out;
        }

        .toggle-switch-handle {
            position: absolute;
            top: 3px;
            /* Adjusted for smaller size */
            left: 3px;
            /* Adjusted for smaller size */
            width: 19px;
            /* Reduced width */
            height: 19px;
            /* Reduced height */
            background-color: #fff;
            border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease-in-out;
        }

        .toggle-switch input[type="checkbox"]:checked+.toggle-switch-background {
            background-color: #05c46b;
        }

        .toggle-switch input[type="checkbox"]:checked+.toggle-switch-background .toggle-switch-handle {
            transform: translateX(25px);
            /* Adjusted for smaller size */
        }
    </style>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-switch/dist/css/bootstrap3/bootstrap-switch.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-switch"></script>
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
                        @can('user-create')
                            <button type="button" class="btn btn-success" id="CreateButton">
                                <i class="fas fa-user"></i> Add Line API </button>
                        @else
                            <span class="d-inline-block" tabindex="0" data-toggle="tooltip" data-placement="bottom"
                                title="You Not Have Permission">
                                <button type="button" class="btn btn-success disabled">
                                    <i class="fas fa-user"></i> Add Line API </button>
                            </span>
                        @endcan &nbsp;

                        @can('user-delete')
                            <button type="button" class="btn btn-danger delete_all_button"><i class="fa fa-trash"></i> Delete
                                All</button>
                        @else
                            <span class="d-inline-block" tabindex="0" data-toggle="tooltip" data-placement="bottom"
                                title="You Not Have Permission">
                                <button type="button" class="btn btn-danger disabled"><i class="fa fa-trash"></i> Delete
                                    All</button>
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
                            <h3 class="card-title"><i class="fa fa-user"></i> Line-api Management</h3>
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
                            <form method="post" action="#" name="delete_all" id="delete_all">
                                @csrf
                                @method('POST')
                                <table id="Listview" class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>name</th>
                                            <th>created_at</th>
                                            <th>updated_at</th>
                                            <th>line_user_id</th>
                                            {{-- <th>channel_secret</th>
                                            <th>channel_access_token</th> --}}
                                            <th>create_by</th>
                                            <th>update_by</th>
                                            <th>status</th>
                                            <th>actions</th>
                                            {{-- <th width="120px"></th> --}}
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

    @include('line_api_setting.create')

    @include('line_api_setting.edit')

    {{--  {!! $data->render() !!} --}}
@endsection

@section('script')
    @include('line_api_setting.script')
@endsection
