<div class="modal fade" id="LogModal">
    <div class="modal-dialog modal-xxl">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title"><i class="fas fa-graduation-cap"></i>History Log</h4>
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

                <div class="card card-success card-tabs">
                    <div class="card-header p-0 pt-1">
                        <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="custom-tabs-one-home-tab" data-toggle="pill"
                                    href="#custom-tabs-one-home" role="tab" aria-controls="custom-tabs-one-home"
                                    aria-selected="true">History Detail</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="custom-tabs-one-tabContent">
                            <div class="tab-pane fade show active" id="custom-tabs-one-home" role="tabpanel"
                                aria-labelledby="custom-tabs-one-home-tab">
                                <div class="card">
                                    <div class="card-body" id="bodyLog">

                                        {{-- <div class="row mb-3">
                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> Centre :</strong>
                                                    <span class="text-info">student Centre</span>

                                                </div>
                                            </div>

                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> Code : </strong>

                                                    <span class="text-info">student code</span>
                                                </div>
                                            </div>
                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> Student : </strong>
                                                    <span class="text-info" for="" >student name</span>

                                                </div>
                                            </div>
                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> app by : </strong>
                                                    <span class="text-info" for="" >app name</span>

                                                </div>
                                            </div>
                                        </div>


                                        <label for="" >Old History</label>
                                        <div class="row mb-3">
                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> Level : </strong>
                                                    <span class="text-danger" for="" >Level name old</span>
                                                </div>
                                            </div>

                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> Term : </strong>
                                                    <span class="text-danger" for="" >Term old</span>
                                                </div>
                                            </div>
                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> Bookuse : </strong>
                                                    <span class="text-danger" for="" >Bookuse old</span>

                                                </div>
                                            </div>
                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group text-nowrap">
                                                    <strong><i class="fas fa-code"></i> Course Remaining : </strong>
                                                    <span class="text-danger" for="" >Course Remaining old</span>


                                                </div>
                                            </div>

                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> Date : </strong>
                                                    <span class="text-danger" for="" >Date old</span>
                                                </div>
                                            </div>

                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> Start Time : </strong>
                                                    <span class="text-danger" for="" >Start Time old</span>
                                                </div>
                                            </div>
                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> End Time : </strong>
                                                    <span class="text-danger" for="" >End Time old</span>

                                                </div>
                                            </div>
                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> Comment : </strong>
                                                    <span class="text-danger" for="" >Comment old </span>


                                                </div>
                                            </div>
                                        </div>

                                        <label for="" >New History</label>
                                        <div class="row mb-3">
                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> Level : </strong>
                                                    <span class="text-success" for="" >Level name New</span>
                                                </div>
                                            </div>

                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> Term : </strong>
                                                    <span class="text-success" for="" >Term New</span>
                                                </div>
                                            </div>
                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> Bookuse : </strong>
                                                    <span class="text-success" for="" >Bookuse New</span>

                                                </div>
                                            </div>
                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group text-nowrap">
                                                    <strong><i class="fas fa-code"></i> Course Remaining : </strong>
                                                    <span class="text-success" for="" >Course Remaining New</span>


                                                </div>
                                            </div>

                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> Date : </strong>
                                                    <span class="text-success" for="" >Date New</span>
                                                </div>
                                            </div>

                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> Start Time : </strong>
                                                    <span class="text-success" for="" >Start Time New</span>
                                                </div>
                                            </div>
                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> End Time : </strong>
                                                    <span class="text-success" for="" >End Time New</span>

                                                </div>
                                            </div>
                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> Comment : </strong>
                                                    <span class="text-success" for="" >Comment New</span>


                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                        </div> --}}

                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {!! Form::close() !!}
            </div>
            <div class="modal-footer {{-- justify-content-between --}}">
                {{-- <button type="button" class="btn btn-success" id="SubmitEditForm"><i class="fas fa-download"></i>
                    Save </button> --}}
                <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal"><i
                        class="fas fa-door-closed"></i> Close</button>
            </div>
        </div>
    </div>
</div>
