<section class="book-shipment-card mb-4" aria-labelledby="book-shipment-history-title">
    <div class="book-shipment-card-header">
        <h2 id="book-shipment-history-title">{{ trans('book-shipments.history.title', []) }}</h2>
    </div>
    <div class="p-3">
        @if ($shipment->Logs->isEmpty())
            <p class="text-muted small mb-0">{{ trans('book-shipments.history.empty', []) }}</p>
        @else
            <ol class="list-unstyled mb-0">
                @foreach ($shipment->Logs as $log)
                    <li class="border-bottom py-2">
                        <div class="font-weight-bold">{{ trans('book-shipments.log_action.' . $log->action, []) }}</div>
                        <div class="small text-muted">
                            {{ $log->actor_name ?: trans('book-shipments.history.system', []) }} · {{ $log->created_at?->format('d/m/Y H:i') ?: '—' }}
                        </div>
                        @if ($log->note)
                            <div class="small mt-1">{{ $log->note }}</div>
                        @endif
                    </li>
                @endforeach
            </ol>
        @endif
    </div>
</section>
