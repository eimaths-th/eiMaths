<div class="modal fade" id="CreateModalNewStd">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title"><i class="fas fa-graduation-cap"></i> {{ __('affiliate-management.course_applications.form.new_student') }}</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ __('affiliate-management.course_applications.form.close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="ref" id="ref"></div>

                {{-- @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <strong>{{ __('affiliate-management.course_applications.form.whoops') }}</strong>{{ __('affiliate-management.course_applications.messages.error') }}<br><br>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif --}}
                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="display: none;">
                    <button type="button" class="btn-close" data-dismiss="alert" aria-label="{{ __('affiliate-management.course_applications.form.close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;">

                    <button type="button" class="btn-close" data-dismiss="alert" aria-label="{{ __('affiliate-management.course_applications.form.close') }}">
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
                                    aria-selected="true">{{ __('affiliate-management.course_applications.form.student_detail') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-one-address-tab" data-toggle="pill"
                                    href="#custom-tabs-one-address" role="tab"
                                    aria-controls="custom-tabs-one-address" aria-selected="true">{{ __('affiliate-management.course_applications.form.address_detail') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-one-profile-tab" data-toggle="pill"
                                    href="#custom-tabs-one-profile" role="tab"
                                    aria-controls="custom-tabs-one-profile" aria-selected="false">{{ __('affiliate-management.course_applications.form.parent_detail') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-one-course-tab" data-toggle="pill"
                                    href="#custom-tabs-one-course" role="tab" aria-controls="custom-tabs-one-course"
                                    aria-selected="false">{{ __('affiliate-management.course_applications.form.course_book') }}</a>
                            </li>
                            {{--  <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-one-dis-tab" data-toggle="pill"
                                    href="#custom-tabs-one-dis" role="tab" aria-controls="custom-tabs-one-dis"
                                    aria-selected="false">{{ __('affiliate-management.course_applications.form.discontinued') }}</a>
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
                                                    <strong><i class="fas fa-code"></i> {{ __('affiliate-management.course_applications.form.centre_code') }}:</strong>
                                                    <select style="width: 100%;"
                                                        class="productl select2 select2_single form-control"
                                                        id="AddCentre" name="centre" multiple="multiple"
                                                        @cannot('all-centre') disabled @endcannot>
                                                        <!--
                                                            <option value="" selected>Select Student</option>
                                                             <option value="" selected>Select Parent</option>
                                                            -->
                                                        @foreach ($centre as $key2)
                                                            <option value="{{ $key2->id }}"
                                                                @if (Auth::user()->department->id == (int) $key2->id) selected @endif>
                                                                {{ $key2->name }}
                                                            </option>
                                                        @endforeach

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> {{ __('affiliate-management.course_applications.form.student_number') }}</strong>
                                                    {!! Form::text('code', null, [
                                                        'id' => 'AddCode',
                                                        'placeholder' => __('affiliate-management.course_applications.form.code'),
                                                        'class' => 'form-control form',
                                                        'readonly' => true,
                                                    ]) !!}
                                                    {!! Form::hidden('Addref', null, [
                                                        'id' => 'Addref',
                                                        'class' => 'form-control',
                                                        'readonly' => true,
                                                    ]) !!}
                                                    {!! Form::hidden('courses_pending_id', null, [
                                                        'id' => 'courses_pending_id',
                                                        'class' => 'form-control',
                                                        'readonly' => true,
                                                    ]) !!}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-calendar"></i> {{ __('affiliate-management.course_applications.form.start_date') }}:</strong>
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
                                                    <strong><i class="fas fa-calendar"></i> {{ __('affiliate-management.course_applications.form.birth_date') }}:</strong>
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
                                                    <strong><i class="fas fa-book-open"></i> {{ __('affiliate-management.course_applications.form.start_term') }}:</strong>
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
                                                    <strong><i class="fas fa-graduation-cap"></i> {{ __('affiliate-management.course_applications.form.name') }}:</strong>
                                                    {!! Form::text('name', null, ['id' => 'AddName', 'placeholder' => __('affiliate-management.course_applications.form.name'), 'class' => 'form-control']) !!}
                                                </div>
                                            </div>
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-graduation-cap"></i> {{ __('affiliate-management.course_applications.form.nickname') }}:</strong>
                                                    {!! Form::text('nickname', null, [
                                                        'id' => 'AddNickname',
                                                        'placeholder' => __('affiliate-management.course_applications.form.nickname'),
                                                        'class' => 'form-control',
                                                    ]) !!}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-school"></i> {{ __('affiliate-management.course_applications.form.school') }}:</strong>
                                                    {!! Form::text('school', null, ['id' => 'AddSchool', 'placeholder' => __('affiliate-management.course_applications.form.school'), 'class' => 'form-control']) !!}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">

                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fa-solid fa-venus-mars"></i> {{ __('affiliate-management.course_applications.form.gender') }}:</strong>
                                                    <div class="form-group clearfix">
                                                        <div class="icheck-success d-inline">
                                                            <input type="radio" name="gender" value="1"
                                                                checked id="radioSuccess1">
                                                            <label for="radioSuccess1">{{ __('affiliate-management.course_applications.form.male') }}
                                                            </label>
                                                        </div>
                                                        <div class="icheck-success d-inline">
                                                            <input type="radio" name="gender" value="2"
                                                                id="radioSuccess2">
                                                            <label for="radioSuccess2">{{ __('affiliate-management.course_applications.form.female') }}
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
                                                    <strong><i class="fas fa-address-card"></i> {{ __('affiliate-management.course_applications.form.address') }}:</strong>
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
                                                    <strong><i class="fas fa-code"></i> {{ __('affiliate-management.course_applications.form.postcode') }}:</strong>
                                                    {!! Form::text('postcode', null, [
                                                        'id' => 'AddPostcode',
                                                        'placeholder' => __('affiliate-management.course_applications.form.postcode'),
                                                        'class' => 'form-control',
                                                    ]) !!}
                                                </div>
                                            </div>
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-phone"></i> {{ __('affiliate-management.course_applications.form.telephone') }}:</strong>
                                                    {!! Form::text('telephone', null, [
                                                        'id' => 'AddTelephone',
                                                        'placeholder' => __('affiliate-management.course_applications.form.telephone'),
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
                                                    <strong><i class="fa-solid fa-user-tie"></i> {{ __('affiliate-management.course_applications.form.father_name') }}:</strong>
                                                    {!! Form::text('getfName', null, ['id' => 'getfName', 'placeholder' => __('affiliate-management.course_applications.form.father_name'), 'class' => 'form-control']) !!}
                                                </div>
                                            </div>
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-at"></i> {{ __('affiliate-management.course_applications.form.email') }}:</strong>
                                                    {!! Form::email('getfEmail', null, [
                                                        'id' => 'getfEmail',
                                                        'placeholder' => __('affiliate-management.course_applications.form.father_email'),
                                                        'class' => 'form-control',
                                                    ]) !!}
                                                </div>
                                            </div>

                                        </div>
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-phone"></i> {{ __('affiliate-management.course_applications.form.mobile') }}:</strong>
                                                    {!! Form::text('getfTelephone', null, [
                                                        'id' => 'getfTelephone',
                                                        'placeholder' => __('affiliate-management.course_applications.form.father_telephone'),
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
                                                    <strong><i class="fa-solid fa-user-tie"></i> {{ __('affiliate-management.course_applications.form.mother_name') }}:</strong>
                                                    {!! Form::text('getmName', null, ['id' => 'getmName', 'placeholder' => __('affiliate-management.course_applications.form.mother_name'), 'class' => 'form-control']) !!}
                                                </div>
                                            </div>
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-at"></i> {{ __('affiliate-management.course_applications.form.email') }}:</strong>
                                                    {!! Form::email('getmEmail', null, [
                                                        'id' => 'getmEmail',
                                                        'placeholder' => __('affiliate-management.course_applications.form.mother_email'),
                                                        'class' => 'form-control',
                                                    ]) !!}
                                                </div>
                                            </div>

                                        </div>
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-phone"></i> {{ __('affiliate-management.course_applications.form.mobile') }}:</strong>
                                                    {!! Form::text('getmTelephone', null, [
                                                        'id' => 'getmTelephone',
                                                        'placeholder' => __('affiliate-management.course_applications.form.mother_telephone'),
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
                                                        {{ __('affiliate-management.course_applications.form.discontinued') }}:</strong>
                                                    <div class="custom-control custom-switch">
                                                        {{ Form::checkbox('discontinued', '1', false, ['id' => 'customCheckbox1', 'class' => 'custom-control-input name', 'disabled' => true]) }}
                                                        <label for="customCheckbox1" class="custom-control-label">
                                                            {{ __('affiliate-management.course_applications.form.discontinued') }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-4 col-sm-4 col-md-4">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-calendar"></i> {{ __('affiliate-management.course_applications.form.date') }}</strong>
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
                                                    <strong><i class="fas fa-list"></i> {{ __('affiliate-management.course_applications.form.reason') }}</strong>
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
                                                    <strong><i class="fas fa-layer-group"></i> {{ __('affiliate-management.course_applications.form.start_level') }}:</strong>
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
                                                    <strong><i class="fas fa-book-open"></i> {{ __('affiliate-management.course_applications.form.term') }}:</strong>
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
                                                    <strong><i class="fas fa-book-medical"></i> {{ __('affiliate-management.course_applications.form.book_use') }}:</strong>
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
                                                    <strong><i class="fas fa-layer-group"></i> {{ __('affiliate-management.course_applications.form.to_level') }}:</strong>
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
                                                    <strong><i class="fas fa-book-open"></i> {{ __('affiliate-management.course_applications.form.term') }}:</strong>
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
                                                    <strong><i class="fas fa-book-medical"></i> {{ __('affiliate-management.course_applications.form.book_use') }}:</strong>
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

                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                            </div>
                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                            </div>
                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-book-open"></i> {{ __('affiliate-management.course_applications.form.total_class') }}:</strong>

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
                    {{ __('affiliate-management.course_applications.form.save') }} </button>
                <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal"><i
                        class="fas fa-door-closed"></i> {{ __('affiliate-management.course_applications.form.close') }}</button>
            </div>
        </div>
    </div>
</div>
