<div class="modal fade" id="EditRModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title"><i class="fa-solid fa-edit"></i> Edit</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {!! Form::open(['method' => 'POST', 'class' => 'form', 'id' => 'editdata']) !!}
                <div id="edit_data">

                </div>

                <div class="row imgs">

                </div>
                <div class="form-row form-focus justify-content-center" id="dropzone-att">
                    <div class="col-md-10 col-sm-10">
                        <a id="datt"></a>
                        <div class="position-relative form-group">
                            <label class="form-label form-label-top form-label-auto" for="att"><i
                                    class="fas fa-file-image"></i>
                                Upload proof of payment<span class="form-required">*</span>
                                <div style="width: 280px"></div>
                            </label>
                            <input type="text" id="drop2" name="drop2" class="form-control form_none"
                                value="" required>
                            <div class="dropzone" id="my-awesome-dropzone2" style="font-size: 1.5em;">
                                <div class="fallback">

                                </div>
                            </div>
                            <label class="form-sub-label" style="min-height:13px" aria-hidden="false">Slip
                                image</label>
                            <div id="dropzone-att-error" class="form-error-message" role="alert">
                                <img src="https://cdn.jotfor.ms/images/exclamation-octagon.png" height="10">
                                <span class="error-navigation-message">This field is
                                    required.</span>
                                <div class="form-error-arrow">
                                    <div class="form-error-arrow-inner"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-10 col-sm-10 justify-content-center">
                    <div class="dropzonex dz-default dz-message " id="dropzone_preview2" style="font-size: 1.5em;">
                        <h3 class="dropzone-previews ui"></h3>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>

            <div class="modal-footer {{-- justify-content-between --}}">
                <button type="button" class="btn btn-success" id="SaveReceipt"><i class="fas fa-edit"></i>
                    Save</button>
                <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal"><i
                        class="fas fa-door-closed"></i> Close</button>
            </div>
        </div>
    </div>

</div>
