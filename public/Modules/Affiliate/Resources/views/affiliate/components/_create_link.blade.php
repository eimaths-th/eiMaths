@php
    $user =\Illuminate\Support\Facades\Auth::user();
    if (empty($user->referral)){
        $user->referral =generateUniqueId();
        $user->save();
    }

@endphp
<div class="white-box">

    <div class="row">
        <div class="col-lg-12">
            <div class="main-title">
                <h3 class="mb-20">
                    @if(isset($row))
                        {{__('affiliate.Update Affiliate Link')}}
                    @else
                        {{__('affiliate.Create Affiliate Link')}}
                    @endif
                </h3>
            </div>
            @if(isset($row))
                {{ Form::open(['class' => 'form-horizontal', 'files' => true, 'route' => array('affiliate.my_affiliate.update',$row->id), 'method' => 'PUT']) }}
            @else

                {{ Form::open(['class' => 'form-horizontal', 'files' => true, 'route' => 'affiliate.my_affiliate.store',
                'method' => 'POST']) }}

            @endif
            <div>
                <div class="add-visitor">
                    <div class="row">
                        <div class="col-lg-12 mb-30 d-none">
                            <div class="input-effect">
                                <input readonly
                                       class="primary-input form-control{{ $errors->has('user_name') ? ' is-invalid' : '' }}"
                                       type="text" id="user_name" name="user_name" autocomplete="off"
                                       value="{{$user->referral}}">

                            </div>
                        </div>
                        <div class="col-lg-12 mb-30">
                            <div class="input-effect">
                                <label class="mb-2"> {{__('affiliate.Enter URL')}} <span
                                        class="required_mark_theme">*</span>
                                </label>
                                <input
                                    class="primary-input-field form-control{{ $errors->has('url') ? ' is-invalid' : '' }}"
                                    type="text" id="url" name="url" autocomplete="off"
                                    value="{{isset($row)? $row->url : old('url') }}">

                                <span class="focus-border"></span>
                                @if ($errors->has('url'))
                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('url') }}</strong>
                                                     </span>
                                @endif
                            </div>
                        </div>

                        <div class="col-lg-12 mb-30">
                            <div class="input-effect">
                                <label class="mb-2"> {{__('affiliate.Affiliate Link')}}</label>

                                <input readonly
                                       class="primary-input-field form-control{{ $errors->has('affiliate_link') ? ' is-invalid' : '' }}"
                                       id="affiliate_link" type="text" name="affiliate_link" autocomplete="off"
                                       value="{{isset($row)? $row->affiliate_link : old('affiliate_link') }}">
                                <span class="focus-border"></span>
                                @if ($errors->has('affiliate_link'))
                                    <span class="invalid-feedback" role="alert">
                                                        <strong>{{ $errors->first('affiliate_link') }}</strong>
                                                     </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row  ">
                        <div class="col-lg-12 text-center">
                            <button type="submit" class="primary-btn fix-gr-bg submit">

                                <i class="ti-check"></i>
                                @if(isset($row))
                                    {{__('common.Update')}}
                                @else
                                    {{__('common.Save')}}
                                @endif
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
