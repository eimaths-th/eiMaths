<div class="modal fade" id="CreateModal">
    <div class="modal-dialog modal-xxl">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title"><i class="fas fa-graduation-cap"></i> New Student</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
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
                {!! Form::open(['method' => 'POST', 'class' => 'form']) !!}

                <div class="card card-success card-tabs">
                    <div class="card-header p-0 pt-1">
                        <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="custom-tabs-one-home-tab" data-toggle="pill"
                                    href="#custom-tabs-one-home" role="tab" aria-controls="custom-tabs-one-home"
                                    aria-selected="true">Student Detail</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-one-address-tab" data-toggle="pill"
                                    href="#custom-tabs-one-address" role="tab"
                                    aria-controls="custom-tabs-one-address" aria-selected="true">Address Detail</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-one-profile-tab" data-toggle="pill"
                                    href="#custom-tabs-one-profile" role="tab"
                                    aria-controls="custom-tabs-one-profile" aria-selected="false">Parent Detail</a>
                            </li>
                           {{--  <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-one-course-tab" data-toggle="pill"
                                    href="#custom-tabs-one-course" role="tab" aria-controls="custom-tabs-one-course"
                                    aria-selected="false">Course & Book</a>
                            </li>  --}}
                             <li class="nav-item">
                                <a class="nav-link" id="custom-tabs-one-dis-tab" data-toggle="pill"
                                    href="#custom-tabs-one-dis" role="tab" aria-controls="custom-tabs-one-dis"
                                    aria-selected="false">Discontinued</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="custom-tabs-one-tabContent">
                            <div class="tab-pane fade show active" id="custom-tabs-one-home" role="tabpanel"
                                aria-labelledby="custom-tabs-one-home-tab">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> Centre Code:</strong>
                                                    <select style="width: 100%;"
                                                        class="productl select2 select2_single form-control"
                                                        id="AddCentre" name="centre" multiple="multiple"
                                                        @cannot('all-centre') disabled @endcannot>
                                                        <!-- <option value="" selected>Select Student</option>
                                                                                                                                                                                                                                                                                                                                                                                                    <option value="" selected>Select Parent</option>-->
                                                        @foreach ($centre as $key2)
                                                            <option value="{{ $key2->id }}"
                                                                @if (Auth::user()->department->id == (int) $key2->id) selected @endif>
                                                                {{-- {{ $key2->code }} --}}
                                                                {{ $key2->name }}
                                                            </option>
                                                        @endforeach

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> Student No.</strong>
                                                    {!! Form::text('code', null, [
                                                        'id' => 'AddCode',
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
                                                    <strong><i class="fas fa-calendar"></i> Start Date:</strong>
                                                    {!! Form::text('start_date', null, [
                                                        'id' => 'AddDate',
                                                        'placeholder' => '',
                                                        'class' => 'AddDate form-control',
                                                        'data-target' => '#reservationdate',
                                                    ]) !!}
                                                </div>
                                            </div>
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-book-open"></i> Start Term:</strong>
                                                    <select style="width: 100%;"
                                                        class="select2 select2_single form-control" id="AddSTerm"
                                                        name="sterm" multiple="multiple">
                                                        @foreach ($sterm as $skeyt)
                                                            <option value="{{ $skeyt->id }}">
                                                                {{ $skeyt->name }}
                                                            </option>
                                                        @endforeach

                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-graduation-cap"></i> Name:</strong>
                                                    {!! Form::text('name', null, ['id' => 'AddName', 'placeholder' => 'Name', 'class' => 'form-control']) !!}
                                                </div>
                                            </div>
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-layer-group"></i> Level:</strong>
                                                    <select style="width: 100%;"
                                                        class="select2 select2_single form-control levels"
                                                        id="AddLevel" name="level" multiple="multiple">
                                                        @foreach ($level as $keyl)
                                                            <option value="{{ $keyl->id }}">
                                                                {{ $keyl->name }}
                                                            </option>
                                                        @endforeach

                                                    </select>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-school"></i> School:</strong>
                                                    {!! Form::text('school', null, ['id' => 'AddSchool', 'placeholder' => 'School', 'class' => 'form-control']) !!}
                                                </div>
                                            </div>
                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-book-open"></i> Term:</strong>
                                                    <select style="width: 100%;"
                                                        class="select2 select2_single form-control terms"
                                                        id="AddTerm" name="term" multiple="multiple" readonly>
                                                        @foreach ($term as $keyt)
                                                            <option value="{{ $keyt->id }}">
                                                                {{ $keyt->name }}
                                                            </option>
                                                        @endforeach

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-xs-3 col-sm-3 col-md-3">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-book-medical"></i> BookUse:</strong>
                                                    <select style="width: 100%;"
                                                        class="select2 select2_single form-control books"
                                                        id="AddBook" name="book" multiple="multiple">
                                                        @foreach ($bookuse as $keyb)
                                                            <option value="{{ $keyb->id }}">
                                                                {{ $keyb->name }}
                                                            </option>
                                                        @endforeach

                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-calendar"></i> Birth Date:</strong>
                                                    {!! Form::text('birth_date', null, [
                                                        'id' => 'AddBirthDate',
                                                        'placeholder' => '',
                                                        'class' => 'AddDate form-control',
                                                        'data-target' => '#reservationdate',
                                                    ]) !!}
                                                </div>
                                            </div>
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fa-solid fa-venus-mars"></i> Gender:</strong>
                                                    <div class="form-group clearfix">
                                                        <div class="icheck-success d-inline">
                                                            <input type="radio" name="gender" value="1"
                                                                checked id="radioSuccess1">
                                                            <label for="radioSuccess1">Male
                                                            </label>
                                                        </div>
                                                        <div class="icheck-success d-inline">
                                                            <input type="radio" name="gender" value="2"
                                                                id="radioSuccess2">
                                                            <label for="radioSuccess2">Female
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                            </div>
                            <div class="tab-pane fade" id="custom-tabs-one-address" role="tabpanel"
                                aria-labelledby="custom-tabs-one-address-tab">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">

                                            <div class="col-xs-12 col-sm-12 col-md-12">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-address-card"></i> Address:</strong>
                                                    {!! Form::textarea('address', null, [
                                                        'rows' => 4,
                                                        'id' => 'AddAddress',
                                                        'class' => 'form-control',
                                                    ]) !!}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-code"></i> Postcode:</strong>
                                                    {!! Form::text('postcode', null, [
                                                        'id' => 'AddPostcode',
                                                        'placeholder' => 'Postcode',
                                                        'class' => 'form-control',
                                                    ]) !!}
                                                </div>
                                            </div>
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-phone"></i> Telephone:</strong>
                                                    {!! Form::text('telephone', null, [
                                                        'id' => 'AddTelephone',
                                                        'placeholder' => 'Telephone',
                                                        'class' => 'form-control',
                                                    ]) !!}
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="custom-tabs-one-profile" role="tabpanel"
                                aria-labelledby="custom-tabs-one-profile-tab">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fa-solid fa-user-tie"></i> Father
                                                        Name:</strong>
                                                    {!! Form::text('fname', null, ['id' => 'AddfName', 'placeholder' => 'Father Name', 'class' => 'form-control']) !!}
                                                </div>
                                            </div>
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-at"></i> Email:</strong>
                                                    {!! Form::email('femail', null, [
                                                        'id' => 'AddfEmail',
                                                        'placeholder' => 'Father Email',
                                                        'class' => 'form-control',
                                                    ]) !!}
                                                </div>
                                            </div>

                                        </div>
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-phone"></i> Mobile:</strong>
                                                    {!! Form::text('ftelephone', null, [
                                                        'id' => 'AddfTelephone',
                                                        'placeholder' => 'Father Telephone',
                                                        'class' => 'form-control',
                                                    ]) !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fa-solid fa-user-tie"></i> Mother
                                                        Name:</strong>
                                                    {!! Form::text('mname', null, ['id' => 'AddmName', 'placeholder' => 'Mother Name', 'class' => 'form-control']) !!}
                                                </div>
                                            </div>
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-at"></i> Email:</strong>
                                                    {!! Form::email('memail', null, [
                                                        'id' => 'AddmEmail',
                                                        'placeholder' => 'Mother Email',
                                                        'class' => 'form-control',
                                                    ]) !!}
                                                </div>
                                            </div>

                                        </div>
                                        <div class="row">
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-phone"></i> Mobile:</strong>
                                                    {!! Form::text('mtelephone', null, [
                                                        'id' => 'AddmTelephone',
                                                        'placeholder' => 'Mother Telephone',
                                                        'class' => 'form-control',
                                                    ]) !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="custom-tabs-one-dis" role="tabpanel"
                                aria-labelledby="custom-tabs-one-dis-tab">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-xs-2 col-sm-2 col-md-2">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-handshake-slash"></i>
                                                        Discontinued:</strong>
                                                    <div class="custom-control custom-switch">
                                                        {{ Form::checkbox('discontinued', '1', false, ['id' => 'customCheckbox1', 'class' => 'custom-control-input name', 'disabled' => true]) }}
                                                        <label for="customCheckbox1" class="custom-control-label">
                                                            Discontinued</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xs-4 col-sm-4 col-md-4">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-calendar"></i> Date</strong>
                                                    {!! Form::text('ddate', null, [
                                                        'id' => 'AddDDate',
                                                        'placeholder' => '',
                                                        'class' => 'form-control',
                                                        'disabled' => true,
                                                    ]) !!}
                                                </div>
                                            </div>
                                            <div class="col-xs-6 col-sm-6 col-md-6">
                                                <div class="form-group">
                                                    <strong><i class="fas fa-list"></i> Reason</strong>
                                                    {!! Form::text('reason', null, [
                                                        'id' => 'AddReason',
                                                        'placeholder' => '',
                                                        'class' => 'form-control',
                                                        'disabled' => true,
                                                    ]) !!}
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            {{-- <div class="tab-pane fade" id="custom-tabs-one-course" role="tabpanel"
                                aria-labelledby="custom-tabs-one-course-tab">
                                <div class="row">
                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                        <table id="myTbl"
                                            class="table table-striped table-bordered responsive-utilities jambo_table "
                                            width="400">
                                            <thead>
                                                <tr class="headings">

                                                    <th class="column-title">
                                                        Class Level</th>
                                                    <th class="column-title"> Amount
                                                    </th>
                                                    <th class="column-title"> Fee
                                                    </th>
                                                    <th class="column-title"> Total
                                                    </th>

                                                </tr>
                                            </thead>
                                            <tbody id="EditModalBodyTable">
                                                <tr class="firstTr">

                                                    <td width="30%">
                                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                                            <select style="width: 100%;" class="products form-control"
                                                                id="estock" name="estock[]" required>


                                                            </select>
                                                            <div id="lot_price" class="text-success"></div>
                                                            <div id="lot_error" class="text-danger"></div>
                                                        </div>
                                                    </td>

                                                    <td width="10%">
                                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                                            <input type="number" step="0.50" id="eamount"
                                                                name="eamount[]"
                                                                class="form-control has-feedback-left" value=""
                                                                required="required">
                                                        </div>
                                                    </td>
                                                    <td width="10%">
                                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                                            <input type="number" step="0.50" id="eprice"
                                                                name="eprice[]" class="form-control has-feedback-left"
                                                                value="" required="required">
                                                        </div>
                                                    </td>

                                                    <td width="10%">
                                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                                            <input type="number" step="0.50" id="etotal"
                                                                name="etotal[]" class="form-control has-feedback-left"
                                                                value="" readonly>
                                                        </div>
                                                    </td>

                                                    <td width="10%"><button type="button" id="removeRowg"
                                                            class="btn btn-sm btn-danger btnRemoveg"><i
                                                                class="fa fa-minus"></i></button></td>

                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-md-12" align="right">

                                        <button type="button" id="addRow" class="btn btn-sm btn-primary"><i
                                                class="fa fa-plus"></i></button>
                                        <button type="button" id="removeRow" class="btn btn-sm btn-danger"><i
                                                class="fa fa-minus"></i></button>
                                    </div>
                                </div>
                            </div> --}}

                        </div>
                    </div>
                </div>

                {!! Form::close() !!}
            </div>
            <div class="modal-footer {{-- justify-content-between --}}">
                <button type="button" class="btn btn-success" id="SubmitCreateForm"><i class="fas fa-download"></i>
                    Save </button>
                <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal"><i
                        class="fas fa-door-closed"></i> Close</button>
            </div>
        </div>
    </div>
</div>
