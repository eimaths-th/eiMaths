<div class="modal fade" id="CreateModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h4 class="modal-title"><i class="fas fa-truck"></i> เพิ่ม รายการขาย</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{-- @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <strong>Whoops!</strong>Something went wrong.<br><br>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif --}}
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
                {!! Form::open(['method' => 'POST', 'class' => 'form','id'=>'create_form']) !!}

                <div class="card card-success card-tabs">
                    <div class="card-header p-0 pt-1">
                        <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="custom-tabs-one-home-tab" data-toggle="pill"
                                    href="#custom-tabs-one-home" role="tab" aria-controls="custom-tabs-one-home"
                                    aria-selected="true">รายละเอียดรายการขาย</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-one-profile-tab" data-toggle="pill"
                                    href="#custom-tabs-one-profile" role="tab"
                                    aria-controls="custom-tabs-one-profile"
                                    aria-selected="false">แนบสลิปหลักฐานการชำระเงิน</a>
                            </li>

                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="custom-tabs-one-tabContent">
                            <div class="tab-pane fade show active" id="custom-tabs-one-home" role="tabpanel"
                                aria-labelledby="custom-tabs-one-home-tab">

                                <div class="row">
                                    <div class="col-xs-6 col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <strong>{{-- <i class="fas fa-list-ol"></i> --}} หมายเลขออเดอร์:</strong>
                                            {!! Form::text('lot', null, [
                                                'id' => 'AddLot',
                                                'placeholder' => 'Order Number',
                                                'class' => 'form-control',
                                                'required' => 'required',
                                                'readonly' => true
                                            ]) !!}
                                        </div>
                                    </div>
                                    <div class="col-xs-6 col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <strong>{{-- <i class="fas fa-cubes"></i> --}} สินค้า:</strong>
                                            <select style="width: 100%;"
                                                class="productl select2 select2_single form-control" id="AddProduct"
                                                name="product" multiple="multiple">
                                                <!-- <option value="" selected>Select Student</option>
                                                                                                                                                                                                                                                                                                                                                                                                    <option value="" selected>Select Parent</option>-->
                                                @foreach ($product as $key2)
                                                    <option value="{{ $key2->id }}">{{ $key2->name }}
                                                    </option>
                                                @endforeach

                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                        <table id="myTbl3"
                                            class="table table-striped table-bordered responsive-utilities jambo_table "
                                            width="400">
                                            <thead>
                                                <tr class="headings">
                                                    {{-- <th class="column-title"> สินค้า</th> --}}
                                                    <th class="column-title">{{-- <i class="fas fa-cubes"></i> --}} ล็อตสินค้าในคลังสินค้า</th>
                                                    <th class="column-title">{{-- <i class="fas fa-warehouse"></i> --}} จำนวนที่ขาย</th>
                                                    <th class="column-title">{{-- <i class="fas fa-dollar-sign"></i> --}} ราคาต่อหน่วย</th>
                                                    <th class="column-title">{{-- <i class="fas fa-dollar-sign"></i> --}} รายรับที่ได้</th>
                                                    <th class="column-title"> </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="firstTr3">
                                                    {{--  <td width="30%">
                                                                <div class="col-md-12 col-sm-12 col-xs-12">
                                                                    <input type="text" id="flown[]" name="flown[]"
                                                                        class="form-control has-feedback-left" value=""
                                                                        required="required">
                                                                </div>
                                                            </td> --}}
                                                    <td width="30%">
                                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                                            <select style="width: 100%;" class="products form-control"
                                                                id="stock" name="stock[]" required>
                                                                <!-- <option value="" selected>Select Student</option>
                                                                                                                                                                                                                                                                                                                                                                                                    <option value="" selected>Select Parent</option>-->
                                                                {{-- @foreach ($product as $key2)
                                                    <option value="{{ $key2->id }}">{{ $key2->name }}
                                                    </option>
                                                @endforeach --}}

                                                            </select>
                                                            <div id="lot_price" class="text-success"></div>
                                                            <div id="lot_error" class="text-danger"></div>
                                                        </div>
                                                    </td>

                                                    <td width="10%">
                                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                                            <input type="number" step="0.50" id="amount"
                                                                name="amount[]"
                                                                class="auto_decimal form-control has-feedback-left"
                                                                value="" required="required">
                                                        </div>
                                                    </td>
                                                    <td width="10%">
                                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                                            <input type="number" step="0.50" id="price"
                                                                name="price[]"
                                                                class="auto_decimal form-control has-feedback-left"
                                                                value="" required="required">
                                                        </div>
                                                    </td>

                                                    <td width="10%">
                                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                                            <input type="number" step="0.50" id="total"
                                                                name="total[]"
                                                                class="auto_decimal form-control has-feedback-left"
                                                                value="" readonly>
                                                        </div>
                                                    </td>

                                                    <td width="10%"><button type="button" id="removeRowt"
                                                            class="btn btn-sm btn-danger btnRemoveg2"><i
                                                                class="fa fa-minus"></i></button></td>

                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-md-12" align="right">

                                        <button type="button" id="addRow2"
                                            class="btn btn-sm btn-primary btnAddg"><i class="fa fa-plus"></i></button>
                                        <button type="button" id="removeRow2"
                                            class="btn btn-sm btn-danger btnRemoveg"><i
                                                class="fa fa-minus"></i></button>
                                    </div>
                                </div>
                                {{-- <div class="row">

                                    <div class="col-xs-6 col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <strong>สินค้าในคลังสินค้า:</strong>
                                            <select style="width: 100%;" class="products select2 select2_singles form-control"
                                                id="AddStock" name="stock" multiple="multiple">
                                            </select>
                                        </div>
                                    </div>
                                </div> --}}
                                <div class="row">
                                    <div class="col-xs-6 col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <strong>{{-- <i class="fas fa-building"></i> --}} บริษัทที่ซื้อ:</strong>
                                            <select style="width: 100%;" class="select2 select2_singlec form-control"
                                                id="AddCompany" name="companies" multiple="multiple">
                                                <!-- <option value="" selected>Select Student</option>
                                                                                                                                                                                                                                                                                                                                                                                                    <option value="" selected>Select Parent</option>-->
                                                @foreach ($contact as $key2)
                                                    <option value="{{ $key2->id }}">{{ $key2->name }}
                                                    </option>
                                                @endforeach

                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-xs-6 col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <strong>{{-- <i class="fas fa-warehouse"></i> --}} รวมจำนวนที่ขาย:</strong>
                                            {!! Form::text('tamount', null, [
                                                'id' => 'AddAmount',
                                                'placeholder' => 'Amount',
                                                'class' => 'form-control',
                                                'readonly' => 'true',
                                            ]) !!}
                                        </div>
                                    </div>

                                </div>
                                <div class="row">

                                    {{-- <div class="col-xs-6 col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <strong>ราคาต่อหน่วย:</strong>
                                            {!! Form::text('price', null, ['id' => 'AddCost', 'placeholder' => 'Price', 'class' => 'form-control']) !!}
                                        </div>
                                    </div> --}}
                                    <div class="col-xs-6 col-sm-6 col-md-6">
                                        <div class="form-group">
                                            <strong>{{-- <i class="fas fa-dollar-sign"></i> --}} รวมรายรับที่ได้:</strong>
                                            {!! Form::text('total_cost', null, [
                                                'id' => 'AddTotalCost',
                                                'placeholder' => 'Total',
                                                'class' => 'form-control',
                                                'readonly' => 'true',
                                            ]) !!}
                                        </div>
                                    </div>

                                </div>

                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                        <div class="form-group">
                                            <strong>{{-- <i class="fas fa-comment"></i> --}} รายละเอียด:</strong>
                                            {!! Form::textarea('detail', null, [
                                                'rows' => 4,
                                                'id' => 'AddDetail',
                                                'class' => 'form-control',
                                            ]) !!}
                                        </div>
                                    </div>
                                </div>


                            </div>
                            <div class="tab-pane fade" id="custom-tabs-one-profile" role="tabpanel"
                                aria-labelledby="custom-tabs-one-profile-tab">
                                <div class="form-row form-focus" id="dropzone-att">
                                    <div class="col-md-12 col-sm-12">
                                        <a id="datt"></a>
                                        <div class="position-relative form-group">
                                            <label class="form-label form-label-top form-label-auto"
                                                for="att"><i class="fas fa-file-image"></i> อัพโหลดหลักฐานการชำระเงิน<span
                                                    class="form-required">*</span>
                                                <div style="width: 280px"></div>
                                            </label>
                                            <input type="text" id="drop" name="drop"
                                                class="form-control form_none" value="" required>
                                            <div class="dropzone" id="my-awesome-dropzone" style="font-size: 1.5em;">
                                                <div class="fallback">

                                                </div>
                                            </div>
                                            <label class="form-sub-label" style="min-height:13px"
                                                aria-hidden="false">รูปภาพใบเสร็จ</label>
                                            <div id="dropzone-att-error" class="form-error-message" role="alert">
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
                            </div>
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
            <div class="modal-footer {{-- justify-content-between --}}">
                <button type="button" class="btn btn-success" id="SubmitCreateForm"><i class="fas fa-download"></i> บันทึก</button>
                <button type="button" class="btn btn-danger modelClose" data-dismiss="modal"><i class="fas fa-door-closed"></i> ปิด</button>
            </div>
        </div>
    </div>
</div>
