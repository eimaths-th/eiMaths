<div role="tabpanel" class="tab-pane fade show active" id="affiliate_link_tab">
    <div class="main-title">
        <h3 class="mb-20">{{__('affiliate.Affiliate Links')}}</h3>
    </div>
    <div class="QA_section QA_section_heading_custom check_box_table">
        <div class="QA_table ">
            <div class="">
                <table id="lms_table" class="table Crm_table_active3">
                    <thead>
                    <tr>
                        <th>{{__('affiliate.Affiliate Link')}}</th>
                        <th>{{__('affiliate.Visits')}}</th>
                        <th>{{__('affiliate.Registered')}}</th>
                        <th>{{__('affiliate.Purchased')}}</th>
                        <th>{{__('affiliate.Commissions')}}</th>

                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data as $item)
                        <tr>
                            <td>{{$item->affiliate_link}}</td>
                            <td>{{$item->visits}}</td>
                            <td>{{$item->registerUser->count()}}</td>
                            <td>{{$item->payment->count()}}</td>
                            <td>{{showPrice($item->payment->sum('amount'))}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
