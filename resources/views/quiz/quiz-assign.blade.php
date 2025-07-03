<div class="modal fade" id="AssignModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title">
                    <i class="fas fa-user"></i>
                    <span id="assign-title"></span>
                    {{-- {{ $editing ? 'Edit Question' : 'Create Question' }} --}}
                </h4>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
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


                <form class="form" action="#" method="POST" id="quizForm">
                    @csrf

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mt-4">
                                <label for="student" class=" form-label">Student</label>
                                <select id="student_id" name="student_id[]" class="select-std form-control" multiple>

                                    @foreach ($students as $student)
                                        <option value="{{ $student->id }}">{{ $student->code }} {{ $student->name }}</option>
                                    @endforeach

                                </select>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mt-4">
                                <label for="quiz" class=" form-label">Quiz</label>

                                <select id="quiz_id" name="quiz_id[]" class="select-quiz form-control" multiple>
                                    @foreach ($quizzes as $quiz)
                                        <option value="{{ $quiz->id }}">{{ $quiz->title }} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mt-4">
                                <label for="status" class=" form-label">Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="" disabled selected>Select Status</option>
                                    <option value="active">active</option>
                                    <option value="inactive">inactive</option>
                                </select>

                            </div>
                        </div>
                    </div>
                </form>

            </div>
            <div class="modal-footer">
                <span id="btn-assign-submit">
                </span>

                <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal">
                    <i class="fas fa-door-closed"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>
