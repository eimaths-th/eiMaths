<div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="addCourseModalLabel">เพิ่มคอร์สใหม่</h5>
                    <small class="text-muted">กรอกข้อมูลที่ต้องการแสดงบนหน้ารวมคอร์ส Affiliate</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body">
                <form id="addCourseForm" class="form" enctype="multipart/form-data">
                    <div class="alert alert-info py-2" role="note">
                        คอร์สใหม่จากฟอร์มนี้จะถูกตั้งเป็น Course, Public และ Publish เพื่อใช้กับหน้ารวม Affiliate
                    </div>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="courseTitle" class="form-label">ชื่อคอร์ส <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="courseTitle" name="title" required maxlength="255">
                        </div>

                        <div class="col-md-4">
                            <label for="coursePrice" class="form-label">ราคาคอร์ส (บาท) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="coursePrice" name="price" min="0" step="1" required>
                        </div>

                        <div class="col-md-6">
                            <label for="categoryId" class="form-label">ระดับ/หมวดหมู่ <span class="text-danger">*</span></label>
                            <select class="form-select" id="categoryId" name="category_id" required>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="deliveryMode" class="form-label">รูปแบบการเรียน <span class="text-danger">*</span></label>
                            <select class="form-select" id="deliveryMode" name="mode_of_delivery" required>
                                <option value="3">เรียนที่สาขา (Face to Face)</option>
                                <option value="1">ออนไลน์ (Online)</option>
                                <option value="2">เรียนทางไกล (Distance Learning)</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="courseAbout" class="form-label">คำอธิบายสั้น</label>
                            <textarea class="form-control" id="courseAbout" name="about" rows="2" maxlength="1000"
                                placeholder="เช่น หลักสูตรคิดวิเคราะห์ ปูพื้นฐานและฝึกแก้โจทย์ปัญหา"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label for="courseOutcomes" class="form-label">ผลลัพธ์/จุดเด่นของคอร์ส</label>
                            <textarea class="form-control" id="courseOutcomes" name="outcomes" rows="3" maxlength="1000"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label for="courseRequirements" class="form-label">สิ่งที่รวมในคอร์ส/เงื่อนไข</label>
                            <textarea class="form-control" id="courseRequirements" name="requirements" rows="3" maxlength="1000"></textarea>
                        </div>

                        <div class="col-12">
                            <label for="courseImage" class="form-label">ภาพหน้าปกคอร์ส <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="courseImage" name="image"
                                accept="image/jpeg,image/png,.jpg,.jpeg,.png" required>
                            <div class="form-text">JPG หรือ PNG ขนาดไม่เกิน 2 MB — แนะนำอัตราส่วน 16:9 อย่างน้อย 1200×675 px</div>
                            <img id="newCourseImagePreview" alt="ตัวอย่างภาพหน้าปกคอร์ส"
                                class="img-fluid rounded border mt-2 d-none" style="width: 100%; max-height: 280px; object-fit: cover;">
                        </div>

                        <div class="col-12">
                            <span class="form-label d-block">สถานะคอร์ส <span class="text-danger">*</span></span>
                            <div class="d-flex flex-wrap gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="statusActive" name="status" value="1" checked>
                                    <label class="form-check-label" for="statusActive">เปิดแสดงบนหน้าบ้าน</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="statusInactive" name="status" value="0">
                                    <label class="form-check-label" for="statusInactive">ปิดการแสดงผล</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-primary" form="addCourseForm">บันทึกคอร์ส</button>
            </div>
        </div>
    </div>
</div>
