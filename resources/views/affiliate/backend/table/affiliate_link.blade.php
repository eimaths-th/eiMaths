<div role="tabpanel" class="tab-pane fade show active" id="affiliate_link_tab">
    <div class="main-title">
        <h3 class="mb-20">Affiliate Links</h3>
    </div>
    <div class="QA_section QA_section_heading_custom check_box_table">
        <div class="QA_table ">
            <div class="">
                <table id="lms_table" class="table Crm_table_active3">
                    <thead>
                        <tr>
                            <th>Affiliate Link</th>
                            {{-- <th>Visits</th> --}}
                            {{-- <th>Registered</th> --}}
                            {{-- <th>Purchased</th>
                            <th>Commissions</th> --}}

                        </tr>
                    </thead>
                    <tbody>
                        {{-- @foreach ($data as $item)
                            <tr>
                                <td>{{ $item->affiliate_link }}</td>
                                <td>{{ $item->visits }}</td>
                                <td>{{ $item->registerUser->count() }}</td>
                                <td>{{ $item->payment->count() }}</td>
                                <td>{{ showPrice($item->payment->sum('amount')) }}
                                </td>
                            </tr>
                        @endforeach --}}
                        @foreach ($data['data'] as $item)
                            <tr>
                                <td>
                                    <a target="_blank" href="{{ $item->affiliate_link }}">{{ $item->affiliate_link }}</a>
                                    {{-- {{ $item->affiliate_link }} --}}
                                </td>
                                {{-- <td>{{ $item->visits }}</td> --}}
                                {{-- <td>{{ $item->registerUser->count() }}</td> --}}
                                {{-- <td>{{ $item->payment->count() }}</td>
                                <td>{{ showPrice($item->payment->sum('amount')) }}
                                </td> --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
