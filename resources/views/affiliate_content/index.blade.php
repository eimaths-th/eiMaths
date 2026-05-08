@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Affiliate Content Management (จัดการสื่อการตลาด)</h3>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addContentModal">
                        <i class="fas fa-plus"></i> เพิ่มสื่อใหม่
                    </button>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <table class="table table-bordered table-striped" id="affiliate-content-table">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="15%">Preview</th>
                                <th width="20%">Title</th>
                                <th width="10%">Type</th>
                                <th width="25%">Description</th>
                                <th width="10%">Status</th>
                                <th width="15%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contents as $index => $content)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    @if($content->type === 'video')
                                        <video width="120" height="80" style="object-fit: cover; border-radius: 4px;">
                                            <source src="{{ asset($content->file_path) }}" type="video/mp4">
                                        </video>
                                    @else
                                        <img src="{{ asset($content->file_path) }}" alt="{{ $content->title }}"
                                             style="width: 120px; height: 80px; object-fit: cover; border-radius: 4px;">
                                    @endif
                                </td>
                                <td>{{ $content->title }}</td>
                                <td>
                                    <span class="badge {{ $content->type === 'video' ? 'bg-danger' : 'bg-info' }}">
                                        {{ ucfirst($content->type) }}
                                    </span>
                                </td>
                                <td>{{ Str::limit($content->description, 80) }}</td>
                                <td>
                                    <span class="badge {{ $content->status === 'enabled' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $content->status === 'enabled' ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                                    </span>
                                </td>
                                <td>
                                    {{-- ปุ่มเปลี่ยนสถานะ --}}
                                    <button class="btn btn-sm {{ $content->status === 'enabled' ? 'btn-warning' : 'btn-success' }} toggle-status-btn"
                                            data-id="{{ $content->id }}">
                                        <i class="fas {{ $content->status === 'enabled' ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                    </button>
                                    {{-- ปุ่มลบ --}}
                                    <form action="{{ route('affiliate-content-manage.destroy', $content->id) }}" method="POST"
                                          style="display:inline;" onsubmit="return confirm('คุณต้องการลบสื่อนี้ใช่หรือไม่?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal: เพิ่มสื่อใหม่ --}}
<div class="modal fade" id="addContentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('affiliate-content-manage.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">เพิ่มสื่อการตลาด Affiliate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">ชื่อสื่อ (Title) <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required placeholder="เช่น โปรโมชั่นเรียน eiMaths ลด 50%">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ประเภท (Type) <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required id="content_type_select">
                            <option value="image">รูปภาพ (Image)</option>
                            <option value="video">วิดีโอ (Video)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ไฟล์ <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" required
                               accept="image/jpeg,image/png,image/jpg,image/gif,image/webp,video/mp4,video/mov,video/avi">
                        <small class="text-muted">รูปภาพ: JPG, PNG, GIF, WebP | วิดีโอ: MP4, MOV, AVI (สูงสุด 50MB)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">คำอธิบาย / Caption ตัวอย่าง</label>
                        <textarea name="description" class="form-control" rows="3"
                                  placeholder="ข้อความที่จะแนะนำให้ Affiliate ใช้เป็นแม่แบบ"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> อัปโหลด</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // เปลี่ยนสถานะ enable/disable ผ่าน AJAX
    $('.toggle-status-btn').click(function() {
        var btn = $(this);
        var content_id = btn.data('id');

        $.ajax({
            url: '{{ route("affiliate-content-manage.toggle-status") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: content_id
            },
            success: function(response) {
                location.reload();
            },
            error: function() {
                alert('เกิดข้อผิดพลาด กรุณาลองใหม่');
            }
        });
    });
});
</script>
@endsection
