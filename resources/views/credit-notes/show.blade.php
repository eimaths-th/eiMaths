@extends('layouts.app')

@section('style')
    <link rel="stylesheet" href="{{ asset('css/credit-notes.css') }}">
@endsection

@section('content')
    <div class="credit-note-page-shell">
        <section class="content-header credit-note-editor-header">
            <div class="container-fluid">
                <div class="d-flex flex-wrap justify-content-between align-items-start">
                    <div>
                        <p class="credit-note-eyebrow mb-1">{{ __('credit-note.page.financial_documents') }}</p>
                        <h1 class="mb-1">
                            {{ __('credit-note.navigation.title') }}
                            <span class="text-muted">{{ $credit_note->number ?? __('credit-note.register.draft_number', ['id' => $credit_note->id]) }}</span>
                        </h1>
                        <p class="text-muted mb-0">
                            {{ __('credit-note.page.original_receipt') }}: {{ $credit_note->original_receipt_number ?? '—' }}
                            <span class="mx-1">•</span>
                            {{ optional($credit_note->original_receipt_date)->format('Y-m-d') ?? '—' }}
                        </p>
                    </div>
                    <div class="credit-note-page-actions mt-2 mt-md-0">
                        @can('credit-note-show')
                            <a class="btn btn-outline-secondary" href="{{ route('credit-notes.print', $credit_note) }}" target="_blank" rel="noopener">
                                <i class="fas fa-print mr-1"></i>{{ __('credit-note.actions.print') }}
                            </a>
                            <a class="btn btn-outline-secondary" href="{{ route('credit-notes.pdf', $credit_note) }}">
                                <i class="fas fa-file-pdf mr-1"></i>{{ __('credit-note.actions.pdf') }}
                            </a>
                        @endcan
                        <a class="btn btn-primary" href="{{ route('credit-notes.index') }}">
                            <i class="fas fa-list mr-1"></i>{{ __('credit-note.navigation.register') }}
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="content" aria-labelledby="credit-note-detail-title">
            <div class="container-fluid">
                @if (session('success'))
                    <div id="credit-note-feedback-success" class="alert alert-success credit-note-feedback" role="status" aria-live="polite">
                        <i class="fas fa-check-circle mr-1" aria-hidden="true"></i>{{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div id="credit-note-feedback-error" class="alert alert-danger credit-note-feedback" role="alert" aria-live="assertive">
                        <i class="fas fa-exclamation-circle mr-1" aria-hidden="true"></i>
                        <div>
                            <div>{{ $errors->first() }}</div>
                            @if (session('credit_note_error_code') === 'CREDIT_NOTE_ISSUER_CONFIGURATION_INVALID')
                                <div id="credit-note-issuer-config-details" class="credit-note-feedback-details">
                                    <strong>{{ __('credit-note.page.required_issuer_setup') }}</strong>
                                    <ul class="mb-1 pl-3">
                                        <li>{{ __('credit-note.page.legal_name') }} <code>CREDIT_NOTE_ISSUER_LEGAL_NAME</code></li>
                                        <li>{{ __('credit-note.page.legal_address') }} <code>CREDIT_NOTE_ISSUER_LEGAL_ADDRESS</code></li>
                                        <li>{{ __('credit-note.page.valid_tax_id') }} <code>CREDIT_NOTE_ISSUER_TAX_ID</code></li>
                                        <li>{{ __('credit-note.page.head_office_branch') }} <code>CREDIT_NOTE_ISSUER_BRANCH_TYPE=head_office</code> and <code>CREDIT_NOTE_ISSUER_BRANCH_CODE=00000</code></li>
                                    </ul>
                                    <span>{{ __('credit-note.page.configuration_guidance') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="card card-primary card-outline credit-note-workspace-card">
                    <div class="card-header credit-note-workspace-header">
                        <div class="credit-note-workspace-copy">
                            <h2 id="credit-note-detail-title" class="card-title mb-1">
                                <i class="fas fa-file-invoice-dollar mr-2"></i>{{ __('credit-note.navigation.title') }} {{ __('credit-note.page.detail') }}
                            </h2>
                            <p class="small text-muted mb-0">{{ __('credit-note.page.review_source') }}</p>
                        </div>
                        <div class="credit-note-header-status">
                            @include('credit-notes.partials.status-badge', ['credit_note' => $credit_note])
                        </div>
                    </div>
                    <div class="card-body">
                        @if (($credit_note->status?->value ?? null) === 'draft')
                            <div class="credit-note-draft-banner" role="status">
                                <span class="credit-note-draft-icon"><i class="fas fa-pen"></i></span>
                                <div>
                                    <strong>{{ __('credit-note.page.draft_document') }}</strong>
                                    <span>{{ __('credit-note.page.draft_not_posted') }}</span>
                                </div>
                            </div>
                            <div class="credit-note-watermark" aria-label="{{ __('credit-note.page.draft_watermark') }}">{{ __('credit-note.document.draft_watermark') }}</div>
                            @include('credit-notes.partials.editor', ['credit_note' => $credit_note, 'source_snapshot' => $source_snapshot])
                        @else
                            @include('credit-notes.partials.totals', ['credit_note' => $credit_note])
                            @if (($credit_note->status?->value ?? null) === 'issued' && config('financial-documents.credit_note.financial_actions_enabled') && auth()->user()?->can('credit-note-cancel'))
                                <div class="credit-note-action-panel mt-4">
                                    <div>
                                        <h3 class="h6 mb-1">{{ __('credit-note.page.cancel_issued_document') }}</h3>
                                        <p class="small text-muted mb-0">{{ __('credit-note.page.cancellation_reversal') }}</p>
                                    </div>
                                    <form method="POST" action="{{ route('credit-notes.cancel', $credit_note) }}" class="credit-note-inline-form">
                                        @csrf
                                        <input type="hidden" name="idempotency_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
                                        <label class="sr-only" for="issued-cancellation-reason">{{ __('credit-note.editor.cancellation_reason') }}</label>
                                        <input id="issued-cancellation-reason" name="reason" class="form-control" required maxlength="2000" placeholder="{{ __('credit-note.editor.cancellation_reason') }}">
                                        <button type="submit" class="btn btn-outline-danger">{{ __('credit-note.actions.cancel') }}</button>
                                    </form>
                                </div>
                            @endif
                        @endif
                        @include('credit-notes.partials.timeline', ['credit_note' => $credit_note])
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('script')
    <script type="module" src="{{ asset('js/credit-note-editor.js') }}"></script>
@endsection
