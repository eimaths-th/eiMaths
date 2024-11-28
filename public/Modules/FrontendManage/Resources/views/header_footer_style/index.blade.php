@php
    $styles=[
     ['id'=>1,'name'=>'Style 1'],
     ['id'=>2,'name'=>'Style 2'],
     ['id'=>3,'name'=>'Style 3'],
     ['id'=>4,'name'=>'Style 4'],
     ['id'=>5,'name'=>'Style 5'],
     ['id'=>6,'name'=>'Style 6'],
    ];
@endphp
@extends('backend.master')
@section('mainContent')
    @push('styles')

    @endpush
    {!! generateBreadcrumb() !!}

    <section class="admin-visitor-area up_st_admin_visitor">
        <div class="container-fluid p-0">

            <div class="row row-gap-3">
                <div class="col-lg-12" id="">
                    <div class="white-box student-details header-menu">
                        <div class="row">
                            <div class="col-md-12">
                                <h2 class="text-center mt-5">This feature is under development</h2>
                            </div>
                            {{--                            @foreach ($styles as $style)--}}
                            {{--                            <div class="col-lg-2 col-md-4 col-sm-6">--}}
                            {{--                                <div class="primary_checkbox footer_style_select_checkbox d-flex mr-12 ">--}}

                            {{--                                        <label class="primary_checkbox d-flex">--}}
                            {{--                                            <input type="radio" name="category"--}}
                            {{--                                                   id="style{{$style['id']}}"--}}
                            {{--                                                   {{$style['id']==''? 'checked':''}} class=""--}}
                            {{--                                                   value="{{$style['id']}}">--}}
                            {{--                                            <span class="checkmark mr_10"></span>--}}
                            {{--                                        </label>--}}
                            {{--                                        <label for="style{{$style['id']}}">{{$style['name']}}</label>--}}
                            {{--                                    </div>--}}
                            {{--                                </div>--}}
                            {{--                            @endforeach--}}
                        </div>
                        <ul class="permission_list">
                            @foreach ($styles as $style)

                                <li>

                                </li>
                        @endforeach

                    </div>
                </div>
            </div>
        </div>

    </section>
    @push('scripts')

    @endpush
@endsection
