<section class="book-shipment-card mb-4" aria-labelledby="book-shipment-history-title">
    <div class="book-shipment-card-header">
        <h2 id="book-shipment-history-title">Shipment history</h2>
    </div>
    <div class="p-3">
        @if ($shipment->Logs->isEmpty())
            <p class="text-muted small mb-0">No history recorded yet.</p>
        @else
            <ol class="list-unstyled mb-0">
                @foreach ($shipment->Logs as $log)
                    <li class="border-bottom py-2">
                        <div class="font-weight-bold">{{ $log->action }}</div>
                        <div class="small text-muted">
                            {{ $log->actor_name ?: 'System' }} · {{ $log->created_at?->format('d M Y H:i') ?: '—' }}
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
