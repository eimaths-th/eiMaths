<div class="modal fade" id="editCourseModal" tabindex="-1" aria-labelledby="editCourseLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="editCourseLabel">{{ __('course.courses.modal.edit_title') }}</h5>
                    <small class="text-muted">{{ __('course.courses.modal.edit_description') }}</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('common.actions.close') }}"></button>
            </div>
            <div class="modal-body">
                <form id="editCourseForm" class="form" enctype="multipart/form-data">
                    <input type="hidden" id="editCourseId" name="id">

                    <div class="alert alert-info py-2" role="note">
                        {{ __('course.courses.modal.affiliate_note_edit') }}
                    </div>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="editCourseTitle" class="form-label">{{ __('course.courses.modal.course_title') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editCourseTitle" name="title" required maxlength="255">
                            <div class="form-text">{{ __('course.courses.modal.normalize_help') }}</div>
                        </div>

                        <div class="col-md-4">
                            <label for="editCoursePrice" class="form-label">{{ __('course.courses.modal.price_baht') }} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="editCoursePrice" name="price" min="0" step="1" required>
                        </div>

                        <div class="col-md-6">
                            <label for="editCategoryId" class="form-label">{{ __('course.courses.modal.category') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="editCategoryId" name="category_id" required>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="editDeliveryMode" class="form-label">{{ __('course.courses.modal.delivery_mode') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="editDeliveryMode" name="mode_of_delivery" required>
                                <option value="3">{{ __('course.courses.modal.face_to_face') }}</option>
                                <option value="1">{{ __('course.courses.modal.online') }}</option>
                                <option value="2">{{ __('course.courses.modal.distance_learning') }}</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="editCourseAbout" class="form-label">{{ __('course.courses.modal.short_description') }}</label>
                            <textarea class="form-control" id="editCourseAbout" name="about" rows="2" maxlength="1000"
                                placeholder="{{ __('course.courses.modal.short_description_placeholder') }}"></textarea>
                            <div class="form-text">{{ __('course.courses.modal.short_description_help') }}</div>
                        </div>

                        <div class="col-md-6">
                            <label for="editCourseOutcomes" class="form-label">{{ __('course.courses.modal.outcomes') }}</label>
                            <textarea class="form-control" id="editCourseOutcomes" name="outcomes" rows="3" maxlength="1000"
                                placeholder="{{ __('course.courses.modal.outcomes_placeholder') }}"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label for="editCourseRequirements" class="form-label">{{ __('course.courses.modal.requirements') }}</label>
                            <textarea class="form-control" id="editCourseRequirements" name="requirements" rows="3" maxlength="1000"
                                placeholder="{{ __('course.courses.modal.requirements_placeholder') }}"></textarea>
                        </div>

                        <div class="col-12">
                            <label for="editCourseImage" class="form-label">{{ __('course.courses.modal.cover_image') }}</label>
                            <input type="file" class="form-control" id="editCourseImage" name="image"
                                accept="image/jpeg,image/png,.jpg,.jpeg,.png">
                            <div class="form-text">{{ __('course.courses.modal.image_help') }}</div>
                            <div id="courseImageMissingWarning" class="alert alert-warning py-2 mt-2 mb-0 d-none" role="alert">
                                {{ __('course.courses.modal.missing_image_warning') }}
                            </div>
                            <img id="courseImagePreview" src="" alt="{{ __('course.courses.modal.image_alt') }}"
                                class="img-fluid rounded border mt-2" style="width: 100%; max-height: 280px; object-fit: cover;">
                        </div>

                        <div class="col-12">
                            <span class="form-label d-block">{{ __('course.courses.modal.status') }} <span class="text-danger">*</span></span>
                            <div class="d-flex flex-wrap gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="editStatusActive" name="status" value="1">
                                    <label class="form-check-label" for="editStatusActive">{{ __('course.courses.modal.active') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="editStatusInactive" name="status" value="0">
                                    <label class="form-check-label" for="editStatusInactive">{{ __('course.courses.modal.inactive') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('course.courses.modal.cancel') }}</button>
                <button type="submit" form="editCourseForm" class="btn btn-primary">{{ __('course.courses.modal.save_changes') }}</button>
            </div>
        </div>
    </div>
</div>
