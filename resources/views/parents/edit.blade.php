<div class="modal fade" id="EditModal">
    <div class="modal-dialog modal-xxl">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title"><i class="fas fa-graduation-cap"></i>{{ __('common.actions.edit') }} {{ __('students.parents.title') }}</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">

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

                <form action="#" method="POST">
                    @csrf
                    <div class="card card-success card-tabs">
                        <div class="card-header p-0 pt-1">
                            <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="custom-tabs-one-home-tab" data-toggle="pill"
                                        href="#custom-tabs-one-home" role="tab" aria-controls="custom-tabs-one-home"
                                        aria-selected="true">{{ __('students.form.parent_detail') }}</a>
                                </li>
                            </ul>
                        </div>


                        <div class="card-body">

                            <div class="alert alert-danger " id="edit_validate" role="alert" style="display: none;">

                            </div>

                            <div class="tab-content" id="custom-tabs-one-tabContent">
                                <div class="tab-pane fade show active" id="custom-tabs-one-home" role="tabpanel"
                                    aria-labelledby="custom-tabs-one-home-tab">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-xs-6 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <strong><i class="fas fa-code"></i> {{ __('students.form.centre_code') }}:</strong>
                                                        <select style="width: 100%;"
                                                            class="productl select2 select2_single form-control"
                                                            id="EditCentre" name="centre_id" multiple="multiple">

                                                            @foreach ($centre as $key2)
                                                                <option value="{{ $key2->id }}"
                                                                    @if (Auth::user()->department->id == (int) $key2->id) selected @endif>
                                                                    {{-- {{ $key2->code }} --}}
                                                                    {{ $key2->name }}
                                                                </option>
                                                            @endforeach

                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-xs-6 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <strong><i class="fas fa-code"></i> {{ __('students.form.student_no') }}</strong>

                                                        <select name="student"
                                                            id="Edits_student"class="select2 select2_multiple form-control s_student"
                                                            multiple="multiple">
                                                            <option value="">{{ __('students.form.select_student') }}</option>

                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-xs-6 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <strong><i class="fas fa-calendar"></i> {{ __('students.form.first_name') }}:</strong>
                                                        <input type="text" name="fname" id="EditFname"
                                                            class="form-control" autocomplete="off"
                                                             placeholder="{{ __('students.parents.parent_name') }}">
                                                    </div>
                                                </div>
                                                <div class="col-xs-6 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <strong><i class="fas fa-calendar"></i>{{ __('students.form.last_name') }}:</strong>
                                                        <input type="text" name="lname" id="EditLname"
                                                            class="form-control" autocomplete="off"
                                                             placeholder="{{ __('students.form.last_name') }}">

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-xs-6 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <strong><i class="fas fa-calendar"></i> {{ __('common.fields.phone') }}:</strong>
                                                        <input type="text" name="telp" id="EditTelp"
                                                            class="form-control" autocomplete="off"
                                                             placeholder="{{ __('common.fields.phone') }}">
                                                    </div>
                                                </div>
                                                <div class="col-xs-6 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <strong><i class="fas fa-calendar"></i>{{ __('common.fields.email') }}:</strong>
                                                        <input type="text" name="email" id="EditEmail"
                                                            class="form-control" autocomplete="off"
                                                             placeholder="{{ __('common.fields.email') }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-xs-6 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <strong><i class="fas fa-calendar"></i> {{ __('students.form.emergency_contact') }}</strong>
                                                        <input type="text" name="emergency_contact"
                                                            id="EditEmergency" class="form-control"
                                                            autocomplete="off" placeholder="{{ __('students.form.emergency_contact') }}">
                                                    </div>
                                                </div>
                                                <div class="col-xs-6 col-sm-6 col-md-6">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <strong><i class="fas fa-calendar"></i>{{ __('students.form.relationship') }}:</strong>
                                                            <select name="relationship" id="EditRelationship"
                                                                class="form-control">
                                                                 <option value="">{{ __('students.parents.relation_select') }}</option>
                                                                 <option value="father">{{ __('students.parents.father') }}</option>
                                                                 <option value="mother">{{ __('students.parents.mother') }}</option>
                                                                 <option value="relative">{{ __('students.parents.relative') }}</option>
                                                                 <option value="other">{{ __('students.parents.other') }}</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <strong class="mb-5"><i
                                                                     class="fas fa-calendar"></i>{{ __('students.form.gender') }}:
                                                            </strong>
                                                            <div class="row">
                                                                <div class="col-lg-6">
                                                                    <label>
                                                                        <input type="radio" name="gender"
                                                                             id="male" value="male"> {{ __('students.form.male') }}
                                                                    </label>
                                                                </div>

                                                                <div class="col-lg-6">
                                                                    <label>
                                                                        <input type="radio" name="gender"
                                                                             id="female" value="female"> {{ __('students.form.female') }}
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                         <strong><i class="fas fa-calendar"></i> {{ __('common.fields.address') }}:</strong>
                                                         <textarea name="address" id="EditAddress" rows="5" class="form-control" placeholder="{{ __('common.fields.address') }}"></textarea>

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                         <strong><i class="fas fa-calendar"></i>{{ __('students.form.details') }}:</strong>
                                                         <textarea name="notes" id="EditNotes" rows="5" class="form-control" placeholder="{{ __('students.form.details') }}"></textarea>

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                @error('password')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                                <div class="col-xs-6 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <strong><i class="fas fa-calendar"></i>{{ __('students.form.password') }}:</strong>
                                                        <input type="password" name="password" id="EditPassword"
                                                            class="form-control" autocomplete="off"
                                                             placeholder="{{ __('students.form.password') }}">

                                                    </div>
                                                </div>
                                                <div class="col-xs-6 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <strong><i class="fas fa-calendar"></i>{{ __('students.form.confirm_password') }}:</strong>
                                                        <input type="password" name="password_confirmation"
                                                            id="password_confirmation1" class="form-control"
                                                             autocomplete="off" placeholder="{{ __('students.form.confirm_password') }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </form>

            </div>

            <div class="modal-footer {{-- justify-content-between --}}">
                <button type="button" class="btn btn-success" id="SubmitEditForm"><i class="fas fa-download"></i>
                    {{ __('common.actions.save') }}</button>
                <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal"><i
                        class="fas fa-door-closed"></i> {{ __('common.actions.close') }}</button>
            </div>
        </div>
    </div>
</div>
