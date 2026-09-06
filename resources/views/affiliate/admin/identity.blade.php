@extends('layouts.app')

@section('content')
<main class="container py-4" aria-labelledby="affiliate-identity-review-title">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h1 id="affiliate-identity-review-title">Affiliate identity review</h1>
            <p class="text-muted mb-0">{{ $profile->User?->name }} · {{ $profile->User?->email }} · {{ $profile->referral_code }}</p>
        </div>
        <a href="{{ route('affiliate.admin.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    @if (!$identity)
        <div class="alert alert-info">This employee Affiliate account does not require external applicant identity verification.</div>
    @else
        <div class="alert alert-warning">Personal data is confidential. Use it only to review this application.</div>
        <div class="card mb-4"><div class="card-body">
            <h2 class="h4">Personal information</h2>
            <dl class="row mb-0">
                <dt class="col-sm-4">Status</dt><dd class="col-sm-8">{{ $identity->status->value }}</dd>
                <dt class="col-sm-4">Legal name</dt><dd class="col-sm-8">{{ __('affiliate.identity.' . $identity->honorific) }} {{ $identity->legal_first_name }} {{ $identity->legal_last_name }}</dd>
                <dt class="col-sm-4">National ID</dt><dd class="col-sm-8"><code>{{ $identity->national_id }}</code></dd>
                <dt class="col-sm-4">Card expires</dt><dd class="col-sm-8">{{ $identity->card_expires_on?->format('Y-m-d') }}</dd>
                <dt class="col-sm-4">Address</dt><dd class="col-sm-8">{{ $identity->address_line1 }} {{ $identity->subdistrict }} {{ $identity->district }} {{ $identity->province }} {{ $identity->postal_code }}</dd>
                <dt class="col-sm-4">Submitted</dt><dd class="col-sm-8">{{ $identity->submitted_at }}</dd>
            </dl>
        </div></div>
        <div class="card mb-4"><div class="card-body">
            <h2 class="h4">Identity document</h2>
            @if ($identity->id_document_path)
                <a href="{{ route('affiliate.admin.identity.document', $profile) }}" target="_blank" rel="noopener">
                    <img src="{{ route('affiliate.admin.identity.document', $profile) }}" alt="Submitted identity document" style="max-width: 100%; max-height: 560px">
                </a>
            @else
                <p>No document submitted.</p>
            @endif
        </div></div>
        <div class="card mb-4"><div class="card-body">
            <h2 class="h4">Consent record</h2>
            <p>Terms {{ $identity->terms_version }} accepted {{ $identity->terms_accepted_at }}</p>
            <p>Privacy notice {{ $identity->privacy_version }} acknowledged {{ $identity->privacy_acknowledged_at }}</p>
            <p>Marketing: {{ $identity->marketing_consent ? 'consented' : 'not consented' }}</p>
        </div></div>

        @if ($profile->status->value === 'PENDING_APPROVAL' && $identity->status->value === 'PENDING')
            <div class="d-flex flex-wrap gap-3">
                <form method="post" action="{{ route('affiliate.admin.approve', $profile) }}">@csrf<button class="btn btn-success" type="submit">Approve Affiliate</button></form>
                <form method="post" action="{{ route('affiliate.admin.identity.reject', $profile) }}" class="d-flex gap-2 flex-grow-1">@csrf
                    <label class="sr-only" for="rejection_reason">Reason</label>
                    <input id="rejection_reason" class="form-control" name="rejection_reason" maxlength="500" placeholder="Reason and requested correction" required>
                    <button class="btn btn-danger" type="submit">Return for correction</button>
                </form>
            </div>
        @endif
    @endif
</main>
@endsection
