@extends('backend.master')

@section('mainContent')

    {!! generateBreadcrumb() !!}

    <section class="mb-40 student-details">
        <div class="container-fluid p-0">
            <div class="row">

                <div class="col-lg-12">

                    @if (permissionCheck('gdrive.setting.update'))
                        <form class="form-horizontal" action="{{route('gdrive.setting.update')}}" method="POST"
                              enctype="multipart/form-data">
                            @endif
                            @csrf
                            <div class="white-box">

                                <div class="row   mb-3 pb-3">

                                    @if(auth()->user()->role_id==1)

                                        @includeIf('setting::storage.partials._gdrive_form')

                                        <div class=" ">
                                            <div class="col-lg-12 text-center">
                                                <button type="submit" class="primary-btn fix-gr-bg"
                                                        data-bs-toggle="tooltip">
                                                    <i class="ti-check"></i>
                                                    {{__('common.Update')}}
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        <table class="">


                                            <tbody>
                                            <tr>
                                                <th>{{__('common.Status')}} :</th>
                                                <td>
                                                    @if (auth()->user()->googleToken)
                                                        {{trans('common.Connected')}}
                                                    @else
                                                        {{trans('setting.Google Drive login is required')}}
                                                    @endif
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    @endif
                                </div>


                            </div>
                        </form>
                </div>
            </div>
        </div>
    </section>

@endsection
