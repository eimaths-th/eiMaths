@extends('layouts.app')

@section('content')
@php
    $status_labels = [];
    foreach ($statuses as $status_value => $status_raw_label) {
        $status_key = 'crm.leads.statuses.' . $status_value;
        $status_translation = __($status_key);
        $status_labels[$status_value] = $status_translation !== $status_key ? $status_translation : $status_raw_label;
    }
    $created_from_key = 'crm.leads.created_from.' . $lead->created_from;
    $created_from_translation = __($created_from_key);
    $created_from_label = $created_from_translation !== $created_from_key ? $created_from_translation : $lead->created_from_label;
@endphp
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('crm.lead_detail.title', ['id' => $lead->id]) }}</h1>
                <p>{{ __('crm.lead_detail.branch', ['name' => \App\Models\Department::find($lead->branch_id)?->name ?? __('crm.lead_detail.awaiting_central_assignment')]) }}</p>
            </div>
            <div class="col-sm-6">
                <a href="{{ route('crm.leads.index') }}" class="btn btn-secondary float-sm-right">{{ __('crm.lead_detail.back') }}</a>
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="container-fluid">
        @if (Session::has('success'))
            <div class="alert alert-success">{{ Session::get('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif

        @if($lead->duplicateOf)
            <div class="alert alert-warning">
                {{ __('crm.lead_detail.duplicate', [
                    'id' => $lead->duplicateOf->id,
                    'parent' => $lead->duplicateOf->parent_name,
                    'phone' => $lead->duplicateOf->phone,
                ]) }}
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('crm.lead_detail.information') }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>{{ __('crm.lead_detail.fields.parent') }}</h5>
                                <p><strong>{{ __('crm.lead_detail.fields.name') }}:</strong> {{ $lead->parent_name }}</p>
                                <p><strong>{{ __('crm.lead_detail.fields.phone') }}:</strong> {{ $lead->phone }}</p>
                                <p><strong>{{ __('crm.lead_detail.fields.line_id') }}:</strong> {{ $lead->line_id ?: __('crm.lead_detail.not_available') }}</p>
                                <p><strong>{{ __('crm.lead_detail.fields.email') }}:</strong> {{ $lead->email ?: __('crm.lead_detail.not_available') }}</p>
                                <p><strong>{{ __('crm.lead_detail.fields.preferred_time') }}:</strong> {{ $lead->preferred_contact_time ?: __('crm.lead_detail.not_available') }}</p>
                                <p>
                                    <strong>{{ __('crm.preferred_branch.label') }}:</strong>
                                    {{ in_array($lead->preferred_branch, \App\Models\CrmLead::PREFERRED_BRANCHES, true)
                                        ? __('crm.preferred_branch.'.$lead->preferred_branch)
                                        : __('crm.preferred_branch.unspecified') }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h5>{{ __('crm.lead_detail.fields.student') }}</h5>
                                <p><strong>{{ __('crm.lead_detail.fields.name') }}:</strong> {{ $lead->student_name }}</p>
                                <p><strong>{{ __('crm.lead_detail.fields.grade') }}:</strong> {{ $lead->student_grade }}</p>
                                <p><strong>{{ __('crm.lead_detail.fields.school') }}:</strong> {{ $lead->student_school ?: __('crm.lead_detail.not_available') }}</p>
                                <p><strong>{{ __('crm.lead_detail.fields.age') }}:</strong> {{ $lead->student_age ?: __('crm.lead_detail.not_available') }}</p>
                            </div>
                        </div>

                        <hr>
                        <h5>{{ __('crm.lead_detail.fields.line_oa') }}</h5>
                        <div class="row">
                            <div class="col-md-3">
                                @if($lead->line_picture_url)
                                    <img src="{{ $lead->line_picture_url }}" alt="{{ $lead->line_display_name }}" class="img-fluid rounded-circle mb-3" style="max-width: 96px;">
                                @endif
                            </div>
                            <div class="col-md-9">
                                <p><strong>{{ __('crm.lead_detail.fields.display_name') }}:</strong> {{ $lead->line_display_name ?: __('crm.lead_detail.not_available') }}</p>
                                <p><strong>{{ __('crm.lead_detail.fields.line_user_id') }}:</strong> <span class="text-break">{{ $lead->line_user_id ?: __('crm.lead_detail.not_available') }}</span></p>
                                <p><strong>{{ __('crm.lead_detail.fields.follow_status') }}:</strong> {{ $lead->line_follow_status ?: __('crm.lead_detail.not_available') }}</p>
                                <p><strong>{{ __('crm.lead_detail.fields.followed_at') }}:</strong> {{ optional($lead->line_followed_at)->format('Y-m-d H:i') ?: __('crm.lead_detail.not_available') }}</p>
                                <p><strong>{{ __('crm.lead_detail.fields.conversation') }}:</strong> {{ $lead->line_conversation_state ?: __('crm.lead_detail.not_available') }}</p>
                                <p><strong>{{ __('crm.lead_detail.fields.line_source') }}:</strong> {{ $lead->line_source ?: __('crm.lead_detail.not_available') }}</p>
                                <p><strong>{{ __('crm.lead_detail.fields.campaign_ref') }}:</strong> {{ $lead->line_ref ?: __('crm.lead_detail.not_available') }}</p>
                                <p><strong>{{ __('crm.lead_detail.fields.last_message') }}:</strong> {{ optional($lead->line_last_message_at)->format('Y-m-d H:i') ?: __('crm.lead_detail.not_available') }}</p>
                            </div>
                        </div>

                        <hr>
                        <h5>{{ __('crm.lead_detail.fields.need_interest') }}</h5>
                        <p><strong>{{ __('crm.lead_detail.fields.pain_point') }}:</strong> {{ $lead->pain_point ?: __('crm.lead_detail.not_available') }}</p>
                        <p><strong>{{ __('crm.lead_detail.fields.interest') }}:</strong> {{ $lead->interest ?: __('crm.lead_detail.not_available') }}</p>

                        <hr>
                        <h5>{{ __('crm.lead_detail.fields.mini_assessment') }}</h5>
                        @if(is_array($lead->assessment_answers) && count($lead->assessment_answers))
                            <ul>
                                @foreach($lead->assessment_answers as $answer)
                                    <li>
                                        <strong>{{ $answer['question'] ?? $answer['key'] ?? __('crm.lead_detail.fields.question') }}:</strong>
                                        {{ is_array($answer['answer'] ?? null) ? implode(', ', $answer['answer']) : ($answer['answer'] ?? __('crm.lead_detail.not_available')) }}
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p>{{ __('crm.lead_detail.not_available') }}</p>
                        @endif
                        <p><strong>{{ __('crm.lead_detail.fields.assessment_note') }}:</strong> {{ $lead->assessment_note ?: __('crm.lead_detail.not_available') }}</p>

                        <hr>
                        <h5>{{ __('crm.lead_detail.fields.tracking') }}</h5>
                        <p><strong>{{ __('crm.lead_detail.fields.created_from') }}:</strong> {{ $created_from_label }}</p>
                        <div class="alert {{ $lead->tracking_link_id ? 'alert-info' : 'alert-light border' }}">
                            <p class="mb-1"><strong>{{ __('crm.lead_detail.fields.tracking_link') }}:</strong> {{ $lead->tracking_link_name ?: __('crm.lead_detail.not_available') }}</p>
                            <p class="mb-1"><strong>{{ __('crm.lead_detail.fields.link_created_by') }}:</strong> {{ $lead->tracking_link_creator_name ?: __('crm.lead_detail.not_available') }}</p>
                            <p class="mb-0"><strong>{{ __('crm.lead_detail.fields.tracking_code') }}:</strong> <span class="text-break">{{ $lead->tracking_link_code ?: __('crm.lead_detail.not_available') }}</span></p>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>{{ __('crm.lead_detail.fields.source') }}:</strong> {{ $lead->utm_source ?: __('crm.lead_detail.not_available') }}</p>
                                <p><strong>{{ __('crm.lead_detail.fields.medium') }}:</strong> {{ $lead->utm_medium ?: __('crm.lead_detail.not_available') }}</p>
                                <p><strong>{{ __('crm.lead_detail.fields.campaign') }}:</strong> {{ $lead->utm_campaign ?: __('crm.lead_detail.not_available') }}</p>
                                <p><strong>{{ __('crm.lead_detail.fields.content') }}:</strong> {{ $lead->utm_content ?: __('crm.lead_detail.not_available') }}</p>
                                <p><strong>{{ __('crm.lead_detail.fields.term') }}:</strong> {{ $lead->utm_term ?: __('crm.lead_detail.not_available') }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>{{ __('crm.lead_detail.fields.first_touch') }}:</strong> {{ $lead->first_touch_source ?: __('crm.lead_detail.not_available') }}</p>
                                <p><strong>{{ __('crm.lead_detail.fields.last_touch') }}:</strong> {{ $lead->last_touch_source ?: __('crm.lead_detail.not_available') }}</p>
                                <p><strong>{{ __('crm.lead_detail.fields.referrer') }}:</strong> <span class="text-break">{{ $lead->referrer ?: __('crm.lead_detail.not_available') }}</span></p>
                                <p><strong>{{ __('crm.lead_detail.fields.landing_page') }}:</strong> <span class="text-break">{{ $lead->landing_page ?: __('crm.lead_detail.not_available') }}</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-secondary" aria-labelledby="marketing-consent-heading">
                    <div class="card-header">
                        <h3 id="marketing-consent-heading" class="card-title">{{ __('crm.lead_detail.marketing.title') }}</h3>
                    </div>
                    <div class="card-body">
                        @php
                            $marketing_context = is_array($lead->marketing_consent_context) ? $lead->marketing_consent_context : [];
                            $channel_names = __('crm.lead_detail.marketing.channel_names');
                            $marketing_channels = array_map(
                                fn ($channel) => $channel_names[$channel] ?? $channel,
                                is_array($marketing_context['channels'] ?? null) ? $marketing_context['channels'] : []
                            );
                        @endphp
                        <p class="font-weight-bold">
                            {{ $lead->marketing_consent === null
                                ? __('crm.lead_detail.marketing.unknown')
                                : ($lead->marketing_consent ? __('crm.lead_detail.marketing.accepted') : __('crm.lead_detail.marketing.declined')) }}
                        </p>
                        <p>{{ __('crm.lead_detail.marketing.warning') }}</p>
                        @if($lead->marketing_consent !== null)
                            <p><strong>{{ __('crm.lead_detail.marketing.recorded_at') }}:</strong> {{ optional($lead->marketing_consent_recorded_at)->format('Y-m-d H:i:s') ?: __('crm.lead_detail.not_available') }}</p>
                            <p><strong>{{ __('crm.lead_detail.marketing.notice_version') }}:</strong> {{ $marketing_context['notice_version'] ?? __('crm.lead_detail.not_available') }}</p>
                            <p><strong>{{ __('crm.lead_detail.marketing.consent_version') }}:</strong> {{ $marketing_context['consent_version'] ?? __('crm.lead_detail.not_available') }}</p>
                            <p><strong>{{ __('crm.lead_detail.marketing.source') }}:</strong> {{ $marketing_context['source'] ?? __('crm.lead_detail.not_available') }}</p>
                            <p><strong>{{ __('crm.lead_detail.marketing.channels') }}:</strong> {{ implode(', ', $marketing_channels) }}</p>
                        @endif
                        <h4 class="h6">{{ __('crm.lead_detail.marketing.history') }}</h4>
                        @forelse($consent_events as $consent_event)
                            @php
                                $event_context = is_array($consent_event->context) ? $consent_event->context : [];
                                $event_channels = array_map(
                                    fn ($channel) => $channel_names[$channel] ?? $channel,
                                    is_array($event_context['channels'] ?? null) ? $event_context['channels'] : []
                                );
                            @endphp
                            <div class="consent-event border-top py-2">
                                <strong>{{ $consent_event->marketing_consent ? __('crm.lead_detail.marketing.accepted') : __('crm.lead_detail.marketing.declined') }}</strong>
                                — {{ $consent_event->recorded_at->format('Y-m-d H:i:s') }}
                                — {{ $event_context['notice_version'] ?? __('crm.lead_detail.not_available') }}
                                / {{ $event_context['consent_version'] ?? __('crm.lead_detail.not_available') }}
                                — {{ $event_context['source'] ?? __('crm.lead_detail.not_available') }}
                                — {{ implode(', ', $event_channels) }}
                            </div>
                        @empty
                            <p>{{ __('crm.lead_detail.marketing.no_history') }}</p>
                        @endforelse
                    </div>
                </div>

                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('crm.lead_detail.notes.title') }}</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('crm.leads.notes.store', $lead) }}" class="mb-4">
                            @csrf
                            <div class="form-group">
                                <textarea name="note" class="form-control" rows="3" placeholder="{{ __('crm.lead_detail.notes.placeholder') }}" required></textarea>
                            </div>
                            <div class="form-group">
                                <select name="status_after" class="form-control">
                                    <option value="">{{ __('crm.lead_detail.notes.keep_status') }}</option>
                                    @foreach($statuses as $value => $label)
                                        <option value="{{ $value }}">{{ $status_labels[$value] ?? $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">{{ __('crm.lead_detail.notes.save') }}</button>
                        </form>

                        @forelse($lead->notes as $note)
                            @php
                                $note_status_before = $note->status_before
                                    ? ($status_labels[$note->status_before] ?? $note->status_before)
                                    : __('crm.lead_detail.not_available');
                                $note_status_after = $note->status_after
                                    ? ($status_labels[$note->status_after] ?? $note->status_after)
                                    : __('crm.lead_detail.not_available');
                            @endphp
                            <div class="border rounded p-3 mb-2">
                                <div class="text-muted small">
                                    {{ optional($note->created_at)->format('Y-m-d H:i') }}
                                    {{ __('crm.lead_detail.notes.by') }} {{ $note->creator->name ?? __('crm.lead_detail.notes.system') }}
                                    @if($note->status_before || $note->status_after)
                                        | {{ $note_status_before }} -> {{ $note_status_after }}
                                    @endif
                                </div>
                                <div>{{ $note->note }}</div>
                            </div>
                        @empty
                            <p>{{ __('crm.lead_detail.notes.empty') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                @if(\App\Support\Branch\BranchAccess::isCentral(auth()->user()) && auth()->user()->can('crm-leads-edit'))
                <div class="card card-body">
                    <form method="POST" action="{{ route('crm.leads.branch', $lead) }}">
                        @csrf
                        <label for="lead-branch">{{ __('crm.lead_detail.branch_transfer.label') }}</label>
                        <select id="lead-branch" name="branch_id" class="form-control mb-2">
                            @foreach(\App\Models\Department::where('status', 1)->get() as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        <label for="lead-reason">{{ __('crm.lead_detail.branch_transfer.reason') }}</label>
                        <input id="lead-reason" name="reason" minlength="5" maxlength="1000" required class="form-control mb-2">
                        <button class="btn btn-primary">{{ __('crm.lead_detail.branch_transfer.submit') }}</button>
                    </form>
                </div>
                @endif
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('crm.lead_detail.click_ids') }}</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>fbclid:</strong> <span class="text-break">{{ $lead->fbclid ?: __('crm.lead_detail.not_available') }}</span></p>
                        <p><strong>gclid:</strong> <span class="text-break">{{ $lead->gclid ?: __('crm.lead_detail.not_available') }}</span></p>
                        <p><strong>ttclid:</strong> <span class="text-break">{{ $lead->ttclid ?: __('crm.lead_detail.not_available') }}</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
