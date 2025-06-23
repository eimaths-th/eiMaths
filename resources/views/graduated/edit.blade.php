<!-- Edit  Modal -->
<div class="fade modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" id="EditModal">
    <div class="modal-dialog modal-xl" role="document">
        <form id="editdata" class="form" action="" method="POST">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title" id="exampleModalLongTitle"><i class="fas fa-graduation-cap"></i> Edit
                        Student</h4>
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

                        <div class="card card-success card-tabs">
                            <div class="card-header p-0 pt-1">
                                <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="custom-tabs-one-homee-tab" data-toggle="pill"
                                            href="#custom-tabs-one-homee" role="tab"
                                            aria-controls="custom-tabs-one-homee" aria-selected="true">Student
                                            Detail</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="custom-tabs-one-addresse-tab" data-toggle="pill"
                                            href="#custom-tabs-one-addresse" role="tab"
                                            aria-controls="custom-tabs-one-addresse" aria-selected="true">Address
                                            Detail</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="custom-tabs-one-profilee-tab" data-toggle="pill"
                                            href="#custom-tabs-one-profilee" role="tab"
                                            aria-controls="custom-tabs-one-profilee" aria-selected="false">Parent
                                            Detail</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="custom-tabs-one-dise-tab" data-toggle="pill"
                                            href="#custom-tabs-one-dise" role="tab"
                                            aria-controls="custom-tabs-one-dise" aria-selected="false">Discontinued</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="tab-content" id="custom-tabs-one-tabContente">
                                    <div class="tab-pane fade show active" id="custom-tabs-one-homee" role="tabpanel"
                                        aria-labelledby="custom-tabs-one-homee-tab">
                                        <div class="card">
                                            <div class="card-body">
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
                                                    <div class="col-xs-6 col-sm-6 col-md-6">
                                                        <div class="form-group">
                                                            <strong><i class="fas fa-code"></i> Student No.</strong>
                                                            {!! Form::text('code', null, [
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
                                                            <strong><i class="fas fa-calendar"></i> Start
                                                                Date:</strong>
                                                            {!! Form::text('start_date', null, [
                                                                'id' => 'EditDate',
                                                                'placeholder' => '',
                                                                'class' => 'AddDate form-control',
                                                                'data-target' => '#reservationdate',
                                                            ]) !!}
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-6 col-sm-6 col-md-6">
                                                        <div class="form-group">
                                                            <strong><i class="fas fa-book-open"></i> Start
                                                                Term:</strong>
                                                            <select style="width: 100%;"
                                                                class="select2 select2_single form-control"
                                                                id="EditSTerm" name="sterm" multiple="multiple">
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
                                                            <strong><i class="fas fa-graduation-cap"></i>
                                                                Name:</strong>
                                                            {!! Form::text('name', null, ['id' => 'EditName', 'placeholder' => 'Name', 'class' => 'form-control']) !!}
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-6 col-sm-6 col-md-6">
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

                                                </div>
                                                <div class="row">
                                                    <div class="col-xs-6 col-sm-6 col-md-6">
                                                        <div class="form-group">
                                                            <strong><i class="fas fa-school"></i> School:</strong>
                                                            {!! Form::text('school', null, ['id' => 'EditSchool', 'placeholder' => 'School', 'class' => 'form-control']) !!}
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
                                                    <div class="col-xs-3 col-sm-3 col-md-3">
                                                        <div class="form-group">
                                                            <strong><i class="fas fa-book-medical"></i>
                                                                BookUse:</strong>
                                                            <select style="width: 100%;"
                                                                class="select2 select2_single form-control ebooks"
                                                                id="EditBook" name="book" multiple="multiple">
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
                                                            <strong><i class="fas fa-calendar"></i> Birth
                                                                Date:</strong>
                                                            {!! Form::text('ebirth_date', null, [
                                                                'id' => 'EditBirthDate',
                                                                'placeholder' => '',
                                                                'class' => 'AddDate form-control',
                                                                'data-target' => '#reservationdate',
                                                            ]) !!}
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-6 col-sm-6 col-md-6">
                                                        <div class="form-group">
                                                            <strong><i class="fa-solid fa-venus-mars"></i>
                                                                Gender:</strong>
                                                            <div class="form-group clearfix">
                                                                <div class="icheck-success d-inline">
                                                                    <input type="radio" name="egender"
                                                                        value="1" checked id="eradioSuccess1">
                                                                    <label for="eradioSuccess1">Male
                                                                    </label>
                                                                </div>
                                                                <div class="icheck-success d-inline">
                                                                    <input type="radio" name="egender"
                                                                        value="2" id="eradioSuccess2">
                                                                    <label for="eradioSuccess2">Female
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="custom-tabs-one-addresse" role="tabpanel"
                                        aria-labelledby="custom-tabs-one-addresse-tab">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row">

                                                    <div class="col-xs-12 col-sm-12 col-md-12">
                                                        <div class="form-group">
                                                            <strong><i class="fas fa-address-card"></i>
                                                                Address:</strong>
                                                            {!! Form::textarea('address', null, [
                                                                'rows' => 4,
                                                                'id' => 'EditAddress',
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
                                                                'id' => 'EditPostcode',
                                                                'placeholder' => 'Postcode',
                                                                'class' => 'form-control',
                                                            ]) !!}
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-6 col-sm-6 col-md-6">
                                                        <div class="form-group">
                                                            <strong><i class="fas fa-phone"></i> Telephone:</strong>
                                                            {!! Form::text('telephone', null, [
                                                                'id' => 'EditTelephone',
                                                                'placeholder' => 'Telephone',
                                                                'class' => 'form-control',
                                                            ]) !!}
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="custom-tabs-one-profilee" role="tabpanel"
                                        aria-labelledby="custom-tabs-one-profilee-tab">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-xs-6 col-sm-6 col-md-6">
                                                        <div class="form-group">
                                                            <strong><i class="fa-solid fa-user-tie"></i> Father
                                                                Name:</strong>
                                                            {!! Form::text('fname', null, ['id' => 'EditfName', 'placeholder' => 'Father Name', 'class' => 'form-control']) !!}
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-6 col-sm-6 col-md-6">
                                                        <div class="form-group">
                                                            <strong><i class="fas fa-at"></i> Email:</strong>
                                                            {!! Form::email('femail', null, [
                                                                'id' => 'EditfEmail',
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
                                                                'id' => 'EditfTelephone',
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
                                                            {!! Form::text('mname', null, ['id' => 'EditmName', 'placeholder' => 'Mother Name', 'class' => 'form-control']) !!}
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-6 col-sm-6 col-md-6">
                                                        <div class="form-group">
                                                            <strong><i class="fas fa-at"></i> Email:</strong>
                                                            {!! Form::email('memail', null, [
                                                                'id' => 'EditmEmail',
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
                                                                'id' => 'EditmTelephone',
                                                                'placeholder' => 'Mother Telephone',
                                                                'class' => 'form-control',
                                                            ]) !!}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="custom-tabs-one-dise" role="tabpanel"
                                        aria-labelledby="custom-tabs-one-dise-tab">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-xs-2 col-sm-2 col-md-2">
                                                        <div class="form-group">
                                                            <strong><i class="fas fa-handshake-slash"></i>
                                                                Discontinued:</strong>
                                                            <div class="custom-control custom-switch">
                                                                {{ Form::checkbox('ediscontinued', '1', false, ['id' => 'ecustomCheckbox1', 'class' => 'custom-control-input name']) }}
                                                                <label for="ecustomCheckbox1"
                                                                    class="custom-control-label">
                                                                    Discontinued</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-4 col-sm-4 col-md-4">
                                                        <div class="form-group">
                                                            <strong><i class="fas fa-calendar"></i> Date</strong>
                                                            {!! Form::text('ddate', null, [
                                                                'id' => 'EditDDate',
                                                                'placeholder' => '',
                                                                'class' => 'AddDate form-control',
                                                                'disabled' => true,
                                                            ]) !!}
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-6 col-sm-6 col-md-6">
                                                        <div class="form-group">
                                                            <strong><i class="fas fa-list"></i> Reason</strong>
                                                            {!! Form::text('reason', null, [
                                                                'id' => 'EditReason',
                                                                'placeholder' => '',
                                                                'class' => 'form-control',
                                                                'disabled' => true,
                                                            ]) !!}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row d-none" id="sreason">
                                                    <div class="col-xs-6 col-sm-6 col-md-6">
                                                    </div>
                                                    <div class="col-xs-6 col-sm-6 col-md-6 float-right">
                                                        <div class="form-group">
                                                            <strong><i class="fas fa-list"></i> Select Reason</strong>
                                                            <select name="ddl_discontinueReason"
                                                                id="ddl_discontinueReason" class="form-control">
                                                                <option selected="selected" value="">--Select
                                                                    Reason--</option>
                                                                <option value="Bored">Bored</option>
                                                                <option value="Dislike Homework">Dislike Homework
                                                                </option>
                                                                <option value="Dislike Teaching Mode">Dislike Teaching
                                                                    Mode</option>
                                                                <option value="Dislike Class Environment">Dislike Class
                                                                    Environment</option>
                                                                <option value="Home Tution">Home Tution</option>
                                                                <option value="Missing in Action">Missing in Action
                                                                </option>
                                                                <option value="No Time">No Time</option>
                                                                <option value="No Result">No Result</option>
                                                                <option value="Relocation">Relocation</option>
                                                                <option value="Special Needs Child">Special Needs Child
                                                                </option>
                                                                <option value="Too Expensive">Too Expensive</option>
                                                                <option value="Too Difficult">Too Difficult</option>
                                                                <option value="">Others</option>

                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="SubmitEditForm"><i
                            class="fas fa-download"></i> Save</button>
                    <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal"><i
                            class="fas fa-door-closed"></i> Close</button>
                </div>
            </div>
        </form>
    </div>
</div>
