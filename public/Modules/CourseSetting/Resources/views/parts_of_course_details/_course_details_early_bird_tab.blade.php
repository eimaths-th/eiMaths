@if (isModuleActive('EarlyBird') && $course_price>0)
    <div role="tabpanel"
         class="tab-pane fade  @if($type=="earlyBirdPrice") show active @endif "
         id="earlyBirdPrice">
        <div class="">
            <div class="">

                @includeIf('earlybird::inc.price_plan_list', ['price_plans' => $course->pricePlans, 'product' => $course])


            </div>
        </div>
    </div>
@endif
