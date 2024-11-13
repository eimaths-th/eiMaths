<div class="modal cs_modal fade admin-query" id="create_affiliate_link_modal">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">{{__('affiliate.Create Affiliate Link')}}</h4>
                <button type="button" class="close " data-bs-dismiss="modal">
                    <i class="ti-close "></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-20">
                    <div class="col-lg-12">
                        @if(isset($row))
                            {{ Form::open(['class' => 'form-horizontal', 'files' => true, 'route' => array('affiliate.my_affiliate.update',$row->id), 'method' => 'PUT']) }}
                        @else
                            {{ Form::open(['class' => 'form-horizontal', 'files' => true, 'route' => 'affiliate.my_affiliate.store',
                            'method' => 'POST']) }}
                        @endif

                        <div class="account_profile_form">
                            <!-- <div class="account_title">
                                <h3 class="font_22 f_w_700 mb-4"> {{__('affiliate.Create Affiliate Link')}}</h3>
                            </div> -->
                            <div class="row ">
                                <div class="col-lg-12 d-none">
                                    <label class="primary_label2" for="user_name">{{__('affiliate.Email')}}</label>
                                    <input readonly name="user_name" id="user_name" value="{{$user->referral}}"
                                           class="primary_input mb_20 {{ $errors->has('user_name') ? ' is-invalid' : '' }}"
                                           type="text">
                                    @if ($errors->has('user_name'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('user_name') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="col-lg-12">
                                    <label class="primary_label2" for="url">{{__('affiliate.Enter URL')}}</label>
                                    <input name="url" id="url" value="{{isset($row)? $row->url : old('url') }}"
                                           class="primary_input mb_20 {{ $errors->has('url') ? ' is-invalid' : '' }}"
                                           autocomplete="off" type="text">
                                    @if ($errors->has('url'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('url') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="col-lg-12">
                                    <label class="primary_label2"
                                           for="user_name">{{__('affiliate.Affiliate Link')}}</label>
                                    <input autocomplete="off" readonly name="affiliate_link" id="affiliate_link"
                                           value="{{$user->affiliate_link}}"
                                           class="primary_input mb_20 {{ $errors->has('affiliate_link') ? ' is-invalid' : '' }}"
                                           type="text">
                                    @if ($errors->has('affiliate_link'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('affiliate_link') }}</strong>
                                        </span>
                                    @endif
                                </div>


                                <div class="col-12">

                                    <button class="theme_btn w-100 text-center">
                                        @if(isset($row))
                                            {{__('common.Update')}}
                                        @else
                                            {{__('common.Save')}}
                                        @endif
                                    </button>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
