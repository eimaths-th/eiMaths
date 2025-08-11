@extends('layouts.app')

@section('content')
<div class="container p-3">
    <h1>Edit Blog</h1>
    @if (Session::has('error'))
        <script>
            toastr.error('{{ Session::get('error') }}', {
                timeOut: 5000
            });
        </script>
    @endif
    <form action="{{ route('blogs.update', $blog) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $blog->title) }}" required>
            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="slug">Slug (Optional, will be generated if empty. Example: my-awesome-blog-post)</label>
            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $blog->slug) }}">
            @error('slug')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="thumbnail">Thumbnail (Optional)</label>
            <input type="file" class="d-flex justify-content-center align-items-center @error('thumbnail') is-invalid @enderror" id="thumbnail" name="thumbnail" accept="image/png, image/jpg, image/jpeg">
            @error('thumbnail')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            @if($blog->thumbnail)
                <img class="mt-3" id="thumbnail_preview" src="{{ asset('storage/' . $blog->thumbnail) }}" alt="Thumbnail Preview" width="25%" style="aspect-ratio: 16/9; object-fit: cover; border-radius: 12px;">
            @else
                <img class="d-none mt-3" id="thumbnail_preview" alt="Thumbnail Preview" width="25%" style="aspect-ratio: 16/9; object-fit: cover; border-radius: 12px;">
            @endif
            <div class="form-text text-danger">* Allowed file types: png, jpg, jpeg</div>
            <div class="form-text text-danger">* Max file size: 5MB</div>
        </div>
        <div class="form-group">
            <div class="d-flex justify-content-between">
                <label for="content">Content</label>
                <div id="upload-status-container" class="text-sm"></div>
            </div>
            <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="10">{{ old('content', $blog->content) }}</textarea>
            @error('content')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="meta_description">Meta Description</label>
            <input type="text" class="form-control @error('meta_description') is-invalid @enderror" id="meta_description" name="meta_description" value="{{ old('meta_description', $blog->meta_description) }}">
            @error('meta_description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="meta_keywords">Meta Keywords</label>
            <input type="text" class="form-control @error('meta_keywords') is-invalid @enderror" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $blog->meta_keywords) }}">
            @error('meta_keywords')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="pen_name">Pen Name (Optional)</label>
            <input type="text" class="form-control @error('pen_name') is-invalid @enderror" id="pen_name" name="pen_name" value="{{ old('pen_name', $blog->pen_name) }}">
            @error('pen_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-check mb-3">
            <input type="hidden" name="is_published" value="0"> <!-- Hidden field for unchecked checkbox -->
            <input type="checkbox" class="form-check-input @error('is_published') is-invalid @enderror" id="is_published" name="is_published" value="1" {{ old('is_published', $blog->is_published) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_published">Publish Now</label>
            @error('is_published')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">Update Blog</button>
    </form>
</div>
@endsection

@section('script')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">
<script src="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.js"></script>
<script>
    function GetCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const pen_name_input = document.getElementById('pen_name');
        if (pen_name_input && !pen_name_input.value) {
            const pen_name_from_cookie = GetCookie('blog_pen_name');
            if (pen_name_from_cookie) {
                pen_name_input.value = pen_name_from_cookie;
            }
        }

        const easy_mde = new EasyMDE({
            element: document.getElementById('content'),
            toolbar: [{
                    name: 'newline',
                    action: function(editor) {
                        const cm = editor.codemirror;
                        const selection = cm.getSelection();
                        cm.replaceSelection('\n<br>\n');
                        const cursor = cm.getCursor();
                        const line = cm.getLine(cursor.line);
                        const newline_pos = line.indexOf('<br>');
                        if (newline_pos >= 0) {
                            cm.setCursor({
                                line: cursor.line,
                                ch: newline_pos + 4
                            });
                            cm.focus();
                        }
                    },
                    className: 'fa fa-grip-lines',
                    title: 'New Line',
                },
                'bold', 'italic', 'strikethrough',
                {
                    name: 'underline',
                    action: function(editor) {
                        const cm = editor.codemirror;
                        const selection = cm.getSelection();
                        cm.replaceSelection('<u>' + selection + '</u>');
                        const cursor = cm.getCursor();
                        const line = cm.getLine(cursor.line);
                        const underline_pos = line.indexOf('<u>');
                        if (underline_pos >= 0) {
                            cm.setCursor({
                                line: cursor.line,
                                ch: underline_pos + 3
                            });
                            cm.focus();
                        }
                    },
                    className: 'fa fa-underline',
                    title: 'Underline',
                },
                '|',
                'heading-1', 'heading-2', 'heading-3',
                '|',
                'quote', 'unordered-list', 'ordered-list',
                '|',
                'link', 'image', {
                    name: 'video',
                    action: function(editor) {
                        const cm = editor.codemirror;
                        const selection = cm.getSelection();
                        cm.replaceSelection('<video src="' + selection + '"></video>');
                        const cursor = cm.getCursor();
                        const line = cm.getLine(cursor.line);
                        const video_pos = line.indexOf('src="');
                        if (video_pos >= 0) {
                            cm.setCursor({
                                line: cursor.line,
                                ch: video_pos + 5
                            });
                            cm.focus();
                        }
                    },
                    className: 'fa fa-video',
                    title: 'Video',
                },
                '|',
                'preview', 'side-by-side', 'fullscreen',
            ],
            shortcuts: {
                newline: 'Ctrl-Alt-N',
                bold: 'Ctrl-Alt-B',
                italic: 'Ctrl-Alt-I',
                strikethrough: 'Ctrl-Alt-S',
                underline: 'Ctrl-Alt-U',
                video: 'Ctrl-Alt-V',
                'heading-1': 'Ctrl-Alt-1',
                'heading-2': 'Ctrl-Alt-2',
                'heading-3': 'Ctrl-Alt-3',
                quote: 'Ctrl-Alt-Q',
                'unordered-list': 'Ctrl-Alt-U',
                'ordered-list': 'Ctrl-Alt-O',
                link: 'Ctrl-Alt-L',
                image: 'Ctrl-Alt-M',
                video: 'Ctrl-Alt-V',
                preview: 'Ctrl-Alt-P',
            },
            uploadImage: true,
            imageUploadFunction: function(file, onSuccess, onError) {
                const upload_status_container = $('#upload-status-container');
                const upload_status = $('<div class="upload-status" style="background-color: #f8f9fa; border: 1px solid #ddd; color: #333; padding: 0px 6px; border-radius: 6px; font-size: 12px;">กำลังอัปโหลด... <span class="progress">0%</span></div>');
                upload_status_container.html(upload_status);
                const form_data = new FormData();
                form_data.append('image', file);
                $.ajax({
                    url: '{{ route("upload.image") }}',
                    type: 'POST',
                    data: form_data,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    xhr: function() {
                        const xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function(evt) {
                            if (evt.lengthComputable) {
                                const percent_complete = Math.round((evt.loaded / evt.total) * 100);
                                $('.upload-status .progress').text(percent_complete + '%');
                            }
                        }, false);
                        return xhr;
                    },
                    success: function(response) {
                        upload_status.html('อัปโหลดรูปภาพสำเร็จ').css('background-color', '#d4edda').css('color', '#155724').css('border-color', '#c3e6cb');
                        setTimeout(function() {
                            upload_status.fadeOut(function() {
                                upload_status_container.empty();
                            });
                        }, 2000);
                        onSuccess(response.url);
                        setTimeout(function() {
                            const cm = easy_mde.codemirror;
                            const text = cm.getValue();
                            const cursor = cm.getCursor();
                            const line = cm.getLine(cursor.line);
                            const img_pos = line.indexOf('![]');
                            if (img_pos >= 0) {
                                cm.setCursor({
                                    line: cursor.line,
                                    ch: img_pos + 2
                                });
                                cm.focus();
                            }
                        }, 100);
                        toastr.success('อัปโหลดรูปภาพสำเร็จ');
                    },
                    error: function(error) {
                        upload_status.html('อัปโหลดรูปภาพไม่สำเร็จ').css('background-color', '#f8d7da').css('color', '#721c24').css('border-color', '#f5c6cb');
                        setTimeout(function() {
                            upload_status.fadeOut(function() {
                                upload_status_container.empty();
                            });
                        }, 2000);
                        onError(error.responseJSON?.message || 'เกิดข้อผิดพลาดในการอัปโหลดรูปภาพ');
                        toastr.error('เกิดข้อผิดพลาดในการอัปโหลดรูปภาพ');
                    }
                });
            },
            imagePathAbsolute: true,
            status: ['autosave', 'lines', 'words', 'cursor'],
            renderingConfig: {
                singleLineBreaks: false,
                codeSyntaxHighlighting: true,
            }
        });

        document.getElementById('thumbnail').addEventListener('change', function() {
            const file = this.files[0];
            const maxSize = 5 * 1024 * 1024; // 5MB in bytes

            if (file && file.size > maxSize) {
                alert('ไฟล์มีขนาดใหญ่เกิน 5MB');
                this.value = '';
            }
        });

        const blog_form = document.querySelector('form');
        if (blog_form) {
            blog_form.addEventListener('submit', function() {
                const pen_name_input = document.getElementById('pen_name');
                if (pen_name_input && pen_name_input.value) {
                    const expiry_date = new Date();
                    expiry_date.setTime(expiry_date.getTime() + (30 * 24 * 60 * 60 * 1000));
                    document.cookie = `blog_pen_name=${encodeURIComponent(pen_name_input.value)}; expires=${expiry_date.toUTCString()}; path=/`;
                }
            });
        }
    });
</script>
@endsection
