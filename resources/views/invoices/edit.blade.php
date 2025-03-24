 <!-- Edit  Modal -->
 <div class="fade modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" id="EditModal">
     <div class="modal-dialog modal-xl" role="document">
         <form id="editdata" class="form" action="" method="POST">
             <div class="modal-content">
                 <div class="modal-header bg-primary">
                     <h4 class="modal-title" id="exampleModalLongTitle"><i class="fa-solid fa-cart-shopping"></i> Create Receipt
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

                     {!! Form::open(['method' => 'POST', 'class' => 'form', 'id' => 'editdata']) !!}

                     <div class="card card-success card-tabs">
                         <div class="card-header p-0 pt-1">
                             <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                                 <li class="nav-item">
                                     <a class="nav-link active" id="custom-tabs-one-home2-tab" data-toggle="pill"
                                         href="#custom-tabs-one-home2" role="tab"
                                         aria-controls="custom-tabs-one-home2" aria-selected="true">New Student Detail</a>
                                 </li>
                                 <li class="nav-item">
                                     <a class="nav-link" id="custom-tabs-one-profile2-tab" data-toggle="pill"
                                         href="#custom-tabs-one-profile2" role="tab"
                                         aria-controls="custom-tabs-one-profile2" aria-selected="false">Attach a payin
                                         slip</a>
                                 </li>

                             </ul>
                         </div>
                         <div class="card-body">
                             <div class="tab-content" id="custom-tabs-one-tabContent">
                                 <div class="tab-pane fade show active" id="custom-tabs-one-home2" role="tabpanel"
                                     aria-labelledby="custom-tabs-one-home2-tab">

                                     <div id="EditModalBody">
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> Centre Code:</strong>
                                                    <select style="width: 100%;"
                                                        class="productl select2 select2_single form-control"
                                                        id="EditCentre" name="centre" multiple="multiple"
                                                        @cannot('all-centre') disabled @endcannot>
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
                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-layer-group"></i> Level:</strong>
                                                    <select style="width: 100%;"
                                                        class="select2 select2_single form-control elevels"
                                                        id="EditLevel" name="level" multiple="multiple">
                                                        @foreach ($level as $keyl)
                                                            <option value="{{ $keyl->id }}">
                                                                {{ $keyl->name }}
                                                            </option>
                                                        @endforeach

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-book-open"></i> Term:</strong>
                                                    <select style="width: 100%;"
                                                        class="select2 select2_single form-control eterms"
                                                        id="EditTerm" name="term" multiple="multiple">
                                                        @foreach ($term as $keyt)
                                                            <option value="{{ $keyt->id }}">
                                                                {{ $keyt->name }}
                                                            </option>
                                                        @endforeach

                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                         <div class="row">
                                             <div class="col-xs-6 col-sm-6 col-md-6">
                                                 <div class="form-group">
                                                     <strong><i class="fa-solid fa-hashtag"></i> Receipt Number:</strong>
                                                     {!! Form::text('lot', null, [
                                                         'id' => 'EditLot',
                                                         'placeholder' => 'Receipt Number',
                                                         'class' => 'form-control',
                                                         'readonly' => true,
                                                     ]) !!}
                                                 </div>
                                             </div>
                                             <div class="col-xs-6 col-sm-6 col-md-6">
                                                 <div class="form-group">
                                                     <strong><i class="fa-solid fa-list"></i> Type:</strong>
                                                     <select style="width: 100%;"
                                                         class="producte select2 select2_single form-control"
                                                         id="EditProduct" name="eproduct" multiple="multiple">
                                                         @foreach ($order_type as $key2)
                                                             <option value="{{ $key2['id'] }}">{{ $key2['name'] }}
                                                             </option>
                                                         @endforeach

                                                     </select>
                                                 </div>
                                             </div>
                                         </div>
                                         <div class="row">
                                             <div class="col-xs-12 col-sm-12 col-md-12">
                                                 <table id="myTbl"
                                                     class="table table-striped table-bordered responsive-utilities jambo_table "
                                                     width="400">
                                                     <thead>
                                                         <tr class="headings">
                                                             {{-- <th class="column-title"> สินค้า</th> --}}
                                                             <th class="column-title">
                                                                 Class Level</th>
                                                             <th class="column-title"> Amount
                                                             </th>
                                                             <th class="column-title"> Fee
                                                             </th>
                                                             <th class="column-title"> Total
                                                             </th>
                                                             {{-- <th class="column-title"> </th> --}}
                                                         </tr>
                                                     </thead>
                                                     <tbody id="EditModalBodyTable">
                                                         <tr class="firstTr">

                                                             <td width="30%">
                                                                 <div class="col-md-12 col-sm-12 col-xs-12">
                                                                     <select style="width: 100%;"
                                                                         class="products form-control" id="estock"
                                                                         name="estock[]" required>


                                                                     </select>
                                                                     <div id="lot_price" class="text-success"></div>
                                                                     <div id="lot_error" class="text-danger"></div>
                                                                 </div>
                                                             </td>

                                                             <td width="10%">
                                                                 <div class="col-md-12 col-sm-12 col-xs-12">
                                                                     <input type="number" step="0.50"
                                                                         id="eamount" name="eamount[]"
                                                                         class="form-control has-feedback-left"
                                                                         value="" required="required">
                                                                 </div>
                                                             </td>
                                                             <td width="10%">
                                                                 <div class="col-md-12 col-sm-12 col-xs-12">
                                                                     <input type="number" step="0.50"
                                                                         id="eprice" name="eprice[]"
                                                                         class="form-control has-feedback-left"
                                                                         value="" required="required">
                                                                 </div>
                                                             </td>

                                                             <td width="10%">
                                                                 <div class="col-md-12 col-sm-12 col-xs-12">
                                                                     <input type="number" step="0.50"
                                                                         id="etotal" name="etotal[]"
                                                                         class="form-control has-feedback-left"
                                                                         value="" readonly>
                                                                 </div>
                                                             </td>

                                                             {{-- <td width="10%"><button type="button"
                                                                     id="removeRowg"
                                                                     class="btn btn-sm btn-danger btnRemoveg"><i
                                                                         class="fa fa-minus"></i></button></td> --}}

                                                         </tr>
                                                     </tbody>
                                                 </table>
                                             </div>
                                             {{-- <div class="col-md-12" align="right">

                                                 <button type="button" id="addRow"
                                                     class="btn btn-sm btn-primary"><i
                                                         class="fa fa-plus"></i></button>
                                                 <button type="button" id="removeRow"
                                                     class="btn btn-sm btn-danger"><i
                                                         class="fa fa-minus"></i></button>
                                             </div> --}}
                                         </div>

                                         <div class="row">
                                             <div class="col-xs-6 col-sm-6 col-md-6">
                                                 <div class="form-group">
                                                     <strong><i class="fa-solid fa-graduation-cap"></i>
                                                         Student:</strong>
                                                     <select style="width: 100%;"
                                                         class="select2 select2_singlec form-control" id="EditCompany"
                                                         name="companies" multiple="multiple" disabled>
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
                                                     <strong><i class="fas fa-dollar-sign"></i> Refundable
                                                         Deposit:</strong>
                                                     {!! Form::number('refund', null, [
                                                         'id' => 'EditRefund',
                                                         'step' => '0.50',
                                                         'placeholder' => 'Refundable Deposit',
                                                         'class' => 'auto_decimal form-control',
                                                         'readonly' => false,
                                                     ]) !!}
                                                 </div>
                                             </div>
                                             {{-- <div class="col-xs-6 col-sm-6 col-md-6">
                                                 <div class="form-group">
                                                     <strong> จำนวนที่ขาย:</strong>
                                                     {!! Form::text('amount', null, [
                                                         'id' => 'EditAmount',
                                                         'placeholder' => 'Amount',
                                                         'class' => 'form-control',
                                                         'readonly' => 'true',
                                                     ]) !!}
                                                 </div>
                                             </div> --}}

                                         </div>
                                         <div class="row">

                                             <div class="col-xs-6 col-sm-6 col-md-6">
                                                 <div class="form-group">
                                                     <strong><i class="fas fa-dollar-sign"></i> Registration
                                                         Fees:</strong>
                                                     {!! Form::number('register_fee', null, [
                                                         'id' => 'EditRegisterFee',
                                                         'step' => '0.50',
                                                         'placeholder' => 'Registration Fees',
                                                         'class' => 'auto_decimal form-control',
                                                         'readonly' => false,
                                                     ]) !!}
                                                 </div>
                                             </div>
                                             <div class="col-xs-6 col-sm-6 col-md-6">
                                                 <div class="form-group">
                                                     <strong><i class="fa-solid fa-percent"></i> Promotion
                                                         :</strong>
                                                     {!! Form::number('promotion', null, [
                                                         'id' => 'EditPromotion',
                                                         'step' => '0.50',
                                                         'placeholder' => 'Promotion',
                                                         'class' => 'auto_decimal form-control',
                                                         'readonly' => false,
                                                     ]) !!}
                                                 </div>
                                             </div>

                                         </div>
                                         <div class="row">
                                             <div class="col-xs-6 col-sm-6 col-md-6">
                                                 <div class="form-group">
                                                     <strong><i class="fa-solid fa-hand-holding-dollar"></i> Total
                                                         Price:</strong>
                                                     {!! Form::number('total_cost', null, [
                                                         'id' => 'EditTotalCost',
                                                         'placeholder' => 'Total',
                                                         'class' => 'auto_decimal form-control',
                                                         'readonly' => 'true',
                                                     ]) !!}
                                                     {!! Form::number('total_costh', null, [
                                                         'id' => 'EditTotalCosth',
                                                         'type' => 'hidden', // Set the input type to 'hidden'
                                                         'hidden' => true, // Add the 'hidden' attribute
                                                     ]) !!}
                                                 </div>
                                             </div>
                                             <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong>{{-- <i class="fas fa-comment"></i> --}} Detail:</strong>
                                                    {!! Form::textarea('detail', null, [
                                                        'rows' => 1,
                                                        'id' => 'EditDetail',
                                                        'class' => 'form-control',
                                                    ]) !!}
                                                </div>
                                            </div>
                                         </div>


                                     </div>
                                 </div>
                                 <div class="tab-pane fade" id="custom-tabs-one-profile2" role="tabpanel"
                                     aria-labelledby="custom-tabs-one-profile2-tab">
                                     <div class="row imgs">

                                     </div>
                                     <div class="form-row form-focus" id="dropzone-att">
                                         <div class="col-md-12 col-sm-12">
                                             <a id="datt"></a>
                                             <div class="position-relative form-group">
                                                 <label class="form-label form-label-top form-label-auto"
                                                     for="att"><i class="fas fa-file-image"></i>
                                                     Upload proof of payment<span class="form-required">*</span>
                                                     <div style="width: 280px"></div>
                                                 </label>
                                                 <input type="text" id="drop2" name="drop2"
                                                     class="form-control form_none" value="" required>
                                                 <div class="dropzone" id="my-awesome-dropzone2"
                                                     style="font-size: 1.5em;">
                                                     <div class="fallback">

                                                     </div>
                                                 </div>
                                                 <label class="form-sub-label" style="min-height:13px"
                                                     aria-hidden="false">Slip image</label>
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
                                     </div>

                                 </div>
                             </div>
                         </div>
                     </div>
                     {!! Form::close() !!}
                 </div>
                 <!-- Modal footer -->
                 <div class="modal-footer">
                     <button type="button" class="btn btn-info" id="SubmitEditFormp"><i
                             class="fa-solid fa-hand-holding-dollar"></i>
                         Pending</button>
                     <button type="button" class="btn btn-success" id="SubmitEditForm"><i
                             class="fa-solid fa-sack-dollar"></i> Save</button>
                     <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal"><i
                             class="fas fa-door-closed"></i> Close</button>
                 </div>
             </div>
         </form>
     </div>
 </div>
