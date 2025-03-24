<div class="modal fade" id="PrintModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title"><i class="fa-solid fa-cart-shopping"></i> Print</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="print_data">

            </div>
            <div class="modal-footer {{-- justify-content-between --}}">
                <button type="button" class="btn btn-success" onClick="printReport()"><i class="fas fa-print"></i> Print</button>
                <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal"><i class="fas fa-door-closed"></i> Close</button>
            </div>
        </div>
    </div>
</div>
