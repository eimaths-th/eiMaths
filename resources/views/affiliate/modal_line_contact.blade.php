<div class="modal fade" id="modalLineContact">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title text-success"> Payment</h3>

                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body" id="modal-body">
                <!-- <h4>Scan PromptPay</h4> 
                <h4>Scan PromptPay</h4>
                <hr>-->
                <div class="text-center">

                    

                    {{-- <img id="qrCodePayment" src="" alt="" style="width: 500px; height: auto;"> --}}
                    <img src="{{ asset('img/qrcode/eipayment.png') }}" alt="" style="width: 500px; height: auto;">

                </div>
                <hr>
                
                <div class="form-group">
                    {{-- bank account --}}
                    <label for="name">ชื่อบัญชี :</label>
                    <label for=""> {{ $qr_payment->account_name }} </label>
                </div>
                <div class="form-group">
                    {{-- bank account --}}
                    <label for="name">ธนาคาร :</label>
                    <label for=""> {{ $qr_payment->bank_name }} </label>
                </div>
                <div class="form-group">
                    <label for="name">เลขบัญชี :</label>
                    <span id="copy_account_number">{{ $qr_payment->account_numbers }}</span>
                    <button class="btn btn-primary" id="copy_account_number_btn">Copy</button>
                </div>
            </div>
            {{-- <hr> --}}
            <form id="paymentForm" enctype="multipart/form-data">
                @csrf
                <hr>
                <label for="payment_slip">Upload Payment Slip:</label>
                <input type="file" id="payment_slip" name="payment_slip" class="form-control" accept="image/*"
                    required>
                <img id="slip_preview" src="" alt="Slip Preview"
                    style="display:none; width: 200px; margin-top: 10px;">
              
                <input type="hidden" name="coursePending_id" id="coursePending_id" value="">
                
                <div class="modal-footer payment_btn d-flex justify-content-between" id="modal-footer">
                    <button type="button" class="btn theme_btn small_btn3" id="downloadQrButton">Download QR</button>
                    <button type="submit" class="btn btn-success">Submit </button>
                </div>
            </form>

           
        </div>
    </div>
</div>
