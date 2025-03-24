<div class="modal fade" id="EditModal">
    <div class="modal-dialog modal-xxl">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title"><i class="fas fa-graduation-cap"></i>History Student</h4>
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
                {!! Form::open(['method' => 'POST', 'class' => 'form', 'id' => 'myeForm']) !!}

                <div class="card card-success card-tabs">
                    <div class="card-header p-0 pt-1">
                        <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="custom-tabs-one-home-tab" data-toggle="pill"
                                    href="#custom-tabs-one-home" role="tab" aria-controls="custom-tabs-one-home"
                                    aria-selected="true">Student Detail</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="custom-tabs-one-tabContent">
                            <div class="tab-pane fade show active" id="custom-tabs-one-home" role="tabpanel"
                                aria-labelledby="custom-tabs-one-home-tab">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="callout callout-info">
                                                    <h5><i class="fa-solid fa-chalkboard-user"></i> ครูผู้สอน: <select
                                                            name="eteacher" id="eteacher" class="form-control">
                                                            <option value="">
                                                                ไม่มีการบันทึกครูผู้สอน
                                                            </option>
                                                            @foreach ($lteacher as $keyt)
                                                                <option value="{{ $keyt->id }}">
                                                                    {{-- {{ $key2->code }} --}}
                                                                    {{ $keyt->name }}
                                                                </option>
                                                            @endforeach
                                                        </select></h5>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> Centre Code:</strong>
                                                    {{-- <select style="width: 100%;"
                                                        class="productl select2 select2_single form-control"
                                                        id="AddCentre" name="centre" multiple="multiple"
                                                        @cannot('all-centre') disabled @endcannot>
                                                        <!-- <option value="" selected>Select Student</option>
                                                                                                                                                                                                                                                                                                                                                                                                    <option value="" selected>Select Parent</option>-->
                                                        @foreach ($centre as $key2)
                                                            <option value="{{ $key2->id }}"
                                                                @if (Auth::user()->department->id == (int) $key2->id) selected @endif>
                                                                {{ $key2->name }}
                                                            </option>
                                                        @endforeach

                                                    </select> --}}
                                                    <input type="text" name="centre" id="getCentre"
                                                        class="form-control" placeholder="Centre student" disabled>

                                                </div>
                                            </div>
                                            {{-- ต่อหน้า create view history --}}
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> Student No.</strong>
                                                    {{-- {!! Form::text('code', null, [
                                                        'id' => 'AddCode',
                                                        'placeholder' => 'Code',
                                                        'class' => 'form-control',
                                                        'readonly' => true,
                                                    ]) !!} --}}
                                                    <input type="text" name="student" id="getStudent"
                                                        class="form-control" placeholder="name student" disabled>
                                                    {{-- <select name="student" id="s_student"class="select2 select2_single form-control s_student"
                                                        multiple="multiple">
                                                        <option value="">select student</option>

                                                    </select> --}}
                                                    {{-- <input type="text" placeholder="Code" class="form-control" id="AddCode" name="code" readonly="ture"> --}}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> Level</strong>
                                                    {{-- <select style="width: 100%;" class="select2 select2_single form-control" id="AddLevel"  name="level" multiple="multiple"> --}}
                                                    <select name="elevel_id" id="EditLevel"
                                                        class="elevel_id select2 select2_single form-control"
                                                        multiple="multiple">
                                                        <option value="">Select Level</option>

                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                        <option value="3">3</option>

                                                    </select>
                                                </div>
                                            </div>
                                            {{-- ต่อหน้า create view history --}}
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i>Term</strong>
                                                    <select name="eterm"
                                                        id="EditTerm"class="eterms select2 select2_single form-control"
                                                        multiple="multiple">
                                                        <option value="">select Term</option>
                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                        <option value="3">3</option>
                                                        <option value="4">4</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> Bookuse</strong>
                                                    {{-- <select style="width: 100%;"
                                                        class="books select2 select2_single form-control" id="AddBook"
                                                        name="bookuse" multiple="multiple"
                                                        @cannot('all-centre') disabled @endcannot>
                                                        <option value="">Select Book</option>
                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                        <option value="3">3</option>

                                                    </select> --}}
                                                    <input type="text" name="bookuse" id="getBookuse"
                                                        class="form-control" placeholder="getBookuse">

                                                </div>
                                            </div>
                                            {{-- ต่อหน้า create view history --}}
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i>Course Remaining</strong>
                                                    <input type="text" name="course_remaining"
                                                        id="getCourseRemaining" class="form-control" readonly>

                                                    {{-- <select name="course_remaining" id="AddCourseRemaining"class="select2 select2_single form-control"
                                                    multiple="multiple">
                                                        <option value="">select student</option>
                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                        <option value="3">3</option>
                                                    </select> --}}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-calendar"></i>Study Date:</strong>
                                                    <input type="text" name="date" id="getDate"
                                                        class="getDate AddDate form-control" placeholder="getDate">
                                                    {{-- {!! Form::text('date', null, [
                                                        'id' => 'AddDate',
                                                        'placeholder' => '',
                                                        'class' => 'AddDate form-control',
                                                        'data-target' => '#reservationdate',
                                                    ]) !!} --}}
                                                </div>
                                            </div>
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <div class="form-group">
                                                            <strong><i class="fas fa-calendar"></i>Study Start
                                                                Time:</strong>

                                                            <div class="row">
                                                                <input type="text" id="getStartTime"
                                                                    class="form-control col-sm-10 timepicker"
                                                                    placeholder="getStartTime">
                                                                <span class="input-group-text input-group-addon">
                                                                    <i class="fa fa-clock"></i>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">

                                                        <div class="form-group">
                                                            <strong><i class="fas fa-calendar"></i>Study End Time:</strong>
                                                            <div class="row">
                                                                <input type="text" id="getEndTime"
                                                                    class="form-control col-sm-10 timepicker"
                                                                    placeholder="getEndTime">
                                                                <span class="input-group-text input-group-addon">
                                                                    <i class="fa fa-clock"></i>
                                                                </span>
                                                            </div>

                                                            {{-- <input id="" type="text" class="form-control timepicker"/> --}}


                                                        </div>

                                                    </div>
                                                </div>

                                            </div>

                                        </div>

                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i>Course Start Date</strong>
                                                    {{-- <select style="width: 100%;"
                                                        class="books select2 select2_single form-control" id="AddBook"
                                                        name="bookuse" multiple="multiple"
                                                        @cannot('all-centre') disabled @endcannot>
                                                        <option value="">Select Book</option>
                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                        <option value="3">3</option>

                                                    </select> --}}
                                                    <input type="text" name="start_date" id="getStartDate"
                                                        class="form-control AddDate" placeholder="getStartDate" readonly>

                                                </div>
                                            </div>
                                            {{-- ต่อหน้า create view history --}}
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i>Course End Date</strong>
                                                    <input type="text" name="end_date" id="getEndDate"
                                                        class="form-control AddDate" readonly>

                                                    {{-- <select name="course_remaining" id="AddCourseRemaining"class="select2 select2_single form-control"
                                                    multiple="multiple">
                                                        <option value="">select student</option>
                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                        <option value="3">3</option>
                                                    </select> --}}
                                                </div>
                                            </div>
                                        </div>

                                       {{--  <div class="row">
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-calendar"></i> Signature:</strong>

                                                    <br>
                                                    <img src="" alt="Signature" id="getSignature"
                                                        class="img-fluid" style="width: 750px; height: 400px;">

                                                </div>
                                            </div>
                                        </div> --}}

                                        <div class="row">
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-calendar"></i> Comment:</strong>
                                                    <textarea name="comment" id="getComment" rows="5" class="form-control" placeholder="Pls Enter Comment"></textarea>

                                                </div>
                                            </div>
                                        </div>


                                        <div class="form-row form-focus" id="dropzone-att2">
                                            <div class="col-md-12 col-sm-12">
                                                <a id="datt"></a>
                                                <div class="position-relative form-group">
                                                    <label class="form-label form-label-top form-label-auto"
                                                        for="att"><i class="fas fa-image"></i> รูปนักเรียน<span
                                                            class="form-required">*</span>
                                                        <div style="width: 280px"></div>
                                                    </label>
                                                    <input type="text" id="drop" name="drop"
                                                        class="form-control form_none" value="" required>
                                                    <div class="dropzone" id="my-awesome-dropzone"
                                                        style="font-size: 1.5em;">
                                                        <div class="fallback">

                                                        </div>
                                                    </div>
                                                    <label class="form-sub-label" style="min-height:13px"
                                                        aria-hidden="false">Student Photo</label>
                                                    <div id="dropzone-att-error" class="form-error-message"
                                                        role="alert">
                                                        <img src="https://cdn.jotfor.ms/images/exclamation-octagon.png"
                                                            height="10">
                                                        <span class="error-navigation-message">This field is
                                                            required.</span>
                                                        <div class="form-error-arrow">
                                                            <div class="form-error-arrow-inner"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="dropzonex dz-default dz-message" id="dropzone_preview"
                                            style="font-size: 1.5em;">
                                            <h3 class="dropzone-previews ui"></h3>
                                        </div>

                                        <div class="row">
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-signature"></i> ลายเซ็นต์:</strong>
                                                    <div class="esignpadi">
                                                        <canvas id="esignature-pad" width="354"
                                                            height="147"></canvas>
                                                    </div>

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
                <button type="button" class="btn btn-warning" id="eclear-signature"><i class="fas fa-eraser"></i>
                    ล้างลายเซ็นต์</button>
                <button type="button" class="btn btn-success" id="SubmitEditForm"><i class="fas fa-download"></i>
                    Save </button>
                <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal"><i
                        class="fas fa-door-closed"></i> Close</button>
            </div>
        </div>
    </div>
</div>
