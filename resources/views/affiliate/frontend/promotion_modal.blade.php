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
            <!-- Close Button Moved Outside modal-body -->
            <button type="button" class="btn-close bg-danger position-absolute top-0 end-0 m-2" data-bs-dismiss="modal"
                aria-label="Close" style="z-index: 1055;"></button>

            <div class="modal-body p-0 text-center">
                @if (count($promotion) > 1) <!-- Check if more than 1 image -->
                    <div id="promoCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            @foreach ($promotion as $key => $image)
                                <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
                                    <a href="{{ $image->url ?? '#'}}" target="_blank">
                                        <img src="{{ asset($image->img) }}" alt="Promotion"
                                            class="img-fluid w-100 rounded">
                                    </a>
                                </div>
                            @endforeach
                        </div>
                        <!-- Carousel controls -->
                        <button class="carousel-control-prev" type="button" data-bs-target="#promoCarousel"
                            data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#promoCarousel"
                            data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                @else
                    <a href="{{ $promotion->first()->url ?? '#'}}" target="_blank">
                        <img src="{{ asset($promotion->first()->img) }}" alt="Promotion" class="img-fluid w-100 rounded">
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>



<!-- Initialize Modal with JavaScript -->
<script>
    // Ensure modal close functionality works even with dynamic content
    var promoModal = new bootstrap.Modal(document.getElementById('promoModal'), {
        keyboard: true
    });

    // If you need to manually trigger the modal or close it, you can use:
    // promoModal.show(); // To open
    // promoModal.hide(); // To close
</script>
