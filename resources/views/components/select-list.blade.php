<div>
    <div wire:ignore class="w-full">
        <select class="select2 w-full" data-placeholder="Select question" {{ $attributes }}>
            @if (!isset($attributes['multiple']))
                <option></option>
            @endif
            @foreach ($options as $key => $value)
                <option value="{{ $key }}">{{ $value }}</option>
            @endforeach
        </select>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {

            document.addEventListener("livewire:initialized", () => {

                let el = $('#{{ $attributes['id'] }}');

                function initSelect() {
                    console.log('Initializing Select2');
                    el.select2({
                        placeholder: 'Select question',
                        allowClear: !el.attr('required')
                    });
                }

                initSelect();

                Livewire.hook('message.processed', (message, component) => {
                    initSelect();
                });

                el.on('change', function(e) {
                    let data = $(this).select2("val");
                    if (data === "") {
                        data = null;
                    }
                    @this.set('{{ $attributes['wire:model'] }}', data)
                });

            });

        });
    </script>
@endpush

