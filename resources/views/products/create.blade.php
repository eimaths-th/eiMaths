<div class="modal fade" id="CreateModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title"> <i class="fas fa-cubes"></i> Add Acceseries
                </h4>
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
                <div class="row">
                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <div class="form-group">
                            <strong><i class="fas fa-code"></i> Centre Code:</strong>
                            <select style="width: 100%;" class="productl select2 select2_single form-control"
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
                            <strong>Acceseries
                                Code:</strong>
                            {!! Form::text('name', null, [
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
                            <strong>Acceseries
                                Name:</strong>
                            {!! Form::text('name', null, ['id' => 'AddName', 'placeholder' => 'Name', 'class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <strong>Acceseries Detail
                                :</strong>
                            {!! Form::textarea('detail', null, [
                                'rows' => 4,
                                'id' => 'AddDetail',
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
                                'id' => 'AddUnit',
                                'placeholder' => 'Unit',
                                'class' => 'form-control',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-xs-6 col-sm-6 col-md-6">
                        <div class="form-group">
                            <strong>Quantity:</strong>
                            {!! Form::input('number', 'amount', null, [
                                'id' => 'AddAmount',
                                'placeholder' => 'Quantity',
                                'class' => 'form-control auto_decimal',
                                'step' => '1.0',
                            ]) !!}
                        </div>
                    </div>
                </div>



                {!! Form::close() !!}
            </div>
            <div class="modal-footer {{-- justify-content-between --}}">
                <button type="button" class="btn btn-success" id="SubmitCreateForm"><i class="fas fa-download"></i>
                    Save</button>
                <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal"><i
                        class="fas fa-door-closed"></i> Close</button>
            </div>
        </div>
    </div>
</div>
