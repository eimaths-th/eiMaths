@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2>{{ __('centre-management.transfers.title') }}</h2>
    <p>{{ __('centre-management.transfers.description') }}</p>
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
    <form method="POST" action="{{ route('branch-transfers.store') }}">
        @csrf
        <div class="form-group"><label for="transfer-type">{{ __('centre-management.transfers.fields.type') }}</label>
            <select name="type" id="transfer-type" class="form-control"><option value="student">{{ __('centre-management.transfers.options.student') }}</option><option value="user">{{ __('centre-management.transfers.options.user') }}</option></select>
        </div>
        <div class="form-group"><label for="transfer-search">{{ __('centre-management.transfers.fields.search') }}</label>
            <div class="input-group"><input id="transfer-search" class="form-control" minlength="2" maxlength="100" placeholder="{{ __('centre-management.transfers.fields.search_placeholder') }}">
            <div class="input-group-append"><button type="button" id="find-transfer" class="btn btn-secondary">{{ __('centre-management.transfers.actions.search') }}</button></div></div>
            <label for="record-id" class="mt-2">{{ __('centre-management.transfers.fields.record') }}</label>
            <select id="record-id" name="record_id" required class="form-control"><option value="">{{ __('centre-management.transfers.options.select_search_result') }}</option></select>
            <small id="transfer-search-status" role="status" class="form-text text-muted"></small>
        </div>
        <div class="form-group"><label for="branch-id">{{ __('centre-management.transfers.fields.destination_branch') }}</label><select id="branch-id" name="to_branch_id" class="form-control">@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select></div>
        <div class="form-group" id="transfer-position-group" hidden><label for="transfer-position">{{ __('centre-management.transfers.fields.destination_department') }}</label>
            <select id="transfer-position" name="position_id" class="form-control"><option value="">{{ __('centre-management.transfers.options.no_department') }}</option></select>
        </div>
        <div class="form-group"><label for="transfer-reason">{{ __('centre-management.transfers.fields.reason') }}</label><textarea id="transfer-reason" name="reason" minlength="5" maxlength="1000" required class="form-control">{{ old('reason') }}</textarea></div>
        <button class="btn btn-primary">{{ __('centre-management.transfers.actions.confirm') }}</button>
    </form>
</div>
@endsection


@section('script')
<script>
(() => {
    const transfer_ui = @json(__('centre-management.transfers'));
    const positions = @json($positions);
    const type = document.getElementById('transfer-type');
    const results = document.getElementById('record-id');
    const status = document.getElementById('transfer-search-status');
    const destination = document.getElementById('branch-id');
    const position = document.getElementById('transfer-position');
    function setPositions() {
        document.getElementById('transfer-position-group').hidden = type.value !== 'user';
        position.disabled = type.value !== 'user';
        position.replaceChildren(new Option(transfer_ui.options.no_department, ''));
        positions.filter(item => String(item.department_id) === destination.value)
            .forEach(item => position.add(new Option(item.name, item.id)));
    }
    type.addEventListener('change', () => {
        results.replaceChildren(new Option(transfer_ui.options.select_search_result, ''));
        setPositions();
    });
    destination.addEventListener('change', setPositions);
    document.getElementById('find-transfer').addEventListener('click', async () => {
        const q = document.getElementById('transfer-search').value.trim();
        if (q.length < 2) { status.textContent = transfer_ui.status.minimum_search; return; }
        const searchedType = type.value;
        results.replaceChildren(new Option(transfer_ui.options.select_record, ''));
        status.textContent = transfer_ui.status.searching;
        try {
            const url = new URL(@json(route('branch-transfers.targets')));
            url.searchParams.set('type', searchedType);
            url.searchParams.set('q', q);
            const response = await fetch(url, {headers: {'Accept': 'application/json'}});
            if (!response.ok) { throw new Error(transfer_ui.status.search_failed); }
            const payload = await response.json();
            if (type.value !== searchedType) { return; }
            payload.data.forEach(item => results.add(new Option(item.label, item.id)));
            status.textContent = payload.data.length ? transfer_ui.status.results : transfer_ui.status.no_results;
        } catch (error) { status.textContent = error.message; }
    });
    setPositions();
})();
</script>
@endsection
