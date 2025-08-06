<div class="modal fade" id="CreateModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title">
                    <i class="fas fa-user"></i>
                    <span id="question-title"></span>
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
                    <div>
                        <label for="title" class="form-label">Title</label>
                        <input id="title" class="form-control" type="text" name="title"
                            value="{{ old('title', $quiz->title ?? '') }}">
                        @error('title')
                            <span class="text-red-600 text-sm mt-2">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <label for="slug" class="form-label">Slug</label>
                        <input id="slug" class="form-control" type="text" name="slug"
                            value="{{ old('slug', $quiz->slug ?? '') }}" disabled>
                        @error('slug')
                            <span class="text-red-600 text-sm mt-2">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" class="form-control" name="description">{{ old('description', $quiz->description ?? '') }}</textarea>
                        @error('description')
                            <span class="text-red-600 text-sm mt-2">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="mt-4">
                                <label for="level" class=" form-label">Grade</label>
                                <select id="level" name="level" class="form-control">
                                    <option value="" disabled selected>Select term</option>
                                    <option value="K1">K1</option>
                                    <option value="K2">K2</option>
                                    <option value="P1">P1</option>
                                    <option value="P2">P2</option>
                                    <option value="P3">P3</option>
                                    <option value="P4">P4</option>
                                    <option value="P5">P5</option>
                                    <option value="P6">P6</option>
                                </select>

                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mt-4">
                                <label for="term" class=" form-label">Term</label>
                                <select id="term" name="term" class="form-control">
                                    <option value="" disabled selected>Select term</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                </select>

                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mt-4">
                                <label for="section" class=" form-label">Level</label>
                                <select id="section" name="section" class="form-control">
                                    <option value="" disabled selected>Select Level</option>
                                    @for ($i = 1; $i <= 20; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor

                                </select>

                            </div>
                        </div>
                    </div>
                    <div class="mt-4" style="overflow: auto;">
                        <label for="questions" class="form-label">Questions</label>
                        <select id="questions" name="questions[]" class="select2 form-control"
                             multiple>
                        </select>
                        @error('questions')
                            <span class="text-red-600 text-sm mt-2">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- <div class="mt-4 form-check">
                        <div class="flex items-center">
                            <label for="published" class="form-label">Published</label>
                            <input type="checkbox" id="published" class="form-check-input ml-2" name="published"
                                {{ old('published', $quiz->published ?? false) ? 'checked' : '' }}>
                        </div>
                        @error('published')
                            <span class="text-red-600 text-sm mt-2">{{ $message }}</span>
                        @enderror
                    </div> --}}

                    <div class="mt-4 form-check">
                        <div class="flex items-center">
                            <label for="public" class="form-label">Public</label>
                            <input type="checkbox" id="public" class="form-check-input ml-2" name="public"
                                {{ old('public', $quiz->public ?? false) ? 'checked' : '' }}>
                        </div>
                        @error('public')
                            <span class="text-red-600 text-sm mt-2">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            Save
                        </button>
                    </div> --}}
                </form>

            </div>
            <div class="modal-footer">
                <span id="btn-submit">
                    {{-- <button type="button" class="btn btn-success" id="SubmitCreateForm">
                        <i class="fas fa-download"></i> Save
                    </button> --}}
                </span>

                <button type="button" class="btn btn-danger modelClose" data-bs-dismiss="modal">
                    <i class="fas fa-door-closed"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>
