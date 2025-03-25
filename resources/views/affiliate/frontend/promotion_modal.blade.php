{{-- <div class="modal fade" id="promoModal" tabindex="-1" aria-labelledby="promoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="promoModalLabel">🔥 Special Promotion! 🔥</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img src="{{ asset('img/promo-banner.jpg') }}" alt="Promotion Banner" class="img-fluid rounded">
                <h4 class="mt-3 text-danger">Limited Time Offer! 🚀</h4>
                <p>Enroll in our top courses now and enjoy **20% off**! 🎉</p>
                <p>Use code: <strong>PROMO20</strong> at checkout.</p>
            </div>
            <div class="modal-footer">
                <a href="{{ url('/courses') }}" class="btn btn-success">View Courses</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div> --}}
<!-- Promotion Image Modal -->
<div class="modal fade" id="promoModal" tabindex="-1" aria-labelledby="promoImageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body p-0 text-center">
                <button type="button" class="btn-close bg-danger position-absolute top-0 end-0 m-2" data-bs-dismiss="modal" aria-label="Close"></button>
                <img src="{{ asset('images/promotionDef.png') }}" alt="Promotion" class="img-fluid w-100 rounded">
            </div>
        </div>
    </div>
</div>
