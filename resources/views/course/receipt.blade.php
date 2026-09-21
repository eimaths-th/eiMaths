<div class="modal fade" id="ReceiptModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title"><i class="fa-solid fa-file-invoice-dollar"></i> {{ __('affiliate-management.course_applications.form.new_receipt') }}</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ __('affiliate-management.course_applications.form.close') }}">
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
                        <ul class="nav nav-tabs" id="custom-tabs-one-tab-receipt" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="custom-tabs-one-home-tab-receipt" data-toggle="pill"
                                    href="#custom-tabs-one-home-receipt" role="tab"
                                    aria-controls="custom-tabs-one-home-receipt" aria-selected="true">{{ __('affiliate-management.course_applications.form.student_detail') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-one-course-tab-receipt" data-toggle="pill"
                                    href="#custom-tabs-one-course-receipt" role="tab"
                                    aria-controls="custom-tabs-one-course-receipt" aria-selected="false">{{ __('affiliate-management.course_applications.form.course_book') }}</a>
                            </li>
                            {{--  <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-one-dis-tab" data-toggle="pill"
                                    href="#custom-tabs-one-dis" role="tab" aria-controls="custom-tabs-one-dis"
                                    aria-selected="false">Discontinued</a>
                            </li> --}}
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="custom-tabs-one-tabContent-receipt">
                            <div class="tab-pane fade show active" id="custom-tabs-one-home-receipt" role="tabpanel"
                                aria-labelledby="custom-tabs-one-home-tab-receipt">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> {{ __('affiliate-management.course_applications.form.referent_code') }}:</strong>
                                                    {{-- <select style="width: 100%;"
                                                        class="productl select2 select2_single form-control"
                                                        id="ReceiptCentre" name="rcentre" multiple="multiple"
                                                        @cannot('all-centre') disabled @endcannot>
                                                        @foreach ($centre as $key2)
                                                            <option value="{{ $key2->id }}"
                                                                @if (Auth::user()->department->id == (int) $key2->id) selected @endif>
                                                                {{ $key2->name }}
                                                            </option>
                                                        @endforeach

                                                    </select> --}}

                                                    <input type="text" name="getRef" id="getRef" class="form-control">
                                                    <input type="hidden" name="courses_pending_id" id="courses_pending_id" class="form-control">
                                                </div>
                                            </div>
                                            <div class="col-xs-3 col-sm-3 col-md-3">

                                            </div>
                                            <div class="col-xs-3 col-sm-3 col-md-3">

                                            </div>
                                            <div class="col-xs-3 col-sm-3 col-md-3">

                                            </div>

                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> {{ __('affiliate-management.course_applications.form.centre_code') }}:</strong>
                                                    <select style="width: 100%;"
                                                        class="productl select2 select2_single form-control"
                                                        id="ReceiptCentre" name="rcentre" multiple="multiple"
                                                        @cannot('all-centre') disabled @endcannot>
                                                        <!-- <option value="" selected>Select Student</option>
                                                                                                                                                                                                                                                                                                                                                                                                    <option value="" selected>Select Parent</option>-->
                                                        {{-- @foreach ($centre as $key2)
                                                            <option value="{{ $key2->id }}"
                                                                @if (Auth::user()->department->id == (int) $key2->id) selected @endif>
                                                                {{ $key2->name }}
                                                            </option>
                                                        @endforeach --}}

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> {{ __('affiliate-management.course_applications.form.student_number') }}</strong>
                                                    <select name="student"
                                                        id="s_student"class="select2 select2_single form-control s_student" multiple="multiple">
                                                        <option value="">{{ __('affiliate-management.course_applications.form.select_student_lower') }}</option>

                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="custom-tabs-one-course-receipt" role="tabpanel"
                                aria-labelledby="custom-tabs-one-course-tab-receipt">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">

                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-layer-group"></i> {{ __('affiliate-management.course_applications.form.start_level') }}:</strong>
                                                    <select style="width: 100%;"
                                                        class="select2 select2_single form-control rlevels"
                                                        id="ReceiptLevel" name="rlevel" multiple="multiple">
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
                                                        class="select2 select2_single form-control rterms"
                                                        id="ReceiptTerm" name="rterm" multiple="multiple" readonly>
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
                                                        class="select2 select2_single form-control rbooks"
                                                        id="ReceiptBook" name="rbook" multiple="multiple">
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
                                                        class="select2 select2_single form-control rlevels2"
                                                        id="ReceiptLevel2" name="rlevel2" multiple="multiple">
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
                                                        class="select2 select2_single form-control rterms2"
                                                        id="ReceiptTerm2" name="rterm2" multiple="multiple"
                                                        readonly>
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
                                                        class="select2 select2_single form-control rbooks2"
                                                        id="ReceiptBook2" name="rbook2" multiple="multiple">
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

                                                    <input type="text" disabled class="form-control rscoin"
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
                <button type="button" class="btn btn-success" id="ReceiptCreateForm"><i
                        class="fas fa-download"></i>
                    {{ __('affiliate-management.course_applications.form.save') }} </button>
                <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal"><i
                        class="fas fa-door-closed"></i> {{ __('affiliate-management.course_applications.form.close') }}</button>
            </div>
        </div>
    </div>
</div>
