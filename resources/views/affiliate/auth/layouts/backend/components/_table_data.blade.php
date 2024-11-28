<div class="row student-details student-details_tab affiliate_tabs_container mt_0_sm m-0 white_box">
    <div class="col-lg-12 p-0">
        <div
            class="d-flex flex-wrap flex-column flex-xxl-row justify-content-between align-items-center mb-4 affiliate_content">
            <ul class="nav nav-tabs affiliate_tabs no-bottom-border mt_0_sm mb-20 m-0 justify-content-start"
                role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" href="#affiliate_link_tab" role="tab" data-bs-toggle="tab">Affiliate
                        Link</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#income_tab" role="tab"
                       data-bs-toggle="tab">{{__('affiliate.Commissions')}}</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="#transaction_tab" role="tab"
                       data-bs-toggle="tab">{{__('affiliate.Withdraw')}}</a>
                </li>

            </ul>

            <div class="affiliate_modals">
                <button class="type1"
                        id="create_affiliate_link_modal_btn">{{__('affiliate.Create Affiliate Link')}}</button>
                <button class="type2"
                        id="affiliate_add_paypal_modal_btn">{{__('common.Add')}} {{__('affiliate.Paypal')}}</button>

                @include('affiliate.auth.layouts.backend.components._create_link')
                @include('affiliate.auth.layouts.backend.components._paypal_account')
            </div>
        </div>

        <!-- Tab panes -->
        <div class="tab-content mt-15">
            {{-- @include('affiliate.auth.layouts.backend.components.tableDataComponents._affiliate_link') --}}
            {{-- @include('affiliate.auth.layouts.backend.components.tableDataComponents._commissions') --}}
            {{-- @include('affiliate.auth.layouts.backend.components.tableDataComponents._transaction') --}}
        </div>
    </div>
</div>
