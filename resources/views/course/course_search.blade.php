<div class="col-lg-12">
    <div class="card card-primary">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">{{ __('courses.Advanced Filter') }} </h4>
        </div>
        <div class="card-body">
            <form action="{{ route('course.list') }}" method="GET">
                <div class="row">

                    <div class="col-lg-3 mt-20">

                        <label class="primary_input_label"
                            for="category">{{ __('courses.Category') }}</label>
                        <select class="primary_select" name="category" id="category">
                            <option data-display="{{ __('common.Select') }} {{ __('courses.Category') }}"
                                value="">{{ __('common.Select') }} {{ __('courses.Category') }}
                            </option>
                            @foreach ($categories as $category)
                                @if ($category->parent_id == 0)
                                    @include('backend.categories._single_select_option', [
                                        'category' => $category,
                                        'level' => 1,
                                    ])
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 mt-20">

                        <label class="primary_input_label" for="type">{{ __('courses.Type') }}</label>
                        <select class="primary_select" name="type" id="type">
                            <option data-display="{{ __('common.Select') }} {{ __('courses.Type') }}"
                                value="">{{ __('common.Select') }} {{ __('courses.Type') }}</option>
                            <option value="1"
                                {{ isset($category_type) ? ($category_type == 1 ? 'selected' : '') : '' }}>
                                {{ __('courses.Course') }}</option>
                            <option value="2"
                                {{ isset($category_type) ? ($category_type == 2 ? 'selected' : '') : '' }}>
                                {{ __('quiz.Quiz') }}</option>
                        </select>

                    </div>
                    <div class="col-lg-3 mt-20">

                        <label class="primary_input_label"
                            for="instructor">{{ __('courses.Instructor') }}</label>
                        <select class="primary_select" name="instructor" id="instructor">
                            <option
                                data-display="{{ __('common.Select') }} {{ __('courses.Instructor') }}"
                                value="">{{ __('common.Select') }} {{ __('courses.Instructor') }}
                            </option>
                            {{-- @foreach ($instructors as $instructor)
                                <option value="{{ $instructor->id }}"
                                    {{ isset($category_instructor) ? ($category_instructor == $instructor->id ? 'selected' : '') : '' }}>
                                    {{ @$instructor->name }} </option>
                            @endforeach --}}
                        </select>

                    </div>

                    <div class="col-lg-3 mt-20">

                        <label class="primary_input_label" for="status">{{ __('common.Status') }}</label>
                        <select class="primary_select" name="search_status" id="status">
                            <option data-display="{{ __('common.Select') }} {{ __('common.Status') }}"
                                value="">{{ __('common.Select') }} {{ __('common.Status') }}
                            </option>
                            <option value="Active"
                                {{ isset($category_status) ? ($category_status == 'Active' ? 'selected' : '') : 'selected' }}>
                                {{ __('courses.Active') }} </option>
                            <option value="Inactive"
                                {{ isset($category_status) ? ($category_status == 'Inactive' ? 'selected' : '') : '' }}>
                                {{ __('common.Inactive') }} </option>
                        </select>

                    </div>
                    {{-- @if (isModuleActive('Org')) --}}
                    {{-- <div class="col-lg-3 mt-20">
                        <label class="primary_input_label"
                            for="search_required_type">{{ __('courses.Required Type') }}</label>
                        <select class="primary_select" name="search_required_type"
                            id="search_required_type">
                            <option
                                data-display="{{ __('common.Select') }} {{ __('courses.Required Type') }}"
                                value="">{{ __('common.Select') }} {{ __('courses.Required Type') }}
                            </option>
                            <option value="Compulsory"
                                {{ isset($search_required_type) ? ($search_required_type == 'Compulsory' ? 'selected' : '') : '' }}>
                                {{ __('courses.Compulsory') }} </option>
                            <option value="Open"
                                {{ isset($search_required_type) ? ($search_required_type == 'Open' ? 'selected' : '') : '' }}>
                                {{ __('courses.Open') }}</option>
                        </select>

                    </div>

                    <div class="col-lg-3 mt-20">

                        <label class="primary_input_label"
                            for="status">{{ __('courses.Delivery Mode') }}</label>
                        <select class="primary_select" name="search_delivery_mode" id="status">
                            <option
                                data-display="{{ __('common.Select') }} {{ __('courses.Delivery Mode') }}"
                                value="">{{ __('common.Select') }} {{ __('courses.Delivery Mode') }}
                            </option>
                            <option value="1"
                                {{ isset($search_delivery_mode) ? ($search_delivery_mode == '1' ? 'selected' : '') : '' }}>
                                {{ __('courses.Online') }} </option>
                            <option value="3"
                                {{ isset($search_delivery_mode) ? ($search_delivery_mode == '3' ? 'selected' : '') : '' }}>
                                {{ __('courses.Offline') }}</option>
                        </select>

                    </div> --}}
                    {{-- @endif --}}
                    <div class="col-12 mt-2">
                        <button type="submit" class="btn btn-primary radius_30px   fix-gr-bg">
                            <span class="ti-search pe-2"></span>

                            {{ __('courses.Filter') }} </button>
                    </div>
                </div>
            </form>
        </div>

    </div>
</div>