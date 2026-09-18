<!-- Edit  Modal -->
<div class="fade modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" id="EditModal">
    <div class="modal-dialog modal-lg" role="document">
        <form id="editdata" class="form" action="" method="POST">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title" id="exampleModalLongTitle"> <i class="fas fa-cubes"></i> {{ __('products.modals.edit') }}
                    </h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ __('common.actions.close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="modal-body">
                    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="display: none;">
                        <button type="button" class="btn-close" data-dismiss="alert" aria-label="{{ __('common.actions.close') }}">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;">
                        <strong>{{ __('common.feedback.saved') }}</strong>
                        <button type="button" class="btn-close" data-dismiss="alert" aria-label="{{ __('common.actions.close') }}">
                            <span aria-hidden="true"></span>
                        </button>
                    </div>
                    <div id="EditModalBody">
                        {!! Form::open(['method' => 'POST']) !!}
                        <div class="row">

                            <div class="col-xs-6 col-sm-6 col-md-6">
                                <div class="form-group">
                                    <strong><i class="fas fa-code"></i> {{ __('products.fields.centre_code') }}:</strong>
                                    <select style="width: 100%;" class="productl select2 select2_single form-control"
                                        id="EditCentre" name="centre" multiple="multiple"
                                        @cannot('all-centre')  @endcannot disabled>
                                        <!-- <option value="" selected>Select Student</option>
                                                                                                                                                                                                                                                                                                                                                                                <option value="" selected>Select Parent</option>-->
                                        @foreach ($centre as $key2)
                                            <option value="{{ $key2->id }}"
                                                @if (Auth::user()->department->id == (int) $key2->id) selected @endif>
                                                {{ $key2->code }}
                                                {{ $key2->name }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>
                            <div class="col-xs-6 col-sm-6 col-md-6">
                                <div class="form-group">
                                    <strong>{{ __('products.fields.accessory_code') }}:</strong>
                                    {!! Form::text('name', null, [
                                        'id' => 'EditCode',
                                        'placeholder' => __('products.table.code'),
                                        'class' => 'form-control',
                                        'readonly' => true,
                                    ]) !!}

                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-xs-6 col-sm-6 col-md-6">
                                <div class="form-group">
                                    <strong>{{ __('products.fields.accessory_name') }}:</strong>
                                    {!! Form::text('name', null, ['id' => 'EditName', 'placeholder' => __('products.table.name'), 'class' => 'form-control']) !!}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="form-group">
                                    <strong>{{ __('products.fields.accessory_detail') }}:</strong>
                                    {!! Form::textarea('detail', null, [
                                        'rows' => 4,
                                        'id' => 'EditDetail',
                                        'class' => 'form-control',
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-xs-6 col-sm-6 col-md-6">
                                <div class="form-group">
                                    <strong>{{ __('products.fields.unit') }}:</strong>
                                    {!! Form::text('unit', null, [
                                        'id' => 'EditUnit',
                                        'placeholder' => __('products.fields.unit'),
                                        'class' => 'form-control',
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-xs-6 col-sm-6 col-md-6">
                                <div class="form-group">
                                    <strong>{{ __('products.fields.quantity') }}:</strong>
                                    {!! Form::text('amount', null, [
                                        'id' => 'EditAmount',
                                        'readonly' => true,
                                        'placeholder' => __('products.fields.quantity'),
                                        'class' => 'auto_decimal form-control',
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="SubmitEditForm"><i class="fas fa-download"></i>
                        {{ __('common.actions.save') }}</button>
                    <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal"><i
                        class="fas fa-door-closed"></i> {{ __('common.actions.close') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
