<div class="modal fade" id="CreateModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title"><i class="fas fa-list-ol"></i> {{ __('centre-management.centres.modal.create_title') }}</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ __('centre-management.centres.actions.close') }}">
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
                    <button type="button" class="btn-close" data-dismiss="alert" aria-label="{{ __('centre-management.centres.actions.close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;">

                    <button type="button" class="btn-close" data-dismiss="alert" aria-label="{{ __('centre-management.centres.actions.close') }}">
                        <span aria-hidden="true"></span>
                    </button>
                </div>


                {{-- 'route' => 'users.store', --}}
                {!! Form::open(['method' => 'POST','class' => 'form']) !!}
                <div class="row">
                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <div class="form-group">
                            <strong><i class="fas fa-list-ol"></i> {{ __('centre-management.centres.modal.name') }}:</strong>
                            {!! Form::text('name', null, ['id' => 'AddName', 'placeholder' => __('centre-management.centres.modal.name_placeholder'), 'class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <div class="form-group">
                            <strong><i class="fas fa-list-ol"></i> {{ __('centre-management.centres.modal.code') }}:</strong>
                            {!! Form::text('code', null, ['id' => 'AddCode', 'placeholder' => __('centre-management.centres.modal.code_placeholder'), 'class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <div class="form-group">
                            <strong><i class="fas fa-eye"></i> {{ __('centre-management.centres.modal.status') }}:</strong>
                            <br />
                            <div class="custom-control custom-switch">
                                {{ Form::checkbox('status', '1', true, ['id' => 'customCheckbox1', 'class' => 'custom-control-input name']) }}
                                <label for="customCheckbox1" class="custom-control-label">
                                    {{ __('centre-management.centres.modal.active') }}</label>
                            </div>
                            <small class="form-text text-muted">{{ __('centre-management.centres.modal.status_help') }}</small>
                        </div>
                    </div>
                </div>




                <div class="form-group">
                    <strong>{{ __('centre-management.centres.modal.modules_title') }}</strong>
                    <p class="text-muted">{{ __('centre-management.centres.modal.modules_help') }}</p>
                    <div class="row">
                        @foreach (config('branch.modules') as $moduleKey => $moduleLabel)
                            <div class="col-md-6">
                                <label>
                                    <input type="checkbox" class="branch-module-Add" value="{{ $moduleKey }}"
                                        @if (in_array($moduleKey, config('branch.default_modules'))) checked @endif
                                        @cannot('branch-modules-manage') disabled @endcannot>
                                    {{ __('centre-management.modules.' . $moduleKey) }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

{!! Form::close() !!}
            </div>
            <div class="modal-footer {{-- justify-content-between --}}">
                <button type="button" class="btn btn-success" id="SubmitCreateForm"><i class="fas fa-download"></i> {{ __('centre-management.centres.actions.save') }}</button>
                <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal"><i class="fas fa-door-closed"></i> {{ __('centre-management.centres.actions.close') }}</button>
            </div>
        </div>
    </div>
</div>
