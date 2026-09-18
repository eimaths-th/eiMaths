<div class="modal fade" id="discontinue-student-modal" tabindex="-1" role="dialog"
    aria-labelledby="discontinue-student-modal-title" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="discontinue-student-modal-title">
                    <i class="fa fa-handshake-slash"></i> Confirm not continuing
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal"
                    aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="discontinue-student-name" class="font-weight-bold mb-3"></p>
                <div class="alert alert-warning">
                    This will move the student to the Discontinued list. You can restart the student later from that list.
                </div>
                <div class="form-group">
                    <label for="discontinue-student-date">Discontinued date</label>
                    <input type="date" id="discontinue-student-date" class="form-control"
                        value="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="form-group mb-0">
                    <label for="discontinue-student-reason">Reason</label>
                    <input type="text" id="discontinue-student-reason" class="form-control"
                        value="Not continuing class" maxlength="255">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm-discontinue-student">
                    <i class="fa fa-check"></i> Confirm
                </button>
            </div>
        </div>
    </div>
</div>
