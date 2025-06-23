 <!-- Edit  Modal -->
 <div class="fade modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" id="EditModal">
     <div class="modal-dialog modal-lg" role="document">
         <form id="editdata" class="form" action="" method="POST">
             <div class="modal-content">
                 <div class="modal-header bg-primary">
                     <h4 class="modal-title" id="exampleModalLongTitle"><i class="fas fa-user"></i> Edit User</h4>
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
                         <div class="row">
                             <div class="col-xs-12 col-sm-12 col-md-12">
                                 <div class="form-group">
                                     <label for="Name"><i class="fas fa-user"></i> User Name:</label>
                                     <input type="text" class="form-control" name="name" id="editName"
                                         value="">
                                 </div>
                             </div>
                             <div class="col-xs-12 col-sm-12 col-md-12">
                                 <div class="form-group">
                                     <label for="Name"><i class="fas fa-at"></i> Email:</label>
                                     <input type="text" class="form-control" name="email" id="editEmail"
                                         value="">
                                 </div>
                             </div>
                             <div class="col-xs-6 col-sm-6 col-md-6">
                                 <div class="form-group">
                                     <strong><i class="fas fa-list-ol"></i> Centre:</strong>
                                     <select style="width: 100%;"
                                         class="departmente select2 select2_single form-control" id="EditDepartment"
                                         name="edepartment" multiple="multiple">
                                         @can('all-centre')
                                             @foreach ($department as $key2)
                                                 <option value="{{ $key2->id }}">{{ $key2->name }}
                                                 </option>
                                             @endforeach
                                         @else
                                             @foreach ($department as $key2)
                                                 @if (Auth::user()->department->id == (int) $key2->id)
                                                     <option value="{{ $key2->id }}">
                                                         {{-- {{ $key2->code }} --}}
                                                         {{ $key2->name }}
                                                     </option>
                                                 @endif
                                             @endforeach
                                         @endcan


                                     </select>
                                 </div>
                             </div>
                             <div class="col-xs-6 col-sm-6 col-md-6">
                                 <div class="form-group">
                                     <strong><i class="fas fa-list-ol"></i> Department:</strong>
                                     <select style="width: 100%;" class="positions select2 select2_single form-control"
                                         id="EditPosition" name="eposition" multiple="multiple">
                                         {{--  @foreach ($position as $key)
                                            <option value="{{ $key->id }}">{{ $key->name }}
                                            </option>
                                        @endforeach
--}}

                                     </select>
                                 </div>
                             </div>
                             <div class="col-xs-12 col-sm-12 col-md-12">
                                 <div class="form-group">
                                     <label for="Name"><i class="fas fa-key"></i> Password:</label>
                                     <input type="password" class="form-control" name="epassword" required
                                         autocomplete="new-password" id="EditPassword">
                                 </div>
                             </div>
                             <div class="col-xs-12 col-sm-12 col-md-12">
                                 <div class="form-group">
                                     <label for="Name"><i class="fas fa-key"></i> Confirm Password:</label>
                                     <input type="password" class="form-control" name="epassword_confirmation"
                                         id="EditPasswordC" required autocomplete="new-password">
                                 </div>
                             </div>

                             <div class="col-xs-12 col-sm-12 col-md-12">
                                 <div class="form-group">
                                     <label for="Name"><i class="fas fa-user-lock"></i> Role:</label>
                                     <select class="form-control" id="editRole" name="role">
                                     </select>
                                 </div>
                             </div>

                             <div class="col-xs-6 col-sm-6 col-md-6">
                                 <div class="form-group">
                                     <strong><i class="fas fa-eye"></i> Disable:</strong>
                                     <br />
                                     <div class="custom-control custom-switch">
                                         {{ Form::checkbox('status', '1', false, ['id' => 'ecustomCheckbox1', 'class' => 'custom-control-input name']) }}
                                         <label for="ecustomCheckbox1" class="custom-control-label">
                                             Yes</label>
                                     </div>

                                 </div>
                             </div>



                         </div>

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
