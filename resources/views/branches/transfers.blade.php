@extends('layouts.app')

@section('content')
<div class="container py-4" id="branch-transfer-page">
    <h2>{{ __('centre-management.transfers.title') }}</h2>
    <p>{{ __('centre-management.transfers.description') }}</p>
    @if(session('success')) <div class="alert alert-success" role="status">{{ session('success') }}</div> @endif
    @if($errors->any()) <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div> @endif

    <form id="transfer-filters">
        <fieldset id="transfer-filter-fields">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="transfer-type">{{ __('centre-management.transfers.fields.type') }}</label>
                    <select id="transfer-type" class="form-control">
                        <option value="student">{{ __('centre-management.transfers.options.student') }}</option>
                        <option value="user">{{ __('centre-management.transfers.options.user') }}</option>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="transfer-source">{{ __('centre-management.transfers.bulk.source_branch') }}</label>
                    <select id="transfer-source" class="form-control">
                        <option value="">{{ __('centre-management.transfers.bulk.all_branches') }}</option>
                        @foreach($source_branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label id="transfer-search-label" for="transfer-search">{{ __('centre-management.transfers.fields.search') }}</label>
                    <input id="transfer-search" class="form-control" minlength="2" maxlength="100" aria-describedby="transfer-search-help">
                    <small id="transfer-search-help" class="form-text text-muted">{{ __('centre-management.transfers.bulk.search_help') }}</small>
                </div>
            </div>
            <button type="submit" id="find-transfer" class="btn btn-secondary">{{ __('centre-management.transfers.actions.search') }}</button>
        </fieldset>
    </form>

    <p id="transfer-status" role="status" aria-live="polite" class="mt-3"></p>
    <div id="transfer-errors" class="alert alert-danger" role="alert" tabindex="-1" hidden></div>
    <div class="d-flex flex-wrap align-items-center mb-2">
        <span id="transfer-selected-count" role="status" class="mr-3"></span>
        <button type="button" id="transfer-clear" class="btn btn-outline-secondary" disabled>{{ __('common.actions.clear') }}</button>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover" id="transfer-candidates">
            <caption class="sr-only">{{ __('centre-management.transfers.fields.record') }}</caption>
            <thead><tr>
                <th scope="col"><input type="checkbox" id="transfer-select-page" aria-label="{{ __('centre-management.transfers.bulk.select_page') }}" disabled></th>
                <th scope="col">{{ __('centre-management.transfers.bulk.name') }}</th>
                <th scope="col">{{ __('centre-management.transfers.bulk.identifier') }}</th>
                <th scope="col">{{ __('common.fields.centre') }}</th>
            </tr></thead>
            <tbody id="transfer-rows"></tbody>
        </table>
    </div>
    <nav aria-label="{{ __('centre-management.transfers.title') }}" class="d-flex flex-wrap align-items-center mb-3">
        <button type="button" id="transfer-previous" class="btn btn-outline-secondary" disabled>{{ __('common.datatable.previous') }}</button>
        <span id="transfer-page-info" class="mx-3"></span>
        <button type="button" id="transfer-next" class="btn btn-outline-secondary" disabled>{{ __('common.datatable.next') }}</button>
    </nav>

    <form id="transfer-details" method="POST" action="{{ route('branch-transfers.store') }}">
        @csrf
        <fieldset id="transfer-detail-fields">
            <div class="form-group">
                <label for="branch-id">{{ __('centre-management.transfers.fields.destination_branch') }}</label>
                <select id="branch-id" name="to_branch_id" class="form-control" required>
                    <option value="">{{ __('centre-management.messages.select_centre') }}</option>
                    @foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach
                </select>
            </div>
            <div class="form-group" id="transfer-position-group" hidden>
                <label for="transfer-position">{{ __('centre-management.transfers.fields.destination_department') }}</label>
                <select id="transfer-position" name="position_id" class="form-control" disabled></select>
            </div>
            <div class="form-group">
                <label for="transfer-reason">{{ __('centre-management.transfers.fields.reason') }}</label>
                <textarea id="transfer-reason" name="reason" minlength="5" maxlength="1000" required class="form-control">{{ old('reason') }}</textarea>
            </div>
            <button type="submit" id="transfer-review-open" class="btn btn-primary" disabled>{{ __('centre-management.transfers.bulk.review') }}</button>
        </fieldset>
    </form>

    <section id="transfer-review" aria-labelledby="transfer-review-title" tabindex="-1" hidden>
        <h3 id="transfer-review-title">{{ __('centre-management.transfers.bulk.review_title') }}</h3>
        <div id="transfer-review-summary"></div>
        <ul id="transfer-review-records" class="list-group mb-3"></ul>
        <button type="button" id="transfer-confirm" class="btn btn-primary">{{ __('centre-management.transfers.actions.confirm') }}</button>
        <button type="button" id="transfer-back" class="btn btn-secondary">{{ __('centre-management.transfers.bulk.back') }}</button>
    </section>
    <button type="button" id="transfer-reload" class="btn btn-outline-secondary mt-3" hidden>{{ __('common.actions.reload') }}</button>
    <script type="application/json" id="transfer-config">@json($transfer_config)</script>
</div>
@endsection

@section('script')
<script type="module">
import { InitializeBranchTransferPage } from '{{ asset('js/branch-transfer-page.js') }}';
InitializeBranchTransferPage();
</script>
@endsection
