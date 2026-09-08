@extends('layouts.app')

@section('style')
<style>
    .identity-review-page {
        color: #25324b;
    }

    .identity-review-page .page-heading {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: .35rem;
    }

    .identity-review-page .review-card {
        border: 1px solid #e1e7f0;
        border-radius: .65rem;
        box-shadow: 0 2px 8px rgba(37, 50, 75, .06);
        overflow: hidden;
    }

    .identity-review-page .review-card-header {
        align-items: center;
        background: #f7f9fc;
        border-bottom: 1px solid #e1e7f0;
        display: flex;
        min-height: 58px;
        padding: .9rem 1.15rem;
    }

    .identity-review-page .review-card-title {
        font-size: 1.05rem;
        font-weight: 700;
        margin: 0;
    }

    .identity-review-page .review-card-title i {
        color: #1976d2;
        margin-right: .55rem;
        width: 1.15rem;
    }

    .identity-review-page .detail-list {
        margin-bottom: 0;
    }

    .identity-review-page .detail-row {
        border-bottom: 1px solid #edf0f5;
        margin: 0;
        padding: .7rem 0;
    }

    .identity-review-page .detail-row:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .identity-review-page .detail-row:first-child {
        padding-top: 0;
    }

    .identity-review-page .detail-label {
        color: #68758b;
        font-size: .875rem;
        font-weight: 600;
        margin-bottom: .2rem;
    }

    .identity-review-page .detail-value {
        color: #202b3c;
        margin-bottom: 0;
        overflow-wrap: anywhere;
    }

    .identity-review-page .status-summary {
        background: #fff;
        border: 1px solid #e1e7f0;
        border-radius: .65rem;
        padding: .85rem 1rem;
    }

    .identity-review-page .status-item {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        margin: .2rem 1.5rem .2rem 0;
    }

    .identity-review-page .status-item-label {
        color: #68758b;
        font-size: .85rem;
        margin-right: .45rem;
    }

    .identity-review-page .document-preview {
        align-items: center;
        background: #f4f6fa;
        border: 1px solid #e1e7f0;
        border-radius: .5rem;
        display: flex;
        justify-content: center;
        min-height: 220px;
        overflow: hidden;
        padding: .75rem;
    }

    .identity-review-page .document-preview img {
        display: block;
        max-height: 430px;
        max-width: 100%;
        object-fit: contain;
    }

    .identity-review-page .decision-card {
        border-top: 4px solid #1976d2;
    }

    .identity-review-page .decision-option {
        background: #f8fafc;
        border: 1px solid #dfe5ee;
        border-radius: .55rem;
        height: 100%;
        padding: 1.1rem;
    }

    .identity-review-page .decision-option h3 {
        font-size: 1rem;
        font-weight: 700;
    }

    .identity-review-page .decision-option p {
        color: #68758b;
        font-size: .9rem;
    }

    .identity-review-page .decision-option textarea {
        min-height: 108px;
        resize: vertical;
    }

    .identity-review-page .empty-state {
        color: #68758b;
        padding: 2.5rem 1rem;
        text-align: center;
    }

    @media (max-width: 767.98px) {
        .identity-review-page .page-heading {
            font-size: 1.4rem;
        }

        .identity-review-page .page-actions {
            margin-top: 1rem;
            width: 100%;
        }

        .identity-review-page .page-actions .btn {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
<main class="container-fluid py-4 identity-review-page" aria-labelledby="affiliate-identity-review-title">
    <div class="row align-items-start mb-3">
        <div class="col-md">
            <h1 id="affiliate-identity-review-title" class="page-heading">Affiliate identity review</h1>
            <p class="text-muted mb-0">Review the applicant information, identity document, and consent record before making a decision.</p>
        </div>
        <div class="col-md-auto page-actions">
            <a href="{{ route('affiliate.admin.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left mr-1" aria-hidden="true"></i> Back to Affiliate Accounts
            </a>
        </div>
    </div>

    @if (!$identity)
        <div class="card review-card">
            <div class="card-body empty-state">
                <i class="fas fa-info-circle fa-2x mb-3" aria-hidden="true"></i>
                <h2 class="h5">Identity verification is not required</h2>
                <p class="mb-0">This employee Affiliate account does not require external applicant identity verification.</p>
            </div>
        </div>
    @else
        <div class="status-summary mb-3" aria-label="Application summary">
            <div class="d-flex flex-wrap align-items-center">
                <div class="status-item">
                    <span class="status-item-label">Applicant</span>
                    <strong>{{ $profile->User?->name ?: '—' }}</strong>
                </div>
                <div class="status-item">
                    <span class="status-item-label">Referral code</span>
                    <code>{{ $profile->referral_code }}</code>
                </div>
                <div class="status-item">
                    <span class="status-item-label">Affiliate status</span>
                    <span class="badge badge-warning">{{ $profile->status->value }}</span>
                </div>
                <div class="status-item mb-0">
                    <span class="status-item-label">Identity status</span>
                    <span class="badge {{ $identity->status->value === 'VERIFIED' ? 'badge-success' : ($identity->status->value === 'REJECTED' ? 'badge-danger' : 'badge-warning') }}">{{ $identity->status->value }}</span>
                </div>
            </div>
        </div>

        <div class="alert alert-warning d-flex align-items-start" role="alert">
            <i class="fas fa-shield-alt mt-1 mr-2" aria-hidden="true"></i>
            <div><strong>Confidential personal data.</strong> Use this information only to review this Affiliate application.</div>
        </div>

        <div class="row">
            <div class="col-lg-7 mb-4">
                <section class="card review-card h-100" aria-labelledby="applicant-information-title">
                    <div class="review-card-header">
                        <h2 id="applicant-information-title" class="review-card-title"><i class="fas fa-user" aria-hidden="true"></i>Applicant information</h2>
                    </div>
                    <div class="card-body">
                        <dl class="detail-list">
                            <div class="row detail-row">
                                <dt class="col-sm-4 detail-label">Account name</dt>
                                <dd class="col-sm-8 detail-value">{{ $profile->User?->name ?: '—' }}</dd>
                            </div>
                            <div class="row detail-row">
                                <dt class="col-sm-4 detail-label">Email</dt>
                                <dd class="col-sm-8 detail-value">{{ $profile->User?->email ?: '—' }}</dd>
                            </div>
                            <div class="row detail-row">
                                <dt class="col-sm-4 detail-label">Legal name</dt>
                                <dd class="col-sm-8 detail-value">{{ __('affiliate.identity.' . $identity->honorific) }} {{ $identity->legal_first_name }} {{ $identity->legal_last_name }}</dd>
                            </div>
                            <div class="row detail-row">
                                <dt class="col-sm-4 detail-label">National ID</dt>
                                <dd class="col-sm-8 detail-value"><code>{{ $identity->national_id }}</code></dd>
                            </div>
                            <div class="row detail-row">
                                <dt class="col-sm-4 detail-label">Card expiry date</dt>
                                <dd class="col-sm-8 detail-value">{{ $identity->card_expires_on?->format('d M Y') ?: '—' }}</dd>
                            </div>
                            <div class="row detail-row">
                                <dt class="col-sm-4 detail-label">Registered address</dt>
                                <dd class="col-sm-8 detail-value">
                                    {{ collect([$identity->address_line1, $identity->subdistrict, $identity->district, $identity->province, $identity->postal_code])->filter()->implode(', ') ?: '—' }}
                                </dd>
                            </div>
                            <div class="row detail-row">
                                <dt class="col-sm-4 detail-label">Submitted</dt>
                                <dd class="col-sm-8 detail-value">{{ $identity->submitted_at?->format('d M Y, H:i') ?: '—' }}</dd>
                            </div>
                        </dl>
                    </div>
                </section>
            </div>

            <div class="col-lg-5 mb-4">
                <section class="card review-card h-100" aria-labelledby="identity-document-title">
                    <div class="review-card-header">
                        <h2 id="identity-document-title" class="review-card-title"><i class="fas fa-id-card" aria-hidden="true"></i>Identity document</h2>
                    </div>
                    <div class="card-body d-flex flex-column">
                        @if ($identity->id_document_path)
                            <a class="document-preview mb-3" href="{{ route('affiliate.admin.identity.document', $profile) }}" target="_blank" rel="noopener" aria-label="Open submitted identity document in a new tab">
                                <img src="{{ route('affiliate.admin.identity.document', $profile) }}" alt="Submitted identity document">
                            </a>
                            <a class="btn btn-outline-primary mt-auto" href="{{ route('affiliate.admin.identity.document', $profile) }}" target="_blank" rel="noopener">
                                <i class="fas fa-external-link-alt mr-1" aria-hidden="true"></i> Open full document
                            </a>
                        @else
                            <div class="empty-state my-auto">
                                <i class="far fa-file-image fa-2x mb-3" aria-hidden="true"></i>
                                <p class="mb-0">No identity document was submitted.</p>
                            </div>
                        @endif
                    </div>
                </section>
            </div>
        </div>

        <section class="card review-card mb-4" aria-labelledby="consent-record-title">
            <div class="review-card-header">
                <h2 id="consent-record-title" class="review-card-title"><i class="fas fa-file-signature" aria-hidden="true"></i>Consent record</h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="detail-label">Terms of Service</div>
                        <div class="detail-value"><strong>Version {{ $identity->terms_version ?: '—' }}</strong></div>
                        <small class="text-muted">Accepted {{ $identity->terms_accepted_at?->format('d M Y, H:i') ?: '—' }}</small>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="detail-label">Privacy notice</div>
                        <div class="detail-value"><strong>Version {{ $identity->privacy_version ?: '—' }}</strong></div>
                        <small class="text-muted">Acknowledged {{ $identity->privacy_acknowledged_at?->format('d M Y, H:i') ?: '—' }}</small>
                    </div>
                    <div class="col-md-4">
                        <div class="detail-label">Marketing communication</div>
                        <div class="detail-value">
                            <span class="badge {{ $identity->marketing_consent ? 'badge-success' : 'badge-secondary' }}">
                                {{ $identity->marketing_consent ? 'Consented' : 'Not consented' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @if ($profile->status->value === 'PENDING_APPROVAL' && $identity->status->value === 'PENDING')
            <section class="card review-card decision-card mb-4" aria-labelledby="review-decision-title">
                <div class="review-card-header">
                    <h2 id="review-decision-title" class="review-card-title"><i class="fas fa-clipboard-check" aria-hidden="true"></i>Review decision</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        @can('affiliate-approve')
                            <div class="col-lg-5 mb-3 mb-lg-0">
                                <div class="decision-option">
                                    <h3><i class="fas fa-check-circle text-success mr-1" aria-hidden="true"></i> Approve application</h3>
                                    <p>Confirm that the applicant information and identity document are complete and correct.</p>
                                    <form method="post" action="{{ route('affiliate.admin.approve', $profile) }}">
                                        @csrf
                                        <button class="btn btn-success" type="submit">
                                            <i class="fas fa-check mr-1" aria-hidden="true"></i> Approve Affiliate
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endcan
                        <div class="{{ auth()->user()?->can('affiliate-approve') ? 'col-lg-7' : 'col-12' }}">
                            <div class="decision-option">
                                <h3><i class="fas fa-undo-alt text-danger mr-1" aria-hidden="true"></i> Return for correction</h3>
                                <p>Explain exactly what the applicant must correct before submitting again.</p>
                                <form method="post" action="{{ route('affiliate.admin.identity.reject', $profile) }}" novalidate>
                                    @csrf
                                    <div class="form-group">
                                        <label for="rejection_reason" class="font-weight-bold">Reason and requested correction <span class="text-danger" aria-hidden="true">*</span></label>
                                        <textarea id="rejection_reason" class="form-control @error('rejection_reason') is-invalid @enderror" name="rejection_reason" maxlength="500" rows="4" required aria-describedby="rejection_reason_help rejection_reason_error" placeholder="Example: Please upload a clearer image showing all four corners of the ID card.">{{ old('rejection_reason') }}</textarea>
                                        <small id="rejection_reason_help" class="form-text text-muted">Required, maximum 500 characters.</small>
                                        @error('rejection_reason')
                                            <div id="rejection_reason_error" class="invalid-feedback" role="alert">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="text-right">
                                        <button class="btn btn-danger" type="submit">
                                            <i class="fas fa-reply mr-1" aria-hidden="true"></i> Return for correction
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        @elseif ($identity->reviewed_at)
            <div class="alert alert-secondary" role="status">
                <strong>Review completed.</strong>
                Status: {{ $identity->status->value }} · {{ $identity->reviewed_at?->format('d M Y, H:i') }}
            </div>
        @endif
    @endif
</main>
@endsection
