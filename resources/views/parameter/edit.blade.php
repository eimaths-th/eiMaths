<!-- Edit  Modal -->
<div class="fade modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" id="EditModal">
    <div class="modal-dialog modal-lg" role="document">
        <form id="editdata" class="form" action="" method="POST">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title" id="exampleModalLongTitle"><i class="fas fa-list-ol"></i> Edit Parameter</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="modal-body">
                    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="display: none;">
                        <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;">
                        <strong>Success!</strong> Users was edit successfully.
                        <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true"></span>
                        </button>
                    </div>
                    <div id="EditModalBody">
                        {!! Form::open(['method' => 'POST', 'class' => 'form']) !!}
                        <div class="row">
                            <div class="col-xs-6 col-sm-6 col-md-6">
                                <div class="form-group">
                                    <strong><i class="fas fa-list-ol"></i> Name:</strong>
                                    {!! Form::text('name', null, [
                                        'id' => 'EditName',
                                        'placeholder' => 'Name',
                                        'class' => 'form-control',
                                        'readonly' => true,
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-6 col-sm-6 col-md-6">
                                <div class="form-group">
                                    <strong><i class="fa-solid fa-comment-dollar"></i> Price:</strong>
                                    {!! Form::text('fee', null, ['id' => 'EditFee', 'placeholder' => 'Term Fee', 'class' => 'auto_decimal form-control']) !!}
                                </div>
                            </div>
                        </div>


                        {!! Form::close() !!}
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="SubmitEditForm"><i class="fas fa-download"></i>
                        Save</button>
                    <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal"><i
                            class="fas fa-door-closed"></i> Close</button>
                </div>
            </div>
        </form>
    </div>
</div>
