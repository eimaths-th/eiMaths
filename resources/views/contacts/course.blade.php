<div class="modal fade" id="CourseModal">
    <div class="modal-dialog modal-xxl">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title"><i class="fa-solid fa-graduation-cap"></i> {{ __('students.course.title') }}</h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="{{ __('common.actions.close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {{-- @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <strong>Whoops!</strong>Something went wrong.<br><br>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif --}}
                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="display: none;">
                    <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="alert alert-success alert-dismissible fade show" role="alert" style="display: none;">

                    <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">{{ __('students.course.all') }}</h3>
                                {{-- <div class="card-tools">
                                    <div class="input-group input-group-sm" style="width: 150px;">
                                        <input type="text" name="table_search" class="form-control float-right"
                                            placeholder="Search">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div> --}}
                            </div>

                            <div class="card-body table-responsive p-0" {{-- style="height: 500px;" --}}>
                                <table class="table table-striped table-head-fixed text-nowrap">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('common.fields.level') }}</th>
                                            <th>{{ __('common.fields.term') }}</th>
                                            <th>{{ __('common.fields.book') }}</th>
                                            <th>{{ __('students.course.free_course') }}</th>
                                            <th>{{ __('common.fields.reason') }}</th>
                                            <th>{{ __('students.course.created_at') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="CourseTable">

                                    </tbody>
                                </table>
                            </div>

                        </div>

                    </div>
                </div>

            </div>
            <div class="modal-footer {{-- justify-content-between --}}">
                <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal"><i
                        class="fas fa-door-closed"></i> {{ __('common.actions.close') }}</button>
            </div>
        </div>
    </div>
</div>
