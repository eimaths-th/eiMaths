@php
    // dd($discount,$price);
@endphp
<div>
    @if (showEcommerce())
        <span class="prise_tag">

            @if ((int) $discount != 0)
                <span class="prev_prise">
                    {{ number_format($price) }}฿

                    {{-- {{ getPriceFormat($price) }} --}}
                </span>
            @endif

            <span>
                {{-- @if ((int) $discount != 0)
                    {{ getPriceFormat($discount) }}
                @else
                    {{ getPriceFormat($price) }}
                @endif --}}
                @if ((int) $discount != 0)
                    {{ $discount }}
                    {{ number_format($discount) }}฿

                @else
                    {{ number_format($price)}}฿ 
                @endif

            </span>
        </span>
    @endif
</div>
