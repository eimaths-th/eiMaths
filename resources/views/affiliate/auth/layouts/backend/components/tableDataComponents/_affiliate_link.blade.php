<div role="tabpanel" class="tab-pane fade show active" id="affiliate_link_tab">
    <!-- <div class="main-title">
        <h3 class="mb-20">{{ __('affiliate.Affiliate Links') }}</h3>
    </div> -->
    <div class="col-xl-12">
        <div class="table-responsive">
            <table class="table custom_table3 mb-0 affiliate_link_tab">
                <thead>
                    <tr>
                        <th> {{ __('affiliate.Affiliate Link') }}</th>
                        <th>{{ __('affiliate.Visits') }}</th>
                        <th>{{ __('affiliate.Registered') }}</th>
                        <th>{{ __('affiliate.Purchased') }}</th>
                        <th>{{ __('affiliate.Commissions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // $data = [];
                    @endphp
                    @foreach ($data as $item)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center justify-content-between gap-3">
                                    <div class="link">{{ $item->affiliate_link ?? 'null'}}</div>

                                    <div class="affiliate_buttons d-flex gap-1">
                                        <button class="view_link" data-bs-toggle="modal"
                                            data-bs-target="#affiliate_view_link_modal" title="View Link">
                                            <svg width="14" height="12" viewBox="0 0 14 12" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M5.50065 6.00008C5.50065 5.17165 6.17222 4.50008 7.00065 4.50008C7.82908 4.50008 8.50065 5.17165 8.50065 6.00008C8.50065 6.82851 7.82908 7.50008 7.00065 7.50008C6.17222 7.50008 5.50065 6.82851 5.50065 6.00008Z"
                                                    fill="#2D264B" />
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                    d="M3.23514 1.73056C4.28282 0.872743 5.57335 0.166748 7.00065 0.166748C8.42796 0.166748 9.71849 0.872743 10.7662 1.73056C11.8196 2.59306 12.6809 3.65251 13.2597 4.45876L13.3075 4.52514C13.6559 5.00931 13.9427 5.40785 13.9427 6.00008C13.9427 6.59231 13.6559 6.99086 13.3074 7.47503L13.2597 7.54141C12.6809 8.34765 11.8196 9.40711 10.7662 10.2696C9.71849 11.1274 8.42796 11.8334 7.00065 11.8334C5.57335 11.8334 4.28282 11.1274 3.23514 10.2696C2.18174 9.40711 1.3204 8.34765 0.741596 7.54141L0.69386 7.47502C0.345419 6.99086 0.0585938 6.59231 0.0585938 6.00008C0.0585938 5.40785 0.345419 5.00931 0.69386 4.52514L0.741595 4.45876C1.3204 3.65251 2.18174 2.59306 3.23514 1.73056ZM7.00065 3.50008C5.61994 3.50008 4.50065 4.61937 4.50065 6.00008C4.50065 7.38079 5.61994 8.50008 7.00065 8.50008C8.38136 8.50008 9.50065 7.38079 9.50065 6.00008C9.50065 4.61937 8.38136 3.50008 7.00065 3.50008Z"
                                                    fill="#2D264B" />
                                            </svg>
                                        </button>
                                        <button class="copy_link" data-bs-toggle="tooltip" data-bs-placement="bottom"
                                            title="Copy Link" data-link="{{ $item->affiliate_link ?? 'null'}}">
                                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path
                                                    d="M11.7881 9.55804V12.6528C11.7881 15.2317 10.7593 16.2633 8.18725 16.2633H5.10083C2.52881 16.2633 1.5 15.2317 1.5 12.6528V9.55804C1.5 6.97909 2.52881 5.94751 5.10083 5.94751H8.18725C10.7593 5.94751 11.7881 6.97909 11.7881 9.55804Z"
                                                    fill="white" />
                                                <path
                                                    d="M12.5979 1.52637H9.51145C7.35078 1.52637 6.28429 2.25956 5.9981 4.01785C5.90863 4.56752 6.37294 5.02637 6.92984 5.02637H8.1887C11.2751 5.02637 12.7081 6.46321 12.7081 9.55795V10.8229C12.7081 11.3798 13.167 11.8442 13.7166 11.754C15.4682 11.4664 16.1987 10.397 16.1987 8.23163V5.13689C16.1987 2.55795 15.1699 1.52637 12.5979 1.52637Z"
                                                    fill="white" />
                                            </svg>

                                        </button>

                                        <div class="modal cs_modal fade admin-query" id="affiliate_view_link_modal">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">
                                                            {{ __('affiliate.Affiliate Link') }}
                                                        </h4>
                                                        <button type="button" class="close " data-bs-dismiss="modal">
                                                            <i class="ti-close "></i>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="m-0 text-wrap text-start">
                                                            {{ $item->affiliate_link ?? 'null'}}
                                                        </p>


                                                        <button
                                                            class="theme_btn copy_link_on_modal theme_btn affiliate_buttons copy_link">
                                                            {{ __('communication.Copy Link') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $item->visits }}</td>
                            <td>{{ $item->registerUser->count() }}</td>
                            <td>{{ $item->payment->count() }}</td>
                            <td>{{ showPrice($item->payment->sum('amount')) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{-- {{ $data->links() }} --}}
        </div>
    </div>
</div>
