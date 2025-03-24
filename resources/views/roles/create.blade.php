<div class="modal fade" id="CreateModal">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title"><i class="fas fa-user-lock"></i> Add Role</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{-- @if (count($errors) > 0)
                        <div class="alert alert-danger">
                            <strong>Whoops!</strong>Something went wrong.<br><br>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif --}}
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


                    {{-- 'route' => 'users.store', --}}
                    {!! Form::open(['method' => 'POST', 'class' => 'form']) !!}
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong><i class="fas fa-user-lock"></i> Role Name:</strong>
                                {!! Form::text('name', null, ['id' => 'AddName', 'placeholder' => 'Name', 'class' => 'form-control']) !!}
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong><i class="fas fa-user-lock"></i> Permission:</strong>
                                <br />

                                <div class="row">
                                    @foreach ($permission as $value)
                                        <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3"> <!-- Adjust the column classes based on your needs -->
                                            <div class="custom-control custom-switch">
                                                {{ Form::checkbox('permission[]', $value->id, false, ['id' => 'customCheckbox' . $value->id, 'class' => 'custom-control-input name']) }}
                                                <label for="customCheckbox{{ $value->id }}" class="custom-control-label">
                                                    {{ $value->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                            </div>
                        </div>

                    </div>
                    {!! Form::close() !!}

                </div>
                <div class="modal-footer {{-- justify-content-between --}}">
                    <button type="button" class="btn btn-success" id="SubmitCreateForm"><i class="fas fa-download"></i> Save</button>
                    <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal"><i class="fas fa-door-closed"></i> Close</button>
                </div>
            </div>
        </div>
    </div>
