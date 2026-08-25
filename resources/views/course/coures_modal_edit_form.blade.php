<div class="modal fade" id="editCourseModal" tabindex="-1" aria-labelledby="editCourseLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="editCourseLabel">แก้ไขข้อมูลคอร์ส</h5>
                    <small class="text-muted">ข้อมูลส่วนนี้จะแสดงบนหน้ารวมคอร์ส Affiliate</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body">
                <form id="editCourseForm" class="form" enctype="multipart/form-data">
                    <input type="hidden" id="editCourseId" name="id">

                    <div class="alert alert-info py-2" role="note">
                        หน้ารวม Affiliate จะแสดงเฉพาะรายการประเภท Course ที่เป็น Public, Publish และเปิดสถานะ Active
                    </div>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="editCourseTitle" class="form-label">ชื่อคอร์ส <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editCourseTitle" name="title" required maxlength="255">
                            <div class="form-text">ระบบจะจัดช่องว่างในชื่อให้เป็นรูปแบบเดียวกันอัตโนมัติ</div>
                        </div>

                        <div class="col-md-4">
                            <label for="editCoursePrice" class="form-label">ราคาคอร์ส (บาท) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="editCoursePrice" name="price" min="0" step="1" required>
                        </div>

                        <div class="col-md-6">
                            <label for="editCategoryId" class="form-label">ระดับ/หมวดหมู่ <span class="text-danger">*</span></label>
                            <select class="form-select" id="editCategoryId" name="category_id" required>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="editDeliveryMode" class="form-label">รูปแบบการเรียน <span class="text-danger">*</span></label>
                            <select class="form-select" id="editDeliveryMode" name="mode_of_delivery" required>
                                <option value="3">เรียนที่สาขา (Face to Face)</option>
                                <option value="1">ออนไลน์ (Online)</option>
                                <option value="2">เรียนทางไกล (Distance Learning)</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="editCourseAbout" class="form-label">คำอธิบายสั้น</label>
                            <textarea class="form-control" id="editCourseAbout" name="about" rows="2" maxlength="1000"
                                placeholder="เช่น หลักสูตรคิดวิเคราะห์ ปูพื้นฐานและฝึกแก้โจทย์ปัญหา"></textarea>
                            <div class="form-text">แสดงเป็นจุดเด่นบรรทัดแรกบนการ์ดคอร์ส</div>
                        </div>

                        <div class="col-md-6">
                            <label for="editCourseOutcomes" class="form-label">ผลลัพธ์/จุดเด่นของคอร์ส</label>
                            <textarea class="form-control" id="editCourseOutcomes" name="outcomes" rows="3" maxlength="1000"
                                placeholder="เช่น พัฒนาการคิดวิเคราะห์ด้วยสื่อ CPA มาตรฐานสิงคโปร์"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label for="editCourseRequirements" class="form-label">สิ่งที่รวมในคอร์ส/เงื่อนไข</label>
                            <textarea class="form-control" id="editCourseRequirements" name="requirements" rows="3" maxlength="1000"
                                placeholder="เช่น คลาสกลุ่มเล็ก ดูแลใกล้ชิด และชุดสื่อการเรียน"></textarea>
                        </div>

                        <div class="col-12">
                            <label for="editCourseImage" class="form-label">ภาพหน้าปกคอร์ส</label>
                            <input type="file" class="form-control" id="editCourseImage" name="image"
                                accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">JPG, PNG หรือ WebP ขนาดไม่เกิน 5 MB — แนะนำอัตราส่วน 16:9 อย่างน้อย 1200×675 px</div>
                            <div id="courseImageMissingWarning" class="alert alert-warning py-2 mt-2 mb-0 d-none" role="alert">
                                ไม่พบไฟล์ภาพเดิมบนเซิร์ฟเวอร์ กรุณาอัปโหลดภาพใหม่ก่อนบันทึก
                            </div>
                            <img id="courseImagePreview" src="" alt="ตัวอย่างภาพหน้าปกคอร์ส"
                                class="img-fluid rounded border mt-2" style="width: 100%; max-height: 280px; object-fit: cover;">
                        </div>

                        <div class="col-12">
                            <span class="form-label d-block">สถานะคอร์ส <span class="text-danger">*</span></span>
                            <div class="d-flex flex-wrap gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="editStatusActive" name="status" value="1">
                                    <label class="form-check-label" for="editStatusActive">เปิดแสดงบนหน้าบ้าน</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="editStatusInactive" name="status" value="0">
                                    <label class="form-check-label" for="editStatusInactive">ปิดการแสดงผล</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" form="editCourseForm" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
            </div>
        </div>
    </div>
</div>
