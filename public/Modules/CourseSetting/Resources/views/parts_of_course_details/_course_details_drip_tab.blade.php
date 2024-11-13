<div role="tabpanel" class="tab-pane fade  @if($type=="drip") show active @endif "
     id="drip">

    <div class="QA_section QA_section_heading_custom check_box_table  pt-20">
        <div class="QA_table ">
            <form action="{{route('setCourseDripContent')}}" method="post">
                <input type="hidden" name="course_id" value="{{$course->id}}">
                @csrf
                <table class="table  pt-0">
                    <thead>
                    <tr>
                        <th>{{__('common.Name')}}</th>
                        <th>{{__('common.Specific Date')}}</th>
                        <th></th>
                        <th>{{__('common.Days After Enrollment')}}</th>
                    </tr>
                    </thead>

                    <tbody>
                    @if(count($chapters)==0)
                        <tr>
                            <td colspan="3"
                                class="text-center">{{__('courses.No Data Found')}}</td>
                        </tr>
                    @endif
                    @php
                        $i=0;
                    @endphp
                    @foreach($chapters as $key1 => $chapter)

                        @foreach ($chapter->lessons as $key => $lesson)

                            <input type="hidden" name="lesson_id[]"
                                   value="{{@$lesson->id}}">
                            <tr>
                                <td>
                                    @if ($lesson->is_quiz==1)

                                        <span> <i class="ti-check-box"></i>   {{$key+1}}. {{@$lesson['quiz'][0]['title']}} </span>

                                    @else

                                        <span> <i class="ti-control-play"></i>  {{$key+1}}. {{$lesson['name']}} [{{MinuteFormat($lesson['duration'])}}] </span>
                                    @endif
                                </td>
                                <td>
                                    <input type="text"
                                           class="primary_input_field primary-input date form-control"
                                           placeholder="{{__('common.Select Date')}}"
                                           readonly
                                           name="lesson_date[]"
                                           value="{{@$lesson->unlock_date!=""?date('m/d/Y',strtotime($lesson->unlock_date)):""}}">
                                </td>
                                <td>
                                    <div class="row">


                                        <div class="form-check p-1">
                                            <input
                                                class="form-check-input  common-radio"
                                                type="radio"
                                                name="drip_type[{{$i}}]"
                                                id="select_drip_{{$i}}1"
                                                value="1"
                                                @if(!empty($lesson->unlock_date))checked @endif>
                                            <label class="form-check-label"
                                                   for="select_drip_{{$i}}1">
                                                {{__('common.Date')}}
                                            </label>
                                        </div>
                                        <div class="form-check  p-1">
                                            <input
                                                class="form-check-input common-radio"
                                                type="radio"
                                                name="drip_type[{{$i}}]"
                                                id="select_drip_{{$i}}2"
                                                @if(empty($lesson->unlock_date))checked
                                                @endif
                                                value="2">
                                            <label class="form-check-label"
                                                   for="select_drip_{{$i}}2">
                                                {{__('common.Days')}}
                                            </label>
                                        </div>

                                    </div>
                                </td>
                                <td>
                                    <input type="number" min="1" max="365"
                                           class="form-control"
                                           placeholder="{{__('common.Days')}}"
                                           name="lesson_day[]"
                                           value="{{@$lesson['unlock_days']}}">
                                </td>

                            </tr>
                            @php
                                $i++;
                            @endphp
                        @endforeach

                    @endforeach
                    </tbody>
                    <tfoot>
                    @if(count($chapters)!=0)
                        <tr>
                            <td colspan="3">
                                <button class="primary-btn fix-gr-bg" type="submit"
                                        data-bs-toggle="tooltip">
                                    <i class="ti-check"></i>
                                    {{__('common.Save')}}
                                </button>
                            </td>
                        </tr>
                    @endif
                    </tfoot>
                </table>
            </form>
        </div>
    </div>
</div>
