<div class="modal fade" id="CreateModal">
    <div class="modal-dialog modal-xxl">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title"><i class="fas fa-graduation-cap"></i> {{ __('students.actions.new_student') }}</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ __('common.actions.close') }}">
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

                <div class="card card-success card-tabs">
                    <div class="card-header p-0 pt-1">
                        <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="custom-tabs-one-home-tab" data-toggle="pill"
                                    href="#custom-tabs-one-home" role="tab" aria-controls="custom-tabs-one-home"
                                    aria-selected="true">{{ __('students.form.student_detail') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-one-address-tab" data-toggle="pill"
                                    href="#custom-tabs-one-address" role="tab"
                                    aria-controls="custom-tabs-one-address" aria-selected="true">{{ __('students.form.address_detail') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-one-profile-tab" data-toggle="pill"
                                    href="#custom-tabs-one-profile" role="tab"
                                    aria-controls="custom-tabs-one-profile" aria-selected="false">{{ __('students.form.parent_detail') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-one-course-tab" data-toggle="pill"
                                    href="#custom-tabs-one-course" role="tab" aria-controls="custom-tabs-one-course"
                                    aria-selected="false">{{ __('students.form.course_book') }}</a>
                            </li>
                            {{--  <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-one-dis-tab" data-toggle="pill"
                                    href="#custom-tabs-one-dis" role="tab" aria-controls="custom-tabs-one-dis"
                                    aria-selected="false">Discontinued</a>
                            </li> --}}
                        </ul>
                    </div>
                    <div class="card-body">
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
                                                        id="AddCentre" name="centre" multiple="multiple"
                                                        @unless(Auth::user()->isHq()) disabled @endunless>
                                                        <!-- <option value="" selected>Select Student</option>
                                                                                                                                                                                                                                                                                                                                                                                                    <option value="" selected>Select Parent</option>-->
                                                        @foreach ($centre as $key2)
                                                            @if (Auth::user()->isHq() || Auth::user()->department_id === (int) $key2->id)
                                                                <option value="{{ $key2->id }}"
                                                                    @if (Auth::user()->department_id === (int) $key2->id) selected @endif>
                                                                    {{-- {{ $key2->code }} --}}
                                                                    {{ $key2->name }}
                                                                </option>
                                                            @endif
                                                        @endforeach

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> {{ __('students.form.student_no') }}</strong>
                                                    {!! Form::text('code', null, [
                                                        'id' => 'AddCode',
                                                        'placeholder' => __('students.form.code'),
                                                        'class' => 'form-control',
                                                        'readonly' => true,
                                                    ]) !!}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-calendar"></i> {{ __('students.form.start_date') }}:</strong>
                                                    {!! Form::text('start_date', null, [
                                                        'id' => 'AddDate',
                                                        'placeholder' => '',
                                                        'class' => 'AddDate form-control',
                                                        'data-target' => '#reservationdate',
                                                    ]) !!}
                                                </div>
                                            </div>
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-calendar"></i> {{ __('students.form.birth_date') }}:</strong>
                                                    {!! Form::text('birth_date', null, [
                                                        'id' => 'AddBirthDate',
                                                        'placeholder' => '',
                                                        'class' => 'AddDate form-control',
                                                        'data-target' => '#reservationdate',
                                                    ]) !!}
                                                </div>
                                            </div>
                                            {{-- <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-book-open"></i> Start Term:</strong>
                                                    <select style="width: 100%;"
                                                        class="select2 select2_single form-control" id="AddSTerm"
                                                        name="sterm" multiple="multiple">
                                                        @foreach ($sterm as $skeyt)
                                                            <option value="{{ $skeyt->id }}">
                                                                {{ $skeyt->name }}
                                                            </option>
                                                        @endforeach

                                                    </select>
                                                </div>
                                            </div> --}}
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-graduation-cap"></i> {{ __('students.form.name') }}:</strong>
                                                    {!! Form::text('name', null, ['id' => 'AddName', 'placeholder' => __('students.form.name'), 'class' => 'form-control']) !!}
                                                </div>
                                            </div>
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-graduation-cap"></i> {{ __('students.form.nickname') }}:</strong>
                                                    {!! Form::text('nickname', null, [
                                                        'id' => 'AddNickname',
                                                        'placeholder' => __('students.form.nickname'),
                                                        'class' => 'form-control',
                                                    ]) !!}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-school"></i> {{ __('students.form.school') }}:</strong>
                                                    {!! Form::text('school', null, ['id' => 'AddSchool', 'placeholder' => __('students.form.school'), 'class' => 'form-control']) !!}
                                                </div>
                                            </div>
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-school"></i> {{ __('students.form.referral') }}:</strong>
                                                    {!! Form::text('referral', null, [
                                                        'id' => 'AddReferral',
                                                        'placeholder' => __('common.fields.referral'),
                                                        'class' => 'form-control',
                                                    ]) !!}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">

                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fa-solid fa-venus-mars"></i> {{ __('students.form.gender') }}:</strong>
                                                    <div class="form-group clearfix">
                                                        <div class="icheck-success d-inline">
                                                            <input type="radio" name="gender" value="1"
                                                                checked id="radioSuccess1">
                                                            <label for="radioSuccess1">{{ __('students.form.male') }}
                                                            </label>
                                                        </div>
                                                        <div class="icheck-success d-inline">
                                                            <input type="radio" name="gender" value="2"
                                                                id="radioSuccess2">
                                                            <label for="radioSuccess2">{{ __('students.form.female') }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="custom-tabs-one-address" role="tabpanel"
                                aria-labelledby="custom-tabs-one-address-tab">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">

                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-address-card"></i> {{ __('students.form.address') }}:</strong>
                                                    {!! Form::textarea('address', null, [
                                                        'rows' => 4,
                                                        'id' => 'AddAddress',
                                                        'class' => 'form-control',
                                                    ]) !!}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> {{ __('students.form.postcode') }}:</strong>
                                                    {!! Form::text('postcode', null, [
                                                        'id' => 'AddPostcode',
                                                        'placeholder' => __('students.form.postcode'),
                                                        'class' => 'form-control',
                                                    ]) !!}
                                                </div>
                                            </div>
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-phone"></i> {{ __('students.form.telephone') }}:</strong>
                                                    {!! Form::text('telephone', null, [
                                                        'id' => 'AddTelephone',
                                                        'placeholder' => __('students.form.telephone'),
                                                        'class' => 'form-control',
                                                    ]) !!}
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="custom-tabs-one-profile" role="tabpanel"
                                aria-labelledby="custom-tabs-one-profile-tab">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fa-solid fa-user-tie"></i> {{ __('students.form.father_name') }}:</strong>
                                                    {!! Form::text('fname', null, ['id' => 'AddfName', 'placeholder' => __('students.form.father_name'), 'class' => 'form-control']) !!}
                                                </div>
                                            </div>
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-at"></i> {{ __('common.fields.email') }}:</strong>
                                                    {!! Form::email('femail', null, [
                                                        'id' => 'AddfEmail',
                                                        'placeholder' => __('students.form.father_email'),
                                                        'class' => 'form-control',
                                                    ]) !!}
                                                </div>
                                            </div>

                                        </div>
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-phone"></i> {{ __('students.form.father_mobile') }}:</strong>
                                                    {!! Form::text('ftelephone', null, [
                                                        'id' => 'AddfTelephone',
                                                        'placeholder' => __('students.form.father_mobile'),
                                                        'class' => 'form-control',
                                                    ]) !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fa-solid fa-user-tie"></i> {{ __('students.form.mother_name') }}:</strong>
                                                    {!! Form::text('mname', null, ['id' => 'AddmName', 'placeholder' => __('students.form.mother_name'), 'class' => 'form-control']) !!}
                                                </div>
                                            </div>
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-at"></i> {{ __('common.fields.email') }}:</strong>
                                                    {!! Form::email('memail', null, [
                                                        'id' => 'AddmEmail',
                                                        'placeholder' => __('students.form.mother_email'),
                                                        'class' => 'form-control',
                                                    ]) !!}
                                                </div>
                                            </div>

                                        </div>
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-phone"></i> {{ __('students.form.mother_mobile') }}:</strong>
                                                    {!! Form::text('mtelephone', null, [
                                                        'id' => 'AddmTelephone',
                                                        'placeholder' => __('students.form.mother_mobile'),
                                                        'class' => 'form-control',
                                                    ]) !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- <div class="tab-pane fade" id="custom-tabs-one-dis" role="tabpanel"
                                aria-labelledby="custom-tabs-one-dis-tab">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-xs-2 col-sm-2 col-md-2">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-handshake-slash"></i>
                                                        Discontinued:</strong>
                                                    <div class="custom-control custom-switch">
                                                        {{ Form::checkbox('discontinued', '1', false, ['id' => 'customCheckbox1', 'class' => 'custom-control-input name', 'disabled' => true]) }}
                                                        <label for="customCheckbox1" class="custom-control-label">
                                                            Discontinued</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-4 col-sm-4 col-md-4">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-calendar"></i> Date</strong>
                                                    {!! Form::text('ddate', null, [
                                                        'id' => 'AddDDate',
                                                        'placeholder' => '',
                                                        'class' => 'form-control',
                                                        'disabled' => true,
                                                    ]) !!}
                                                </div>
                                            </div>
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-list"></i> Reason</strong>
                                                    {!! Form::text('reason', null, [
                                                        'id' => 'AddReason',
                                                        'placeholder' => '',
                                                        'class' => 'form-control',
                                                        'disabled' => true,
                                                    ]) !!}
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div> --}}
                            <div class="tab-pane fade" id="custom-tabs-one-course" role="tabpanel"
                                aria-labelledby="custom-tabs-one-course-tab">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">

                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-layer-group"></i> {{ __('students.form.start_level') }}:</strong>
                                                    <select style="width: 100%;"
                                                        class="select2 select2_single form-control levels"
                                                        id="AddLevel" name="level" multiple="multiple">
                                                        @foreach ($level as $keyl)
                                                            <option value="{{ $keyl->id }}">
                                                                {{ $keyl->name }}
                                                            </option>
                                                        @endforeach

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-book-open"></i> {{ __('common.fields.term') }}:</strong>
                                                    <select style="width: 100%;"
                                                        class="select2 select2_single form-control terms"
                                                        id="AddTerm" name="term" multiple="multiple" readonly>
                                                        @foreach ($term as $keyt)
                                                            <option value="{{ $keyt->id }}">
                                                                {{ $keyt->name }}
                                                            </option>
                                                        @endforeach

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-book-medical"></i> {{ __('students.form.book_use') }}:</strong>
                                                    <select style="width: 100%;"
                                                        class="select2 select2_single form-control books"
                                                        id="AddBook" name="book" multiple="multiple">
                                                        @foreach ($bookuse as $keyb)
                                                            <option value="{{ $keyb->id }}">
                                                                {{ $keyb->name }}
                                                            </option>
                                                        @endforeach

                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">

                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-layer-group"></i> {{ __('students.form.to_level') }}:</strong>
                                                    <select style="width: 100%;"
                                                        class="select2 select2_single form-control levels2"
                                                        id="AddLevel2" name="level2" multiple="multiple">
                                                        @foreach ($level as $keyl)
                                                            <option value="{{ $keyl->id }}">
                                                                {{ $keyl->name }}
                                                            </option>
                                                        @endforeach

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-book-open"></i> {{ __('common.fields.term') }}:</strong>
                                                    <select style="width: 100%;"
                                                        class="select2 select2_single form-control terms2"
                                                        id="AddTerm2" name="term2" multiple="multiple" readonly>
                                                        @foreach ($term as $keyt)
                                                            <option value="{{ $keyt->id }}">
                                                                {{ $keyt->name }}
                                                            </option>
                                                        @endforeach

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-book-medical"></i> {{ __('students.form.book_use') }}:</strong>
                                                    <select style="width: 100%;"
                                                        class="select2 select2_single form-control books2"
                                                        id="AddBook2" name="book2" multiple="multiple">
                                                        @foreach ($bookuse as $keyb)
                                                            <option value="{{ $keyb->id }}">
                                                                {{ $keyb->name }}
                                                            </option>
                                                        @endforeach

                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                
                                            </div>
                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <strong><i class="fas fa-book-open"></i>{{ __('students.form.free_class') }}:</strong>
                                                <input type="number" min="0" class="form-control free_course"
                                                    id="AddFreeCourse" name="free_course" value="0">
                                            </div>
                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <strong><i class="fas fa-book-open"></i>{{ __('students.form.reason_free_class') }}:</strong>
                                                <input type="text" class="form-control free_course_reason"
                                                    id="AddFreeCourseReason" name="free_course_reason"
                                                    value="">
                                            </div>

                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-book-open"></i> {{ __('students.form.total_class') }}:</strong>

                                                    <input type="text" disabled class="form-control scoin"
                                                        value="0">

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {!! Form::close() !!}
            </div>
            <div class="modal-footer {{-- justify-content-between --}}">
                <button type="button" class="btn btn-success" id="SubmitCreateForm"><i class="fas fa-download"></i>
                    {{ __('common.actions.save') }} </button>
                <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal"><i
                        class="fas fa-door-closed"></i> {{ __('common.actions.close') }}</button>
            </div>
        </div>
    </div>
</div>
