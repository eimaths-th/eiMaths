<div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="addCourseModalLabel">{{ __('course.courses.modal.add_title') }}</h5>
                    <small class="text-muted">{{ __('course.courses.modal.add_description') }}</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('common.actions.close') }}"></button>
            </div>
            <div class="modal-body">
                <form id="addCourseForm" class="form" enctype="multipart/form-data">
                    <div class="alert alert-info py-2" role="note">
                        {{ __('course.courses.modal.affiliate_note_add') }}
                    </div>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="courseTitle" class="form-label">{{ __('course.courses.modal.course_title') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="courseTitle" name="title" required maxlength="255">
                        </div>

                        <div class="col-md-4">
                            <label for="coursePrice" class="form-label">{{ __('course.courses.modal.price_baht') }} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="coursePrice" name="price" min="0" step="1" required>
                        </div>

                        <div class="col-md-6">
                            <label for="categoryId" class="form-label">{{ __('course.courses.modal.category') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="categoryId" name="category_id" required>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="deliveryMode" class="form-label">{{ __('course.courses.modal.delivery_mode') }} <span class="text-danger">*</span></label>
                            <select class="form-select" id="deliveryMode" name="mode_of_delivery" required>
                                <option value="3">{{ __('course.courses.modal.face_to_face') }}</option>
                                <option value="1">{{ __('course.courses.modal.online') }}</option>
                                <option value="2">{{ __('course.courses.modal.distance_learning') }}</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="courseAbout" class="form-label">{{ __('course.courses.modal.short_description') }}</label>
                            <textarea class="form-control" id="courseAbout" name="about" rows="2" maxlength="1000"
                                placeholder="{{ __('course.courses.modal.short_description_placeholder') }}"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label for="courseOutcomes" class="form-label">{{ __('course.courses.modal.outcomes') }}</label>
                            <textarea class="form-control" id="courseOutcomes" name="outcomes" rows="3" maxlength="1000"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label for="courseRequirements" class="form-label">{{ __('course.courses.modal.requirements') }}</label>
                            <textarea class="form-control" id="courseRequirements" name="requirements" rows="3" maxlength="1000"></textarea>
                        </div>

                        <div class="col-12">
                            <label for="courseImage" class="form-label">{{ __('course.courses.modal.cover_image') }} <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="courseImage" name="image"
                                accept="image/jpeg,image/png,.jpg,.jpeg,.png" required>
                            <div class="form-text">{{ __('course.courses.modal.image_help') }}</div>
                            <img id="newCourseImagePreview" alt="{{ __('course.courses.modal.image_alt') }}"
                                class="img-fluid rounded border mt-2 d-none" style="width: 100%; max-height: 280px; object-fit: cover;">
                        </div>

                        <div class="col-12">
                            <span class="form-label d-block">{{ __('course.courses.modal.status') }} <span class="text-danger">*</span></span>
                            <div class="d-flex flex-wrap gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="statusActive" name="status" value="1" checked>
                                    <label class="form-check-label" for="statusActive">{{ __('course.courses.modal.active') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="statusInactive" name="status" value="0">
                                    <label class="form-check-label" for="statusInactive">{{ __('course.courses.modal.inactive') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('course.courses.modal.cancel') }}</button>
                <button type="submit" class="btn btn-primary" form="addCourseForm">{{ __('course.courses.modal.save_course') }}</button>
            </div>
        </div>
    </div>
</div>
