<div class="modal fade" id="ViewModal">
    <div class="modal-dialog modal-xxl">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title"><i class="fas fa-graduation-cap"></i>View History Student</h4>
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
                {{-- {!! Form::open(['method' => 'POST', 'class' => 'form', 'id' => 'myForm']) !!} --}}

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
                                                    <h5><i class="fa-solid fa-chalkboard-user"></i> ครูผู้สอน: <span
                                                            id="vteacher_name"></span></h5>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> Centre Code:</strong>
                                                    <input type="text" class="form-control"
                                                        id="viewCentre"placeholder="pls Enter book for student"
                                                        disabled>

                                                </div>
                                            </div>
                                            {{-- ต่อหน้า create view history --}}
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> Student No.</strong>

                                                    <input type="text" class="form-control"
                                                        id="viewStudent"placeholder="pls Enter book for student"
                                                        disabled>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> Level</strong>
                                                    {{-- <select style="width: 100%;" class="select2 select2_single form-control" id="AddLevel"  name="level" multiple="multiple"> --}}
                                                    <input type="text" class="form-control"
                                                        id="viewLV"placeholder="pls Enter book for student" disabled>

                                                </div>
                                            </div>
                                            {{-- ต่อหน้า create view history --}}
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i>Term</strong>
                                                    <input type="text" class="form-control"
                                                        id="viewTerm"placeholder="pls Enter book for student" disabled>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> Bookuse</strong>
                                                    <input type="text" class="form-control"
                                                        id="viewBook"placeholder="pls Enter book for student" disabled>

                                                </div>
                                            </div>
                                            {{-- ต่อหน้า create view history --}}
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i>Course Remaining</strong>
                                                    <input type="text" class="form-control"
                                                        id="viewCourseRemaining"placeholder="pls Enter book for student"
                                                        disabled>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-calendar"></i> Date:</strong>


                                                    <input type="text" class="form-control"
                                                        id="viewDate"placeholder="pls Enter book for student"
                                                        disabled>

                                                </div>
                                            </div>
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <div class="form-group">
                                                            <strong><i class="fas fa-calendar"></i> Start
                                                                Time:</strong>


                                                            <input type="text" class="form-control"
                                                                id="ViewStartTime"placeholder="pls Enter book for student"
                                                                disabled>

                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">

                                                        <div class="form-group">
                                                            <strong><i class="fas fa-calendar"></i> End Time:</strong>
                                                            <input type="text" class="form-control"
                                                                id="ViewEndTime"placeholder="pls Enter book for student"
                                                                disabled>
                                                        </div>

                                                    </div>
                                                </div>

                                            </div>

                                        </div>
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> Start Date</strong>
                                                    <input type="text" class="form-control"
                                                        id="viewStartDate"placeholder="pls Enter book for student"
                                                        disabled>

                                                </div>
                                            </div>
                                            {{-- ต่อหน้า create view history --}}
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i>End Date</strong>
                                                    <input type="text" class="form-control"
                                                        id="viewEndDate"placeholder="pls Enter book for student"
                                                        disabled>


                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-calendar"></i> Signature:</strong>
                                                    {{-- <textarea name="signature" id="AddSignature" rows="5" class="form-control"></textarea> --}}

                                                    <div id="signimg"></div>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div id="carouselExampleIndicators" class="carousel slide"
                                                    data-ride="carousel">
                                                    <ol class="carousel-indicators" id="hsliden">

                                                    </ol>
                                                    <div class="carousel-inner" id="hslidei">

                                                    </div>
                                                    <a class="carousel-control-prev" href="#carouselExampleIndicators"
                                                        role="button" data-slide="prev">
                                                        <span class="carousel-control-custom-icon" aria-hidden="true">
                                                            <i class="fas fa-chevron-left"></i>
                                                        </span>
                                                        <span class="sr-only">Previous</span>
                                                    </a>
                                                    <a class="carousel-control-next" href="#carouselExampleIndicators"
                                                        role="button" data-slide="next">
                                                        <span class="carousel-control-custom-icon" aria-hidden="true">
                                                            <i class="fas fa-chevron-right"></i>
                                                        </span>
                                                        <span class="sr-only">Next</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-calendar"></i> Comment:</strong>
                                                    <textarea name="comment" id="ViewComment" rows="5" class="form-control" disabled></textarea>

                                                </div>
                                            </div>
                                        </div>

                                        {{-- <div class="form-row form-focus" id="dropzone-att">
                                            <div class="col-md-12 col-sm-12">
                                                <a id="datt"></a>
                                                <div class="position-relative form-group">
                                                    <label class="form-label form-label-top form-label-auto"
                                                        for="att">รูปนักเรียน<span class="form-required">*</span>
                                                        <div style="width: 280px"></div>
                                                    </label>
                                                    <img class="d-block w-100" id="ViewImgStd" height="400px"
                                                        src=""
                                                        alt="" />

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
                                        <div class="dropzonex dz-default dz-message" id="dropzone_preview2"
                                            style="font-size: 1.5em;">
                                            <h3 class="dropzone-previews ui"></h3>
                                        </div> --}}

                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- {!! Form::close() !!} --}}
            </div>
            <div class="modal-footer {{-- justify-content-between --}}">
                {{-- <button type="button" class="btn btn-success" id="SubmitCreateForm"><i class="fas fa-download"></i>
                    Save </button> --}}
                <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal"><i
                        class="fas fa-door-closed"></i> Close</button>
            </div>
        </div>
    </div>
</div>
