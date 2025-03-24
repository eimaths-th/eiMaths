<!-- Edit  Modal -->
<div class="fade modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" id="EditModal">
    <div class="modal-dialog modal-lg" role="document">
        <form id="editdata" class="form" action="" method="POST">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title" id="exampleModalLongTitle"> <i class="fas fa-cubes"></i> Edit Acceseries
                    </h4>
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
                        <strong>Success!</strong> Product was edit successfully.
                        <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true"></span>
                        </button>
                    </div>
                    <div id="EditModalBody">
                        {!! Form::open(['method' => 'POST']) !!}
                        <div class="row">

                            <div class="col-xs-6 col-sm-6 col-md-6">
                                <div class="form-group">
                                    <strong><i class="fas fa-code"></i> Centre Code:</strong>
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
                                    <strong>รหัสสินค้า:</strong>
                                    {!! Form::text('name', null, [
                                        'id' => 'EditCode',
                                        'placeholder' => 'Code',
                                        'class' => 'form-control',
                                        'readonly' => true,
                                    ]) !!}

                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-xs-6 col-sm-6 col-md-6">
                                <div class="form-group">
                                    <strong>ชื่อสินค้า:</strong>
                                    {!! Form::text('name', null, ['id' => 'EditName', 'placeholder' => 'Name', 'class' => 'form-control']) !!}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="form-group">
                                    <strong>รายละเอียด:</strong>
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
                                    <strong>Unit:</strong>
                                    {!! Form::text('unit', null, [
                                        'id' => 'EditUnit',
                                        'placeholder' => 'Unit',
                                        'class' => 'form-control',
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-xs-6 col-sm-6 col-md-6">
                                <div class="form-group">
                                    <strong>Quantity:</strong>
                                    {!! Form::text('amount', null, [
                                        'id' => 'EditAmount',
                                        'readonly' => true,
                                        'placeholder' => 'Quantity',
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
                        Save</button>
                    <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal"><i
                            class="fas fa-door-closed"></i> Close</button>
                </div>
            </div>
        </form>
    </div>
</div>
