@php  
    $approve = true;
@endphp

@if ($approve)
    @php
        $status_enable_disable = 'status_enable_disable';
        $checked = $query->status == 1 ? 'checked' : '';
    @endphp

    <label class="switch_toggle">
        <input type="checkbox" class="{{ $status_enable_disable }}" data-id="{{ $query->id }}" {{ $checked }}>
        <i class="slider round"></i>
    </label>
@else
    {{ $query->status == 1 ? 'Approved' : 'Pending' }}
@endif