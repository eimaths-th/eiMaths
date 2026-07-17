@extends('layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Lead Detail #{{ $lead->id }}</h1>
            </div>
            <div class="col-sm-6">
                <a href="{{ route('crm.leads.index') }}" class="btn btn-secondary float-sm-right">Back</a>
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
                Possible duplicate of lead #{{ $lead->duplicateOf->id }}: {{ $lead->duplicateOf->parent_name }} / {{ $lead->duplicateOf->phone }}
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Lead Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Parent</h5>
                                <p><strong>Name:</strong> {{ $lead->parent_name }}</p>
                                <p><strong>Phone:</strong> {{ $lead->phone }}</p>
                                <p><strong>LINE ID:</strong> {{ $lead->line_id ?: '-' }}</p>
                                <p><strong>Email:</strong> {{ $lead->email ?: '-' }}</p>
                                <p><strong>Preferred time:</strong> {{ $lead->preferred_contact_time ?: '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h5>Student</h5>
                                <p><strong>Name:</strong> {{ $lead->student_name }}</p>
                                <p><strong>Grade:</strong> {{ $lead->student_grade }}</p>
                                <p><strong>School:</strong> {{ $lead->student_school ?: '-' }}</p>
                                <p><strong>Age:</strong> {{ $lead->student_age ?: '-' }}</p>
                            </div>
                        </div>

                        <hr>
                        <h5>LINE OA</h5>
                        <div class="row">
                            <div class="col-md-3">
                                @if($lead->line_picture_url)
                                    <img src="{{ $lead->line_picture_url }}" alt="{{ $lead->line_display_name }}" class="img-fluid rounded-circle mb-3" style="max-width: 96px;">
                                @endif
                            </div>
                            <div class="col-md-9">
                                <p><strong>Display name:</strong> {{ $lead->line_display_name ?: '-' }}</p>
                                <p><strong>LINE user ID:</strong> <span class="text-break">{{ $lead->line_user_id ?: '-' }}</span></p>
                                <p><strong>Follow status:</strong> {{ $lead->line_follow_status ?: '-' }}</p>
                                <p><strong>Followed at:</strong> {{ optional($lead->line_followed_at)->format('Y-m-d H:i') ?: '-' }}</p>
                                <p><strong>Conversation:</strong> {{ $lead->line_conversation_state ?: '-' }}</p>
                                <p><strong>LINE source:</strong> {{ $lead->line_source ?: '-' }}</p>
                                <p><strong>Campaign ref:</strong> {{ $lead->line_ref ?: '-' }}</p>
                                <p><strong>Last message:</strong> {{ optional($lead->line_last_message_at)->format('Y-m-d H:i') ?: '-' }}</p>
                            </div>
                        </div>

                        <hr>
                        <h5>Need & Interest</h5>
                        <p><strong>Pain point:</strong> {{ $lead->pain_point ?: '-' }}</p>
                        <p><strong>Interest:</strong> {{ $lead->interest ?: '-' }}</p>

                        <hr>
                        <h5>Mini Assessment</h5>
                        @if(is_array($lead->assessment_answers) && count($lead->assessment_answers))
                            <ul>
                                @foreach($lead->assessment_answers as $answer)
                                    <li>
                                        <strong>{{ $answer['question'] ?? $answer['key'] ?? 'Question' }}:</strong>
                                        {{ is_array($answer['answer'] ?? null) ? implode(', ', $answer['answer']) : ($answer['answer'] ?? '-') }}
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p>-</p>
                        @endif
                        <p><strong>Assessment note:</strong> {{ $lead->assessment_note ?: '-' }}</p>

                        <hr>
                        <h5>Tracking</h5>
                        <p><strong>Created from:</strong> {{ $lead->created_from_label }}</p>
                        <div class="alert {{ $lead->tracking_link_id ? 'alert-info' : 'alert-light border' }}">
                            <p class="mb-1"><strong>Tracking link:</strong> {{ $lead->tracking_link_name ?: '-' }}</p>
                            <p class="mb-1"><strong>Link created by:</strong> {{ $lead->tracking_link_creator_name ?: '-' }}</p>
                            <p class="mb-0"><strong>Tracking code:</strong> <span class="text-break">{{ $lead->tracking_link_code ?: '-' }}</span></p>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Source:</strong> {{ $lead->utm_source ?: '-' }}</p>
                                <p><strong>Medium:</strong> {{ $lead->utm_medium ?: '-' }}</p>
                                <p><strong>Campaign:</strong> {{ $lead->utm_campaign ?: '-' }}</p>
                                <p><strong>Content:</strong> {{ $lead->utm_content ?: '-' }}</p>
                                <p><strong>Term:</strong> {{ $lead->utm_term ?: '-' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>First touch:</strong> {{ $lead->first_touch_source ?: '-' }}</p>
                                <p><strong>Last touch:</strong> {{ $lead->last_touch_source ?: '-' }}</p>
                                <p><strong>Referrer:</strong> <span class="text-break">{{ $lead->referrer ?: '-' }}</span></p>
                                <p><strong>Landing page:</strong> <span class="text-break">{{ $lead->landing_page ?: '-' }}</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">Notes / Contact History</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('crm.leads.notes.store', $lead) }}" class="mb-4">
                            @csrf
                            <div class="form-group">
                                <textarea name="note" class="form-control" rows="3" placeholder="Add contact note..." required></textarea>
                            </div>
                            <div class="form-group">
                                <select name="status_after" class="form-control">
                                    <option value="">Keep current status</option>
                                    @foreach($statuses as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Save Note</button>
                        </form>

                        @forelse($lead->notes as $note)
                            <div class="border rounded p-3 mb-2">
                                <div class="text-muted small">
                                    {{ optional($note->created_at)->format('Y-m-d H:i') }}
                                    by {{ $note->creator->name ?? 'System' }}
                                    @if($note->status_before || $note->status_after)
                                        | {{ $note->status_before ?: '-' }} -> {{ $note->status_after ?: '-' }}
                                    @endif
                                </div>
                                <div>{{ $note->note }}</div>
                            </div>
                        @empty
                            <p>No notes yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">Click IDs</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>fbclid:</strong> <span class="text-break">{{ $lead->fbclid ?: '-' }}</span></p>
                        <p><strong>gclid:</strong> <span class="text-break">{{ $lead->gclid ?: '-' }}</span></p>
                        <p><strong>ttclid:</strong> <span class="text-break">{{ $lead->ttclid ?: '-' }}</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
