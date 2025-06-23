<div class="modal fade" id="EditModal">
    <div class="modal-dialog modal-xxl">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title"><i class="fas fa-graduation-cap"></i>Edit Parent</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">

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

                <form action="#" method="POST">
                    @csrf
                    <div class="card card-success card-tabs">
                        <div class="card-header p-0 pt-1">
                            <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="custom-tabs-one-home-tab" data-toggle="pill"
                                        href="#custom-tabs-one-home" role="tab" aria-controls="custom-tabs-one-home"
                                        aria-selected="true">Parent Detail</a>
                                </li>
                            </ul>
                        </div>


                        <div class="card-body">

                            <div class="alert alert-danger " id="edit_validate" role="alert" style="display: none;">

                            </div>

                            <div class="tab-content" id="custom-tabs-one-tabContent">
                                <div class="tab-pane fade show active" id="custom-tabs-one-home" role="tabpanel"
                                    aria-labelledby="custom-tabs-one-home-tab">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-xs-6 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <strong><i class="fas fa-code"></i> Centre Code1:</strong>
                                                        <select style="width: 100%;"
                                                            class="productl select2 select2_single form-control"
                                                            id="EditCentre" name="centre_id" multiple="multiple">

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

                                                        <select name="student"
                                                            id="Edits_student"class="select2 select2_multiple form-control s_student"
                                                            multiple="multiple">
                                                            <option value="">select student</option>

                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-xs-6 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <strong><i class="fas fa-calendar"></i> First Name
                                                            (ชื่อจริง):</strong>
                                                        <input type="text" name="fname" id="EditFname"
                                                            class="form-control" autocomplete="off"
                                                            placeholder="Enter Parent Name">
                                                    </div>
                                                </div>
                                                <div class="col-xs-6 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <strong><i class="fas fa-calendar"></i>Last Name
                                                            (นามสกุล):</strong>
                                                        <input type="text" name="lname" id="EditLname"
                                                            class="form-control" autocomplete="off"
                                                            placeholder="Enter Parent Last Name">

                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-xs-6 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <strong><i class="fas fa-calendar"></i> Telp.
                                                            (เบอร์โทรศัพท์):</strong>
                                                        <input type="text" name="telp" id="EditTelp"
                                                            class="form-control" autocomplete="off"
                                                            placeholder="Enter Parent telphone">
                                                    </div>
                                                </div>
                                                <div class="col-xs-6 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <strong><i class="fas fa-calendar"></i>Email
                                                            (อีเมลล์):</strong>
                                                        <input type="text" name="email" id="EditEmail"
                                                            class="form-control" autocomplete="off"
                                                            placeholder="Enter Parent Email">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-xs-6 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <strong><i class="fas fa-calendar"></i> Emergency
                                                            Contact(เบอร์ติดต่อด่วน)</strong>
                                                        <input type="text" name="emergency_contact"
                                                            id="EditEmergency" class="form-control"
                                                            autocomplete="off" placeholder="Enter Emergency Contact">
                                                    </div>
                                                </div>
                                                <div class="col-xs-6 col-sm-6 col-md-6">
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <strong><i class="fas fa-calendar"></i>Relationship
                                                                (ความสัมพันธ์):</strong>
                                                            <select name="relationship" id="EditRelationship"
                                                                class="form-control">
                                                                <option value="">Select Relation</option>
                                                                <option value="father">Father</option>
                                                                <option value="mother">Mother</option>
                                                                <option value="relative">Relative</option>
                                                                <option value="other">Other</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <strong class="mb-5"><i
                                                                    class="fas fa-calendar"></i>Gender (เพศ):
                                                            </strong>
                                                            <div class="row">
                                                                <div class="col-lg-6">
                                                                    <label>
                                                                        <input type="radio" name="gender"
                                                                            id="male" value="male"> Male
                                                                    </label>
                                                                </div>

                                                                <div class="col-lg-6">
                                                                    <label>
                                                                        <input type="radio" name="gender"
                                                                            id="female" value="female"> Female
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <strong><i class="fas fa-calendar"></i> Address:</strong>
                                                        <textarea name="address" id="EditAddress" rows="5" class="form-control" placeholder="Enter Address"></textarea>

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-xs-12 col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        <strong><i class="fas fa-calendar"></i>Orther Details
                                                            :</strong>
                                                        <textarea name="notes" id="EditNotes" rows="5" class="form-control" placeholder="Enter Detail"></textarea>

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                @error('password')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                                <div class="col-xs-6 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <strong><i class="fas fa-calendar"></i>Password.
                                                            (รหัสผ่าน):</strong>
                                                        <input type="password" name="password" id="EditPassword"
                                                            class="form-control" autocomplete="off"
                                                            placeholder="Enter Password">

                                                    </div>
                                                </div>
                                                <div class="col-xs-6 col-sm-6 col-md-6">
                                                    <div class="form-group">
                                                        <strong><i class="fas fa-calendar"></i>Conf Password. (ยืนยัน
                                                            รหัสผ่าน):</strong>
                                                        <input type="password" name="password_confirmation"
                                                            id="password_confirmation1" class="form-control"
                                                            autocomplete="off" placeholder="Enter Confirm Password">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </form>

            </div>

            <div class="modal-footer {{-- justify-content-between --}}">
                <button type="button" class="btn btn-success" id="SubmitEditForm"><i class="fas fa-download"></i>
                    Save </button>
                <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal"><i
                        class="fas fa-door-closed"></i> Close</button>
            </div>
        </div>
    </div>
</div>
