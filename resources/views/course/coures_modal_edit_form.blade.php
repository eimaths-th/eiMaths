<div class="modal fade" id="editCourseModal" tabindex="-1" aria-labelledby="editCourseLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCourseLabel">Edit Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editCourseForm" class="form" enctype="multipart/form-data">

                    <input type="hidden"  id="editCourseId" name="id">

                    <!-- The same form fields as the add form -->

                    <!-- Course Title -->
                    <div class="mb-3">
                        <label for="editCourseTitle" class="form-label">Course Title</label>
                        <input type="text" class="form-control" id="editCourseTitle" name="title" value=""
                            required>
                    </div>

                    <!-- Categories -->
                    <div class="mb-3">
                        <label for="editCategoryId" class="form-label">Categories:</label>
                        <select class="form-select" id="editCategoryId" name="category_id" required>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Add other fields as needed -->

                    <div class="mb-3">
                        <label for="courseDelivery" class="form-label">Mode of Delivery:</label>
                        <select class="form-select" id="editDeliveryMode" name="mode_of_delivery" required>
                            <option value="3">Face to Face</option>
                            <option value="1">Online</option>
                        </select>
                    </div>

                    <!-- Course Price -->
                    <div class="mb-3">
                        <label for="coursePrice" class="form-label">Course Price</label>
                        <input type="number" class="form-control" id="editCoursePrice" name="price" required>
                    </div>

                    <!-- Status (Radio Buttons) -->
                    <div class="mb-3">
                        <label class="form-label">Course Status:</label>
                        <div>
                            <input type="radio" id="edittStatusActive" name="status" value="1" checked>
                            <label for="statusActive">Active</label>
                        </div>
                        <div>
                            <input type="radio" id="editStatusInactive" name="status" value="0">
                            <label for="statusInactive">Inactive</label>
                        </div>
                    </div>

                    <!-- Upload Course Image -->
                    <div class="mb-3">
                        <label for="courseImage" class="form-label">Upload Course Image:</label>
                        <input type="file" class="form-control" id="editCourseImage" name="image" accept="image/*">
                        <img id="courseImagePreview" src="" alt="Course Image" style="max-width: 100%; margin-top: 10px;">

                    </div>

                    <!-- Hidden Inputs -->
                    <input type="hidden" name="level" id="editLevel">
                    <input type="hidden" name="publish" id="editPublish">
                    <input type="hidden" name="lang_id" id="editLang_id">
                    <input type="hidden" name="user_id" id="editUser_id">
                    <input type="hidden" name="type" id="editType">
                    <input type="hidden" name="scope" id="editScope">
                    <input type="hidden" name="show_mode_of_delivery" id="editShow_mode_of_delivery">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="editCourseForm" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </div>
</div>
