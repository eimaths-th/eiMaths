<!-- Edit  Modal -->
<div class="fade modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" id="PlusModal">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title" id="exampleModalLongTitle"> <i class="fas fa-plus"></i> รับเข้า Stock
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
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <h1 id="istock" style="text-align: center; color: green;"></h1>
                        </div>
                    </div>
                    {!! Form::open(['method' => 'POST', 'class' => 'form']) !!}
                    <div class="row">
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <div class="form-group">
                                <strong><i class="fa-solid fa-barcode"></i> Acceseries
                                    Code:</strong>
                                {!! Form::text('pcode', null, [
                                    'id' => 'PlusCode',
                                    'placeholder' => 'Please Scan BarCode/QRcode',
                                    'class' => 'form-control',
                                    'readonly' => true,
                                    'autofocus' => true,
                                ]) !!}
                                {!! Form::hidden('pcodeh', null, ['id' => 'PlusCodeh']) !!}
                                {!! Form::hidden('stockt', 0, ['id' => 'Stockt']) !!}
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <div class="form-group">
                                <strong>Acceseries
                                    Name:</strong>
                                {!! Form::text('pname', null, [
                                    'id' => 'PlusName',
                                    'placeholder' => 'Name',
                                    'class' => 'form-control',
                                    'readonly' => true,
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong>Acceseries Detail
                                    :</strong>
                                {!! Form::textarea('pdetail', null, [
                                    'rows' => 4,
                                    'id' => 'PlusDetail',
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
                                    'id' => 'PlusUnit',
                                    'placeholder' => 'Unit',
                                    'class' => 'form-control',
                                    'readonly' => true,
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <div class="form-group">
                                <strong>Quantity:</strong>
                                {!! Form::input('number', 'pamount', 1, [
                                    'id' => 'PlusAmount',
                                    'placeholder' => 'Quantity',
                                    'class' => 'form-control auto_decimal',
                                    'step' => '1.00',
                                ]) !!}
                            </div>
                        </div>
                    </div>



                    {!! Form::close() !!}
                </div>
            </div>
            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="PlusButton"><i class="fas fa-download"></i>
                    Save</button>
                <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal"><i
                        class="fas fa-door-closed"></i> Close</button>
            </div>
        </div>
    </div>
</div>
