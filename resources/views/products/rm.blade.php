<!-- Edit  Modal -->
<div class="fade modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" id="RmModal">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h4 class="modal-title" id="exampleModalLongTitle"> <i class="fas fa-minus"></i> จ่ายออก
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
                            <h1 id="istock2" style="text-align: center; color: green;"></h1>
                        </div>
                    </div>
                    {!! Form::open(['method' => 'POST', 'class' => 'form']) !!}
                    <div class="row">
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <div class="form-group clearfix">
                                <strong><i class="fas fa-code"></i> นำจ่ายให้:</strong>
                                <div class="form-group ">
                                    <div class="icheck-success d-inline">
                                        <input type="radio" class="rtype" name="type" value="1" checked
                                            id="radioSuccess1">
                                        <label for="radioSuccess1">Student
                                        </label>
                                    </div>
                                    <div class="icheck-success d-inline">
                                        <input type="radio" class="rtype" name="type" value="2"
                                            id="radioSuccess2">
                                        <label for="radioSuccess2">Centre
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <div class="form-group">
                                <strong><i class="fas fa-code"></i> Centre Code:</strong>

                                <select style="width: 100%;" class="productl select2 select2_single form-control"
                                    id="RmCentre" name="rcentre" multiple="multiple" {{-- @cannot('all-centre') disabled @endcannot --}}>
                                    <!-- <option value="" selected>Select Student</option>
                                                                                                                                                                                                                                                                                                                                                                                        <option value="" selected>Select Parent</option>-->
                                    @foreach ($centre as $key2)
                                        @cannot('all-centre')
                                            @if (Auth::user()->department->id == (int) $key2->id)
                                                <option value="{{ $key2->id }}"{{ $key2->name }}>{{ $key2->name }}
                                                </option>
                                            @endif
                                        @else
                                            <option value="{{ $key2->id }}"{{ $key2->name }}>{{ $key2->name }}
                                            </option>
                                        @endcannot
                                    @endforeach

                                </select>
                            </div>
                        </div>
                        {{-- ต่อหน้า create view history --}}

                    </div>
                    <div class="row">
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <div class="form-group">
                                <strong><i class="fas fa-code"></i> Destination No.</strong>
                                {{-- {!! Form::text('code', null, [
                                            'id' => 'AddCode',
                                            'placeholder' => 'Code',
                                            'class' => 'form-control',
                                            'readonly' => true,
                                        ]) !!} --}}
                                <select name="student"
                                    id="s_student"class="select2 select2_single form-control s_student"
                                    multiple="multiple">
                                    <option value="">select student</option>

                                </select>
                                {{-- <input type="text" placeholder="Code" class="form-control" id="AddCode" name="code" readonly="ture"> --}}
                            </div>
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <div class="form-group">
                                <strong>Quantity:</strong>
                                {!! Form::input('number', 'ramount', 1, [
                                    'id' => 'RmAmount',
                                    'placeholder' => 'Quantity',
                                    'class' => 'form-control auto_decimal',
                                    'step' => '1.00',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <div class="form-group">
                                <strong><i class="fa-solid fa-barcode"></i> Acceseries
                                    Code:</strong>
                                {!! Form::text('rcode', null, [
                                    'id' => 'RmCode',
                                    'placeholder' => 'Please Scan BarCode/QRcode',
                                    'class' => 'form-control',
                                    'readonly' => true,
                                    'autofocus' => true,
                                ]) !!}
                                {!! Form::hidden('rcodeh', null, ['id' => 'RmCodeh']) !!}
                            </div>
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <div class="form-group">
                                <strong>Acceseries
                                    Name:</strong>
                                {!! Form::text('rname', null, [
                                    'id' => 'RmName',
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
                                {!! Form::textarea('rdetail', null, [
                                    'rows' => 4,
                                    'id' => 'RmDetail',
                                    'class' => 'form-control',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">

                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <div class="form-group">
                                <strong>Unit:</strong>
                                {!! Form::text('runit', null, [
                                    'id' => 'RmUnit',
                                    'placeholder' => 'Unit',
                                    'class' => 'form-control',
                                    'readonly' => true,
                                ]) !!}
                            </div>

                        </div>
                    </div>



                    {!! Form::close() !!}
                </div>
            </div>
            <!-- Modal footer -->
            <div class="modal-footer">
                {{--  <button type="button" class="btn btn-success" id="RmButton"><i class="fas fa-download"></i>
                    Save</button> --}}
                <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal"><i
                        class="fas fa-door-closed"></i> Close</button>
            </div>
        </div>
    </div>
</div>
