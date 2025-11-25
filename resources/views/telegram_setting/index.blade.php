@extends('layouts.app')

@section('style')
    @include('users.style')
    <style>
        #Listview td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 200px;
        }

        #Listview td {
            word-wrap: break-word;
            max-width: 200px;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 25px;
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
            transition: background-color 0.3s ease-in-out;
        }

        .toggle-switch-handle {
            position: absolute;
            top: 3px;
            left: 3px;
            width: 19px;
            height: 19px;
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
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        @can('user-create')
                            <button type="button" class="btn btn-success" id="CreateButton">
                                <i class="fab fa-telegram"></i> Add Telegram Setting </button>
                        @else
                            <span class="d-inline-block" tabindex="0" data-toggle="tooltip" data-placement="bottom"
                                title="You Not Have Permission">
                                <button type="button" class="btn btn-success disabled">
                                    <i class="fab fa-telegram"></i> Add Telegram Setting </button>
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
                            <h3 class="card-title"><i class="fab fa-telegram"></i> Telegram Setting Management</h3>
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

                            <div class="alert alert-info">
                                <h5><i class="icon fas fa-info"></i> วิธีการตั้งค่า Telegram Bot</h5>
                                <ol class="mb-0">
                                    <li>สร้าง Bot ใหม่โดยพูดคุยกับ <a href="https://t.me/BotFather" target="_blank">@BotFather</a> บน Telegram</li>
                                    <li>ใช้คำสั่ง <code>/newbot</code> และทำตามขั้นตอน</li>
                                    <li>คัดลอก <strong>Bot Token</strong> ที่ได้รับมา</li>
                                    <li>เพิ่ม Bot เข้า Group หรือ Channel ที่ต้องการรับแจ้งเตือน</li>
                                    <li>หา <strong>Chat ID</strong> โดยใช้ <a href="https://t.me/userinfobot" target="_blank">@userinfobot</a> หรือ <a href="https://t.me/getidsbot" target="_blank">@getidsbot</a></li>
                                </ol>
                            </div>

                            <form method="post" action="#" name="delete_all" id="delete_all">
                                @csrf
                                @method('POST')
                                <table id="Listview" class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Description</th>
                                            <th>Created At</th>
                                            <th>Updated At</th>
                                            <th>Status</th>
                                            <th>Actions</th>
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

    @include('telegram_setting.create')

    @include('telegram_setting.edit')

@endsection

@section('script')
    @include('telegram_setting.script')
@endsection
