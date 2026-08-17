<form method="POST" action="{{ route('affiliate.employee-access.store') }}">
    @csrf
    <button class="btn btn-primary" type="submit">{{ __('affiliate.employee_access.request_action') }}</button>
</form>
