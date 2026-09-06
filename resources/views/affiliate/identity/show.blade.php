@extends('affiliate.auth.layouts.app')
@section('title', __('affiliate.identity.title'))
@section('content')
<main class="container py-5" style="max-width: 880px">
    <section class="bg-white p-4 p-md-5 rounded shadow-sm">
        <h1 class="h2">{{ __('affiliate.identity.title') }}</h1>
        <p>{{ __('affiliate.identity.intro') }}</p>

        @if ($identity?->rejection_reason)
            <div class="alert alert-danger" role="alert">
                <strong>{{ __('affiliate.identity.rejection_title') }}</strong><br>{{ $identity->rejection_reason }}
            </div>
        @endif

        <form method="POST" action="{{ route('affiliate.identity.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="honorific">{{ __('affiliate.identity.honorific') }} *</label>
                    <select id="honorific" name="honorific" class="form-control" required>
                        <option value="">{{ __('affiliate.identity.select') }}</option>
                        @foreach (['mr' => __('affiliate.identity.mr'), 'mrs' => __('affiliate.identity.mrs'), 'miss' => __('affiliate.identity.miss')] as $value => $label)
                            <option value="{{ $value }}" @selected(old('honorific', $identity?->honorific) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('honorific')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="legal_first_name">{{ __('affiliate.identity.first_name') }} *</label>
                    <input id="legal_first_name" name="legal_first_name" class="form-control" value="{{ old('legal_first_name', $identity?->legal_first_name) }}" required>
                    @error('legal_first_name')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-5 mb-3">
                    <label for="legal_last_name">{{ __('affiliate.identity.last_name') }} *</label>
                    <input id="legal_last_name" name="legal_last_name" class="form-control" value="{{ old('legal_last_name', $identity?->legal_last_name) }}" required>
                    @error('legal_last_name')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-7 mb-3">
                    <label for="national_id">{{ __('affiliate.identity.national_id') }} *</label>
                    <input id="national_id" name="national_id" class="form-control" value="{{ old('national_id', $identity?->national_id) }}" maxlength="17" inputmode="numeric" autocomplete="off" required>
                    @error('national_id')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-5 mb-3">
                    <label for="card_expires_on">{{ __('affiliate.identity.card_expires_on') }} *</label>
                    <input id="card_expires_on" type="date" name="card_expires_on" class="form-control" value="{{ old('card_expires_on', $identity?->card_expires_on?->format('Y-m-d')) }}" required>
                    @error('card_expires_on')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 mb-3">
                    <label for="address_line1">{{ __('affiliate.identity.address_line1') }} *</label>
                    <input id="address_line1" name="address_line1" class="form-control" value="{{ old('address_line1', $identity?->address_line1) }}" required>
                    @error('address_line1')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
                @foreach (['subdistrict', 'district', 'province'] as $field)
                    <div class="col-md-4 mb-3">
                        <label for="{{ $field }}">{{ __('affiliate.identity.' . $field) }} *</label>
                        <input id="{{ $field }}" name="{{ $field }}" class="form-control" value="{{ old($field, $identity?->{$field}) }}" required>
                        @error($field)<div class="text-danger">{{ $message }}</div>@enderror
                    </div>
                @endforeach
                <div class="col-md-4 mb-3">
                    <label for="postal_code">{{ __('affiliate.identity.postal_code') }} *</label>
                    <input id="postal_code" name="postal_code" class="form-control" value="{{ old('postal_code', $identity?->postal_code) }}" maxlength="5" inputmode="numeric" required>
                    @error('postal_code')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 mb-3">
                    <label for="id_document">{{ __('affiliate.identity.document') }} *</label>
                    <input id="id_document" type="file" name="id_document" class="form-control" accept="image/jpeg,image/png" {{ $identity?->id_document_path ? '' : 'required' }}>
                    <small class="form-text text-muted">{{ __('affiliate.identity.document_help') }}</small>
                    @if ($identity?->id_document_path)<div><a href="{{ route('affiliate.identity.document') }}" target="_blank" rel="noopener">{{ __('affiliate.identity.current_document') }}</a></div>@endif
                    @error('id_document')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 mb-4">
                    <label><input type="checkbox" name="identity_certified" value="1" required> {{ __('affiliate.identity.certify') }}</label>
                    @error('identity_certified')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
            </div>
            <button class="btn theme_btn" type="submit">{{ __('affiliate.identity.submit') }}</button>
        </form>
    </section>
</main>
@endsection
