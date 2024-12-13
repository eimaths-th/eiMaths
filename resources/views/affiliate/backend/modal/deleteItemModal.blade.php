<div class="modal fade deleteForm" id="deleteItemModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Delete Withdraw </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal">
                    <i class="ti-close "></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <h4>Are you sure to delete ?</h4>
                </div>
                <div class="mt-40 d-flex justify-content-between">
                    <button type="button" class="primary-btn tr-bg"
                        data-bs-dismiss="modal">Cancel</button>
                    <form id="item_delete_form">
                        <input type="hidden" name="id" id="delete_item_id">
                        <input id="dataDeleteBtn" type="submit" class="primary-btn tr-bg"
                            value="Delete" />
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>