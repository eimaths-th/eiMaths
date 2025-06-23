<div class="modal fade" id="CreateModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title"><i class="fas fa-user"></i> Add Line Api</h4>
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
                            <strong>name :</strong>
                            {!! Form::text('name', null, [
                                'id' => 'name',
                                'placeholder' => 'name',
                                'class' => 'form-control',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <strong>line_user_id:</strong>
                            {!! Form::text('line_user_id', null, [
                                'id' => 'Addline_user_id',
                                'placeholder' => 'line_user_id',
                                'class' => 'form-control',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <strong> channel_secret:</strong>
                            {!! Form::text('channel_secret', null, [
                                'id' => 'Addchannel_secret',
                                'placeholder' => 'channel_secret',
                                'class' => 'form-control',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <strong> channel_access_token:</strong>
                            {!! Form::textarea('channel_access_token', null, [
                                'id' => 'Addchannel_access_token',
                                'placeholder' => 'channel_access_token',
                                'class' => 'form-control',
                                'rows' => 3,
                            ]) !!}
                        </div>
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
</div>
