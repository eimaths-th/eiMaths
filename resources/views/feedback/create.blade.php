@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Create Feedback</h1>

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
                            <h3 class="card-title"><i class="fa fa-user"></i>Create Feedback</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
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
                                        <label for="title">Title:</label>
                                        <input class="form-control mb-3" type="text" name="title" required>
                                        <label for="description">Description:</label>
                                        <textarea class="form-control mb-3" name="description" required></textarea>
                                        <div class="row mb-3">
                                            <div class="col-lg-6  mb-3">
                                                <label for="category">Category:</label>
                                                <select class="form-control" name="category">
                                                    <option value="Bug">Bug</option>
                                                    <option value="Suggestion">Suggestion</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-6 mb-3">
                                                <label for="priority">Priority:</label>
                                                <select class="form-control" name="priority">
                                                    <option value="Low">Low</option>
                                                    <option value="Medium">Medium</option>
                                                    <option value="High">High</option>
                                                </select>
                                            </div>

                                            {{-- <div class="col-lg-6  mb-3">
                                                <label for="attachment">Attachment:</label>
                                                <input class="form-control" type="file" name="attachment">
                                            </div> --}}
                                        </div>

                                        <button class="btn btn-success" type="submit">Submit Feedback</button>
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
