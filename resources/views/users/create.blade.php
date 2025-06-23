<div class="modal fade" id="CreateModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title"><i class="fas fa-user"></i> Add User</h4>
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
                            <strong><i class="fas fa-user"></i> User Name:</strong>
                            {!! Form::text('name', null, ['id' => 'AddName', 'placeholder' => 'Name', 'class' => 'form-control']) !!}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <strong><i class="fas fa-at"></i> Email:</strong>
                            {!! Form::text('email', null, ['id' => 'AddEmail', 'placeholder' => 'Email', 'class' => 'form-control']) !!}
                        </div>
                    </div>
                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <div class="form-group">
                            <strong><i class="fas fa-list-ol"></i> Centre:</strong>
                            <select style="width: 100%;" class="departmentl select2 select2_single form-control"
                                id="AddDepartment" name="department" multiple="multiple">
                                @can('all-centre')
                                    @foreach ($department as $key2)
                                        <option value="{{ $key2->id }}">{{ $key2->name }}
                                        </option>
                                    @endforeach
                                @else
                                    @foreach ($department as $key2)
                                        @if (Auth::user()->department->id == (int) $key2->id)
                                            <option value="{{ $key2->id }}">
                                                {{-- {{ $key2->code }} --}}
                                                {{ $key2->name }}
                                            </option>
                                        @endif
                                    @endforeach
                                @endcan
                            </select>
                        </div>
                    </div>
                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <div class="form-group">
                            <strong><i class="fas fa-list-ol"></i> Department:</strong>
                            <select style="width: 100%;" class="positions select2 select2_single form-control"
                                id="AddPosition" name="position" multiple="multiple">
                                <!-- <option value="" selected>Select Student</option>
                                                                                                                                                                                                                                                                                                                                                                                        <option value="" selected>Select Parent</option>-->
                                {{--   @foreach ($position as $key)
                                        <option value="{{ $key->id }}">{{ $key->name }}
                                        </option>
                                    @endforeach --}}

                            </select>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <strong><i class="fas fa-key"></i> Password:</strong>
                            {!! Form::password('password', ['id' => 'AddPassword', 'placeholder' => 'Password', 'class' => 'form-control']) !!}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <strong><i class="fas fa-key"></i> Confirm Password:</strong>
                            {!! Form::password('confirm-password', [
                                'id' => 'AddPasswordc',
                                'placeholder' => 'Confirm Password',
                                'class' => 'form-control',
                            ]) !!}
                        </div>
                    </div>



                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <strong><i class="fas fa-user-lock"></i> Role:</strong>
                            {!! Form::select('roles', $roles, [], ['id' => 'AddRole', 'class' => 'form-control', 'single']) !!}
                        </div>
                    </div>
                    {{--  <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                      <button type="submit" class="btn btn-primary">Submit</button>
                  </div> --}}
                </div>
                {!! Form::close() !!}
            </div>
            <div class="modal-footer {{-- justify-content-between --}}">
                <button type="button" class="btn btn-success" id="SubmitCreateForm"><i class="fas fa-download"></i>
                    Save</button>
                <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal"><i
                        class="fas fa-door-closed"></i> Close</button>
            </div>
        </div>
    </div>
</div>
