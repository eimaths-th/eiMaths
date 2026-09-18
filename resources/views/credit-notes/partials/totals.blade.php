@php
    $credit_note_totals = [
        ['label' => __('credit-note.fields.original') . ' ' . __('credit-note.editor.total_suffix'), 'value' => $credit_note->original_grand_total_minor, 'tone' => 'neutral'],
        ['label' => __('credit-note.fields.corrected') . ' ' . __('credit-note.editor.total_suffix'), 'value' => $credit_note->corrected_grand_total_minor, 'tone' => 'blue'],
        ['label' => __('credit-note.fields.difference') . ' ' . __('credit-note.editor.total_suffix'), 'value' => $credit_note->difference_grand_total_minor, 'tone' => 'orange'],
        ['label' => __('credit-note.fields.vat') . ' ' . __('credit-note.editor.difference_suffix'), 'value' => $credit_note->difference_vat_minor, 'tone' => 'purple'],
        ['label' => __('credit-note.fields.exempt') . ' ' . __('credit-note.editor.difference_suffix'), 'value' => $credit_note->difference_exempt_minor, 'tone' => 'neutral'],
        ['label' => __('credit-note.fields.out_of_scope') . ' ' . __('credit-note.editor.difference_suffix'), 'value' => $credit_note->difference_out_of_scope_minor, 'tone' => 'neutral'],
    ];
@endphp

<section aria-labelledby="credit-note-totals-title" class="credit-note-totals">
    <div class="credit-note-section-heading">
        <div>
            <span class="credit-note-section-kicker">{{ __('credit-note.editor.control_totals') }}</span>
            <h3 id="credit-note-totals-title">{{ __('credit-note.fields.original') }} / {{ __('credit-note.fields.corrected') }} / {{ __('credit-note.fields.difference') }}</h3>
        </div>
        <i class="fas fa-calculator" aria-hidden="true"></i>
    </div>
    <div class="row">
        @foreach ($credit_note_totals as $total)
            <div class="col-sm-6 col-lg-4">
                <div class="credit-note-total-card credit-note-total-card-{{ $total['tone'] }}">
                    <span class="credit-note-total-label">{{ $total['label'] }}</span>
                    <strong>{{ number_format(((int) $total['value']) / 100, 2, '.', ',') }}</strong>
                    <span class="credit-note-total-currency">THB</span>
                </div>
            </div>
        @endforeach
    </div>
</section>
