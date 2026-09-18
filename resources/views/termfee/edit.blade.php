<!-- Edit  Modal -->
<div class="fade modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" id="EditModal">
    <div class="modal-dialog modal-lg" role="document">
        <form id="editdata" class="form" action="" method="POST">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title" id="exampleModalLongTitle"><i class="fas fa-list-ol"></i> {{ __('term-fee.modal.edit_title') }}</h4>
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
                        <strong>{{ __('common.feedback.saved') }}</strong> {{ __('term-fee.messages.updated') }}
                        <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true"></span>
                        </button>
                    </div>
                    <div id="EditModalBody">
                        {!! Form::open(['method' => 'POST', 'class' => 'form']) !!}
                        <div class="row">
                            <div class="col-xs-6 col-sm-6 col-md-6">
                                <div class="form-group">
                                    <strong><i class="fas fa-list-ol"></i> {{ __('term-fee.modal.level_name') }}:</strong>
                                    {!! Form::text('name', null, [
                                        'id' => 'EditName',
                                        'placeholder' => __('term-fee.modal.level_name'),
                                        'class' => 'form-control',
                                        'readonly' => true,
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-6 col-sm-6 col-md-6">
                                <div class="form-group">
                                    <strong><i class="fa-solid fa-comment-dollar"></i> {{ __('term-fee.modal.term_fee') }}:</strong>
                                    {!! Form::text('fee', null, ['id' => 'EditFee', 'placeholder' => __('term-fee.modal.term_fee'), 'class' => 'auto_decimal form-control']) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-6 col-sm-6 col-md-6">
                                <div class="form-group">
                                    <strong><i class="fa-solid fa-comment-dollar"></i> {{ __('term-fee.modal.term_fee_half') }}:</strong>
                                    {!! Form::text('half', null, ['id' => 'EditHalf', 'placeholder' => __('term-fee.modal.term_fee_half'), 'class' => 'auto_decimal form-control']) !!}

                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-xs-6 col-sm-6 col-md-6">
                                <div class="form-group">
                                    <strong><i class="fa-solid fa-comment-dollar"></i> {{ __('term-fee.modal.book_price') }}:</strong>
                                    {!! Form::text('book', null, ['id' => 'EditBook', 'placeholder' => __('term-fee.modal.book_price'), 'class' => 'auto_decimal form-control']) !!}

                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-xs-6 col-sm-6 col-md-6">
                                <div class="form-group">
                                    <strong><i class="fa-solid fa-comment-dollar"></i> {{ __('term-fee.modal.book_half_price') }}:</strong>
                                    {!! Form::text('book_half', null, ['id' => 'EditBookHalf', 'placeholder' => __('term-fee.modal.book_half_price'), 'class' => 'auto_decimal form-control']) !!}

                                </div>
                            </div>

                        </div>

                        {!! Form::close() !!}
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="SubmitEditForm"><i class="fas fa-download"></i>
                        {{ __('term-fee.actions.save') }}</button>
                    <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal"><i
                            class="fas fa-door-closed"></i> {{ __('term-fee.actions.close') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
