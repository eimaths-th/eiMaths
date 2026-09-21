@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('feedback-management.create.page_title') }}</h1>

                    {{-- <h4>Users & Roles Management</h4> --}}
                </div>
                <div class="col-sm-6">
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fa fa-user"></i>{{ __('feedback-management.create.card_title') }}</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="{{ __('feedback-management.create.collapse') }}">
                                    <i class="fas fa-minus"></i>
                                </button>
                                {{-- <button type="button" class="btn btn-tool" data-card-widget="remove" title="Remove">
                                <i class="fas fa-times"></i>
                            </button> --}}
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6">
                                    <form action="{{ route('feedback.store') }}" method="POST"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <label for="title">{{ __('feedback-management.create.fields.title') }}</label>
                                        <input class="form-control mb-3" type="text" name="title" required>
                                        <label for="description">{{ __('feedback-management.create.fields.description') }}</label>
                                        <textarea class="form-control mb-3" name="description" required></textarea>
                                        <div class="row mb-3">
                                            <div class="col-lg-6  mb-3">
                                                <label for="category">{{ __('feedback-management.create.fields.category') }}</label>
                                                <select class="form-control" name="category">
                                                    <option value="Bug">{{ __('feedback-management.create.categories.bug') }}</option>
                                                    <option value="Suggestion">{{ __('feedback-management.create.categories.suggestion') }}</option>
                                                    <option value="Other">{{ __('feedback-management.create.categories.other') }}</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-6 mb-3">
                                                <label for="priority">{{ __('feedback-management.create.fields.priority') }}</label>
                                                <select class="form-control" name="priority">
                                                    <option value="Low">{{ __('feedback-management.create.priorities.low') }}</option>
                                                    <option value="Medium">{{ __('feedback-management.create.priorities.medium') }}</option>
                                                    <option value="High">{{ __('feedback-management.create.priorities.high') }}</option>
                                                </select>
                                            </div>

                                            {{-- <div class="col-lg-6  mb-3">
                                                <label for="attachment">Attachment:</label>
                                                <input class="form-control" type="file" name="attachment">
                                            </div> --}}
                                        </div>

                                        <button class="btn btn-success" type="submit">{{ __('feedback-management.create.submit') }}</button>
                                    </form>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>
@endsection
