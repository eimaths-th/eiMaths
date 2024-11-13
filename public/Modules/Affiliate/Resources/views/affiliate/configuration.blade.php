@extends('backend.master')
@push('styles')
    <style>
        .mtr-15 {
            margin-top: -15px;
        }
    </style>
@endpush
@section('mainContent')

    {!! generateBreadcrumb() !!}

    <section class="admin-visitor-area up_st_admin_visitor">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="white-box">
                                <div class="add-visitor">
                                    <div class="main-title">
                                        <h3 class="mb-20">
                                            {{__('affiliate.Configurations')}}
                                        </h3>
                                    </div>

                                    {{ Form::open(['class' => 'form-horizontal', 'files' => true, 'route' => 'affiliate.configurations.update',
                                        'method' => 'POST', 'enctype' => 'multipart/form-data']) }}
                                    <div class="row">
                                        <div class="col-lg-3 mb-10">
                                            <div class="input-effect">
                                                <label class="mb-2">{{__('affiliate.Minimum withdraw amount')}}
                                                    ({{Settings('currency_symbol')}})
                                                    <span
                                                        class="required_mark_theme">*</span> </label>
                                                <input step="0.01"
                                                       class="primary-input-field form-control{{ $errors->has('min_withdraw') ? ' is-invalid' : '' }}"
                                                       type="number" name="min_withdraw" autocomplete="off"
                                                       value="{{affiliateConfig('min_withdraw')}}">


                                            </div>
                                        </div>


                                        <div class="col-lg-3 mb-10">
                                            <div class="input-effect">
                                                <label
                                                    class="mb-2">{{__('affiliate.Earning will add in account after')}}
                                                    [{{__('affiliate.In Days')}}] <span
                                                        class="required_mark_theme">*</span></label>

                                                <input
                                                    class="primary-input-field form-control{{ $errors->has('balance_add_account_after_days') ? ' is-invalid' : '' }}"
                                                    type="number" name="balance_add_account_after_days"
                                                    autocomplete="off"
                                                    value="{{affiliateConfig('balance_add_account_after_days')}}">

                                            </div>
                                        </div>
                                        <div class="col-lg-3 mb-10 mtr-15">
                                            <div class="input-effect">
                                                <label class="primary_input_label"
                                                       for="">{{__('affiliate.Balance transfer admin approval required?')}}
                                                    <span
                                                        class="required_mark_theme">*</span></label>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <label for="transfer_approval_1"
                                                               class="primary_checkbox d-flex mr-12">
                                                            <input
                                                                {{affiliateConfig('transfer_approval_need') == 1? 'Checked' :''}} type="radio"
                                                                id="transfer_approval_1"
                                                                class="transfer_approval common-radio {{ $errors->has('transfer_approval_need') ? ' is-invalid' : '' }}"
                                                                name="transfer_approval_need" value="1">
                                                            <span class="checkmark me-2"></span>
                                                            {{__('affiliate.Yes')}}</label>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label for="transfer_approval_2"
                                                               class="primary_checkbox d-flex mr-12">
                                                            <input
                                                                {{affiliateConfig('transfer_approval_need') == 0? 'Checked' :''}} type="radio"
                                                                id="transfer_approval_2"
                                                                class="transfer_approval common-radio {{ $errors->has('transfer_approval_need') ? ' is-invalid' : '' }}"
                                                                name="transfer_approval_need" value="0">
                                                            <span class="checkmark me-2"></span>
                                                            {{__('affiliate.No')}}</label>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>


                                        <div class="col-lg-3 mb-10 mtr-15">
                                            <div class="input-effect">
                                                <label class="primary_input_label"
                                                       for="">{{__('affiliate.Affiliate user approval required?')}}
                                                    <span
                                                        class="required_mark_theme">*</span></label>

                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <label class="primary_checkbox d-flex mr-12"
                                                               for="admin_approval_need1">
                                                            <input
                                                                {{affiliateConfig('admin_approval_need') == 1? 'Checked' :''}} type="radio"
                                                                id="admin_approval_need1"
                                                                class="transfer_approval common-checkbox form-control{{ $errors->has('transfer_approval_need') ? ' is-invalid' : '' }}"
                                                                name="admin_approval_need" value="1">
                                                            <span class="checkmark me-2"></span> {{__('affiliate.Yes')}}
                                                        </label>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="primary_checkbox d-flex mr-12"
                                                               for="admin_approval_need2">
                                                            <input
                                                                {{affiliateConfig('admin_approval_need') == 0? 'Checked' :''}} type="radio"
                                                                id="admin_approval_need2"
                                                                class="transfer_approval common-checkbox form-control{{ $errors->has('transfer_approval_need') ? ' is-invalid' : '' }}"
                                                                name="admin_approval_need" value="0">

                                                            <span class="checkmark me-2"></span>
                                                            {{__('affiliate.No')}}</label>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>


                                    <div class="row mt-20  mb-20">
                                        <div class="col-lg-4 mb-10 mtr-15">
                                            <div class="input-effect">
                                                <label class="primary_input_label"
                                                       for="">{{__('affiliate.Commission Type')}} <span
                                                        class="required_mark_theme">*</span></label>

                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <li>
                                                            <label class="primary_checkbox d-flex mr-12"
                                                                   for="commission_type_1">
                                                                <input
                                                                    {{affiliateConfig('commission_type') == 'Percentage'? 'Checked' :''}} type="radio"
                                                                    id="commission_type_1"
                                                                    class="commission_type common-checkbox form-control{{ $errors->has('commission_type') ? ' is-invalid' : '' }}"
                                                                    name="commission_type" value="Percentage">
                                                                <span
                                                                    class="checkmark me-2"></span> {{__('affiliate.Percentage')}}
                                                            </label>
                                                        </li>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <li>
                                                            <label class="primary_checkbox d-flex mr-12"
                                                                   for="commission_type_2">
                                                                <input
                                                                    {{affiliateConfig('commission_type') == 'Flat'? 'Checked' :''}}  type="radio"
                                                                    id="commission_type_2"
                                                                    class="commission_type common-checkbox form-control{{ $errors->has('commission_type') ? ' is-invalid' : '' }}"
                                                                    name="commission_type" value="Flat">
                                                                <span
                                                                    class="checkmark me-2"></span> {{__('affiliate.Flat')}}
                                                            </label>
                                                        </li>
                                                    </div>
                                                </div>


                                            </div>
                                        </div>

                                        <div class="col-lg-2 mb-10">
                                            <div class="input-effect">
                                                <label class="mb-2">
                                                        <span
                                                            class="commissionTypePercentage {{affiliateConfig('commission_type') == 'Percentage'?'':'d-none'}}">
                                                            {{__('affiliate.Commission Percentage')}}
                                                        </span>

                                                    <span
                                                        class=" commissionTypeAmount {{affiliateConfig('commission_type') == 'Percentage'?'d-none':''}}">
                                                            {{__('affiliate.Commission Amount')}}
                                                        </span>

                                                    <span
                                                        class="required_mark_theme">*</span> </label>
                                                <input
                                                    class="primary-input-field form-control{{ $errors->has('commission_amount') ? ' is-invalid' : '' }}"
                                                    type="number" name="commission_amount" autocomplete="off"
                                                    value="{{affiliateConfig('commission_amount')}}">

                                            </div>
                                        </div>
                                    </div>


                                    <div class="row mb-20">
                                        <div class="col-lg-4 mb-10 mtr-15">
                                            <div class="input-effect">
                                                <label class="primary_input_label"
                                                       for="">{{__('affiliate.Referral Duration Type')}} <span
                                                        class="required_mark_theme">*</span></label>
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <label class="primary_checkbox d-flex mr-12"
                                                               for="referral_duration_type_3">
                                                            <input
                                                                {{affiliateConfig('referral_duration_type') == 'Onetime'? 'Checked' :''}} type="radio"
                                                                id="referral_duration_type_3"
                                                                class="referral_duration_type common-checkbox form-control{{ $errors->has('referral_duration_type') ? ' is-invalid' : '' }}"
                                                                name="referral_duration_type" value="Onetime">
                                                            <span
                                                                class="checkmark me-2"></span> {{__('affiliate.Onetime')}}
                                                        </label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <label class="primary_checkbox d-flex mr-12 text-nowrap"
                                                               for="referral_duration_type_2">
                                                            <input
                                                                {{affiliateConfig('referral_duration_type') == 'Lifetime'? 'Checked' :''}}  type="radio"
                                                                id="referral_duration_type_2"
                                                                class="referral_duration_type  common-checkbox form-control{{ $errors->has('referral_duration_type') ? ' is-invalid' : '' }}"
                                                                name="referral_duration_type" value="Lifetime">
                                                            <span
                                                                class="checkmark me-2"></span> {{__('affiliate.Lifetime')}}
                                                            [{{__('affiliate.All Purchase')}}]</label>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="primary_checkbox d-flex mr-12"
                                                               for="referral_duration_type_1">
                                                            <input
                                                                {{affiliateConfig('referral_duration_type') == 'Fixed'? 'Checked' :''}} type="radio"
                                                                id="referral_duration_type_1"
                                                                class="referral_duration_type common-checkbox form-control{{ $errors->has('referral_duration_type') ? ' is-invalid' : '' }}"
                                                                name="referral_duration_type" value="Fixed">
                                                            <span
                                                                class="checkmark me-2"></span> {{__('affiliate.Fixed')}}
                                                        </label>
                                                    </div>
                                                </div>

 
                                            </div>
                                        </div>

                                        <div class="col-lg-2 mb-10 referral_duration_div">
                                            <div class="input-effect">
                                                <label class="mb-2">{{__('affiliate.Referral Duration')}} [In Days]
                                                    <span
                                                        class="required_mark_theme">*</span> </label>

                                                <input
                                                    class="primary-input-field form-control{{ $errors->has('referral_duration') ? ' is-invalid' : '' }}"
                                                    type="number" name="referral_duration" autocomplete="off"
                                                    value="{{affiliateConfig('referral_duration')}}">

                                            </div>
                                        </div>
                                    </div>


                                    <div class="row mt-20 mb-20">
                                        <div class="col-lg-4 mb-10 mtr-15">
                                            <div class="input-effect">
                                                <label class="primary_input_label"
                                                       for="">{{__('affiliate.Communication approval system')}} <span
                                                        class="required_mark_theme">*</span></label>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <label class="primary_checkbox d-flex mr-12 text-nowrap"
                                                               for="communication_approval_system1">
                                                            <input
                                                                {{affiliateConfig('communication_approval_system') == 'cron'? 'Checked' :''}} type="radio"

                                                                id="communication_approval_system1"
                                                                class="communication_approval_system common-checkbox form-control{{ $errors->has('communication_approval_system') ? ' is-invalid' : '' }}"
                                                                name="communication_approval_system" value="cron">
                                                            <span
                                                                class="checkmark me-2"></span> {{__('affiliate.Cron Job')}}
                                                        </label>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <label class="primary_checkbox d-flex mr-12"
                                                               for="communication_approval_system2"
                                                        >
                                                            <input
                                                                {{affiliateConfig('communication_approval_system') == 'cron'? '' :'Checked'}} type="radio"
                                                                id="communication_approval_system2"
                                                                class="communication_approval_system common-checkbox form-control{{ $errors->has('communication_approval_system') ? ' is-invalid' : '' }}"
                                                                name="communication_approval_system" value="manually">
                                                            <span
                                                                class="checkmark me-2"></span> {{__('affiliate.Manually')}}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-8 mb-10 mtr-15">
                                            <div
                                                class="cronjobDiv  {{affiliateConfig('communication_approval_system') == 'cron'?'':'d-none'}}">
                                                <div class="tab-pane fade white-box active show " id=""
                                                     role="tabpanel"
                                                     aria-labelledby="tab">

                                                    <input type="hidden" name="g_set" value="1">

                                                    <div class="General_system_wrap_area d-block">
                                                        <div class="single_system_wrap">
                                                            <h5>{{__('setting.To run cron jobs you should set this path in cPanel Cron Command field for email and Due Date Reminder')}}
                                                                .</h5>
                                                            <div class="single_system_wrap_inner text-center">

                                                                <p>{{ 'cd ' . base_path() . '/ && php artisan affiliate:commission >> /dev/null 2>&1' }}</p>

                                                            </div>
                                                            <h6>{{__('setting.In cPanel you should set time interval Once Per day')}}
                                                                (0 0 * * *).</h6>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12 text-center">
                                            <button type="submit" class="primary-btn fix-gr-bg submit">
                                                <i class="ti-check"></i>
                                                {{__('common.Update')}}
                                            </button>
                                        </div>
                                    </div>
                                    {{ Form::close() }}

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('scripts')
    <script src="{{asset('Modules/Affiliate/Resources/assets/js')}}/config.js"></script>
@endpush

