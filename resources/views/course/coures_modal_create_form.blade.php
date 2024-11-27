<div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCourseModalLabel">Add New Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- <form id="addCourseForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="courseTitle" class="form-label">Course Title</label>
                        <input type="text" class="form-control" id="courseTitle" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="courseType" class="form-label">categories : </label>
                        <select class="form-select" id="categoryId" name="category_id" required>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="courseType" class="form-label">Mode of Delivery : </label>
                        <select class="form-select" id="categoryId" name="category_id" required>
                            <option value="3">Face to Face</option>
                            <option value="1">Online</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="coursePrice" class="form-label">Course Price</label>
                        <input type="text" class="form-control" id="coursePrice" name="price" required>
                    </div>

                    <input type="hidden" name="level" id="courseLevel" value="1">
                    
                    <input type="hidden" name="publish" id="coursePublish" value="1">
                    <input type="hidden" name="lang_id" id="langId" value="19">
                    <input type="hidden" name="user_id" id="userId" value="{{ Auth::user()->id }}">
                    
                    <input type="hidden" name="type" id="courseType" value="1">
                    <input type="hidden" name="scope" id="courseScope" value="1"> 
                    <input type="hidden" name="show_mode_of_delivery" id="showModeOfDelivery" value="1">
                
                </form> --}}
                <form id="addCourseForm" class="form" enctype="multipart/form-data">
                    <!-- Course Title -->
                    <div class="mb-3">
                        <label for="courseTitle" class="form-label">Course Title</label>
                        <input type="text" class="form-control" id="courseTitle" name="title" required>
                    </div>

                    <!-- Categories -->
                    <div class="mb-3">
                        <label for="courseCategory" class="form-label">Categories:</label>
                        <select class="form-select" id="categoryId" name="category_id" required>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Mode of Delivery -->
                    <div class="mb-3">
                        <label for="courseDelivery" class="form-label">Mode of Delivery:</label>
                        <select class="form-select" id="deliveryMode" name="mode_of_delivery" required>
                            <option value="3">Face to Face</option>
                            <option value="1">Online</option>
                        </select>
                    </div>

                    <!-- Course Price -->
                    <div class="mb-3">
                        <label for="coursePrice" class="form-label">Course Price</label>
                        <input type="number" class="form-control" id="coursePrice" name="price" required>
                    </div>

                    <!-- Status (Radio Buttons) -->
                    <div class="mb-3">
                        <label class="form-label">Course Status:</label>
                        <div>
                            <input type="radio" id="statusActive" name="status" value="1" checked>
                            <label for="statusActive">Active</label>
                        </div>
                        <div>
                            <input type="radio" id="statusInactive" name="status" value="0">
                            <label for="statusInactive">Inactive</label>
                        </div>
                    </div>

                    <!-- Upload Course Image -->
                    <div class="mb-3">
                        <label for="courseImage" class="form-label">Upload Course Image:</label>
                        <input type="file" class="form-control" id="courseImage" name="image" accept="image/*"
                            required>
                    </div>

                    <!-- Hidden Inputs -->
                    <input type="hidden" name="level" value="1">
                    <input type="hidden" name="publish" value="1">
                    <input type="hidden" name="lang_id" value="19">
                    <input type="hidden" name="user_id" value="{{ Auth::user()->id }}">
                    <input type="hidden" name="type" value="1">
                    <input type="hidden" name="scope" value="1">
                    <input type="hidden" name="show_mode_of_delivery" value="1">
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="addCourseForm">Save Course</button>
            </div>
        </div>
    </div>
</div>
