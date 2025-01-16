 <!-- Edit  Modal -->
 <div class="fade modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" id="EditModal">
     <div class="modal-dialog modal-lg" role="document">
         <form id="editdata" class="form" action="" method="POST">
             <div class="modal-content">
                 <div class="modal-header bg-primary">
                     <h4 class="modal-title" id="exampleModalLongTitle"><i class="fas fa-user"></i> Edit Line Api</h4>
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
                                     <label for="Name"><i class="fas fa-user"></i>name :</label>
                                     <input type="text" class="form-control" name="name"
                                         id="editname" value="">
                                 </div>
                             </div>
                             <div class="col-xs-12 col-sm-12 col-md-12">
                                 <div class="form-group">
                                     <label for="Name"><i class="fas fa-user"></i> line_user_id:</label>
                                     <input type="text" class="form-control" name="line_user_id"
                                         id="line_user_id" value="">
                                 </div>
                             </div>
                             <div class="col-xs-12 col-sm-12 col-md-12">
                                 <div class="form-group">
                                     <label for="Name">channel_secret:</label>
                                     <input type="text" class="form-control" name="channel_secret"
                                         id="channel_secret" value="">
                                 </div>
                             </div>
                             <div class="col-xs-12 col-sm-12 col-md-12">
                                 <div class="form-group">
                                     <label for="Name">channel_access_token:</label>
                                     <input type="text" class="form-control" name="channel_access_token"
                                         id="channel_access_token" value="">
                                 </div>
                             </div>


                         </div>

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
