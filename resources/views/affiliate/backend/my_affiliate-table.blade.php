<div class="col-lg-9">
    <div class="row student-details student-details_tab mt_0_sm m-0">
        <div class="col-lg-12 p-0">
            <ul class="nav nav-tabs no-bottom-border mt_0_sm mb-20 m-0 justify-content-start"
                role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" href="#affiliate_link_tab" role="tab"
                        data-bs-toggle="tab">Affiliate Link</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#courses_registered_tab" role="tab"
                        data-bs-toggle="tab">Courses Registered</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#income_tab" role="tab"
                        data-bs-toggle="tab">Commissions</a>
                </li>

                
                <li class="nav-item">
                    <a class="nav-link" href="#transaction_tab" role="tab"
                        data-bs-toggle="tab">Withdraw</a>
                </li>
                <li class="nav-item ms-auto" >
                    <a title="Your balance less then minimum payout amount." id="withdraw_request_btn" type="button" href="#" class="nav-link">Withdraw Request</a>
                </li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content white-box mt-15">

                @include('affiliate.backend.table.affiliate_link')
                @include('affiliate.backend.table.courses_registered')
                @include('affiliate.backend.table.commission_history')
                @include('affiliate.backend.table.withdraw_history')
               
            </div>
        </div>
    </div>
</div>