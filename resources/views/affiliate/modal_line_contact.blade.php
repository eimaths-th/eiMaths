<div class="modal fade" id="modalLineContact">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-success" >Successfully</h5>

                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="courseForm" class="form">
                @csrf

                <div class="modal-body" id="modal-body">

                    <div class="text-center">

                        <p class="theme_text">Pls contact Admin / โปรติดต่อ แอดมิน</p>

                        <img src="{{ asset('images/line/qr-line.jpg') }}" alt="" style="width: 500px; height: auto;">



                        <h3>
                            <a href="https://line.me/ti/p/z8UOvSf1hX" target="_blank" class="theme_btn">Click</a>

                        </h3>

                    </div>

                </div>
                <div class="modal-footer payment_btn d-flex justify-content-between" id="modal-footer">
                    <button type="button" class="btn theme_btn small_btn3" id="downloadQrButton">Download QR</button>
                    <button type="button" class="btn btn-danger small_btn3" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
