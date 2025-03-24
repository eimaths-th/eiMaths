<div class="white-box">

    <div class="row">
        <div class="col-lg-12">
            <div class="row d-flex justify-content-between mb-5">
                <div>
                    <h3>
                        Your Referral Code
                    </h3>
                </div>
                <div class="row">
                    <div class="col-lg-8">
                        <input type="text" class="form-control" value="{{ $user->referral }}" id="referralCopy" readonly>
                    </div>
                    <div class="col-lg-4">
                        <button id="copyBtn" class="primary-btn small fix-gr-bg" type="button"
                            onclick="copyToClipboard('#referralCopy')">
                            <i class="fa fa-copy"></i> Copy
                        </button>
                    </div>
                </div>
            </div>
            {{-- <div class="main-title">
                <h3 class="mb-20">
                    Create Affiliate Link
                </h3>
            </div> --}}

            {{-- <form method="POST" action="{{ route('my_affiliate.index') }}"
                accept-charset="UTF-8" class="form-horizontal"
                enctype="multipart/form-data"><input name="_token" type="hidden"
                    value="{{ csrf_token() }}">

                <div>
                    <div class="add-visitor">
                        <div class="row">
                            <div class="col-lg-12 mb-30 d-none">
                                <div class="input-effect">
                                    <input readonly class="primary-input form-control"
                                        type="text" id="user_name" name="user_name"
                                        autocomplete="off" value="GVhBeu96Mq">

                                </div>
                            </div>
                            <div class="col-lg-12 mb-30">
                                <div class="input-effect">
                                    <label class="mb-2"> Enter URL <span
                                            class="required_mark_theme">*</span>
                                    </label>
                                    <input class="primary-input-field form-control"
                                        type="text" id="url" name="url"
                                        autocomplete="off" value="">

                                    <span class="focus-border"></span>
                                </div>
                            </div>

                            <div class="col-lg-12 mb-30">
                                <div class="input-effect">
                                    <label class="mb-2"> Affiliate Link</label>

                                    <input readonly
                                        class="primary-input-field form-control"
                                        id="affiliate_link" type="text"
                                        name="affiliate_link" autocomplete="off"
                                        value="">
                                    <span class="focus-border"></span>
                                </div>
                            </div>
                        </div>

                        <div class="row  ">
                            <div class="col-lg-12 text-center">
                                <button type="submit"
                                    class="primary-btn fix-gr-bg submit">

                                    <i class="ti-check"></i>
                                    Save
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form> --}}

            {{-- @if (isset($row))
                {{ Form::open([
                    'class' => 'form-horizontal',
                    'files' => true,
                    // 'route' => ['my_affiliate.index', $row->id],
                    'route' => ['my_affiliate.update', $row->id],
                    'method' => 'PUT',
                ]) }}
            @else
                {{ Form::open([
                    'class' => 'form-horizontal',
                    'files' => true,
                    'route' => 'my_affiliate.store',
                    // 'route' => 'my_affiliate.index',
                    'method' => 'POST',
                ]) }}
            @endif
            <div>
                <div class="add-visitor">
                    <div class="row">
                        <div class="col-lg-12 mb-30 d-none">
                            <div class="input-effect">
                                <input readonly
                                    class="primary-input form-control{{ $errors->has('user_name') ? ' is-invalid' : '' }}"
                                    type="text" id="user_name" name="user_name" autocomplete="off"
                                    value="{{ $user->referral }}">

                            </div>
                        </div>
                        <div class="col-lg-12 mb-30">
                            <div class="input-effect">
                                <label class="mb-2"> Enter URL
                                    <span class="required_mark_theme">*</span>
                                </label>
                                <input
                                    class="primary-input-field form-control{{ $errors->has('url') ? ' is-invalid' : '' }}"
                                    type="text" id="url" name="url" autocomplete="off"
                                    value="{{ isset($row) ? $row->url : old('url') }}">

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
                                <label class="mb-2">Affiliate Link</label>

                                <input readonly
                                    class="primary-input-field form-control{{ $errors->has('affiliate_link') ? ' is-invalid' : '' }}"
                                    id="affiliate_link" type="text" name="affiliate_link" autocomplete="off"
                                    value="{{ isset($row) ? $row->affiliate_link : old('affiliate_link') }}">
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
                                @if (isset($row))
                                    {{ __('common.Update') }}
                                    Update
                                @else
                                    Save
                                @endif
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            {{ Form::close() }} --}}


        </div>
    </div>
</div>
