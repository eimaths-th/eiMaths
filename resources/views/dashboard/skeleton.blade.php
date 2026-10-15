<div class="dashboard-skeleton dashboard-skeleton--{{ $type }}" aria-hidden="true">
    @if ($type === 'metric')
        <span class="dashboard-skeleton__icon dashboard-skeleton__shape"></span>
        <div class="dashboard-skeleton__copy">
            <span class="dashboard-skeleton__label dashboard-skeleton__shape"></span>
            <span class="dashboard-skeleton__value dashboard-skeleton__shape"></span>
        </div>
    @else
        <div class="dashboard-skeleton__heading">
            <span class="dashboard-skeleton__title dashboard-skeleton__shape"></span>
            <span class="dashboard-skeleton__tool dashboard-skeleton__shape"></span>
        </div>
        <div class="dashboard-skeleton__plot">
            @if ($type === 'donut')
                <div class="dashboard-skeleton__donut dashboard-skeleton__shape"></div>
            @else
                <div class="dashboard-skeleton__bars">
                    @foreach ([38, 62, 48, 80, 56, 70, 90, 65] as $height)
                        <span class="dashboard-skeleton__shape" style="height: {{ $height }}%"></span>
                    @endforeach
                </div>
            @endif
            <div class="dashboard-skeleton__legend">
                <span class="dashboard-skeleton__shape"></span>
                <span class="dashboard-skeleton__shape"></span>
                <span class="dashboard-skeleton__shape"></span>
            </div>
        </div>
    @endif
</div>
