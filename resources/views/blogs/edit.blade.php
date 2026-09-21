@extends('layouts.app')

@section('content')
<div class="container p-3">
    <h1>{{ __('content-management.blog.form.edit_title') }}</h1>
    @if (Session::has('error'))
        <script>
            toastr.error('{{ Session::get('error') }}', {
                timeOut: 5000
            });
        </script>
    @endif
    <form id="blog-form" action="{{ route('blogs.update', $blog) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="title">{{ __('content-management.blog.form.title') }}</label>
            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $blog->title) }}" required>
            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="slug">{{ __('content-management.blog.form.slug') }}</label>
            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $blog->slug) }}">
            @error('slug')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="thumbnail">{{ __('content-management.blog.form.thumbnail') }}</label>
            <input type="file" class="d-flex justify-content-center align-items-center @error('thumbnail') is-invalid @enderror" id="thumbnail" name="thumbnail" accept="image/png, image/jpg, image/jpeg">
            @error('thumbnail')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            @if($blog->thumbnail)
                <img class="mt-3" id="thumbnail_preview" src="{{ asset('storage/' . $blog->thumbnail) }}" alt="{{ __('content-management.blog.form.thumbnail_preview') }}" width="25%" style="aspect-ratio: 16/9; object-fit: cover; border-radius: 12px;">
            @else
                <img class="d-none mt-3" id="thumbnail_preview" alt="{{ __('content-management.blog.form.thumbnail_preview') }}" width="25%" style="aspect-ratio: 16/9; object-fit: cover; border-radius: 12px;">
            @endif
            <div class="form-text text-danger">{{ __('content-management.blog.form.allowed_file_types') }}</div>
            <div class="form-text text-danger">{{ __('content-management.blog.form.max_file_size') }}</div>
        </div>
        <div class="form-group">
            <div class="d-flex justify-content-between">
                <label for="content">{{ __('content-management.blog.form.content') }}</label>
                <div id="upload-status-container" class="text-sm"></div>
            </div>
            <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="10">{{ old('content', $blog->content) }}</textarea>
            @error('content')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="meta_description">{{ __('content-management.blog.form.meta_description') }}</label>
            <input type="text" class="form-control @error('meta_description') is-invalid @enderror" id="meta_description" name="meta_description" value="{{ old('meta_description', $blog->meta_description) }}">
            @error('meta_description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="meta_keywords">{{ __('content-management.blog.form.meta_keywords') }}</label>
            <input type="text" class="form-control @error('meta_keywords') is-invalid @enderror" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $blog->meta_keywords) }}">
            @error('meta_keywords')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="pen_name">{{ __('content-management.blog.form.pen_name') }}</label>
            <input type="text" class="form-control @error('pen_name') is-invalid @enderror" id="pen_name" name="pen_name" value="{{ old('pen_name', $blog->pen_name) }}">
            @error('pen_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-check mb-3">
            <input type="hidden" name="is_published" value="0"> <!-- Hidden field for unchecked checkbox -->
            <input type="checkbox" class="form-check-input @error('is_published') is-invalid @enderror" id="is_published" name="is_published" value="1" {{ old('is_published', $blog->is_published) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_published">{{ __('content-management.blog.form.publish_now') }}</label>
            @error('is_published')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary">{{ __('content-management.blog.form.update') }}</button>
    </form>
</div>
@endsection

@section('script')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">
<style>
    /* Show H4–H6 indicators in the dropdown like H1–H3 */
    .EasyMDEContainer .editor-toolbar .fa-header { position: relative; }
    .EasyMDEContainer .editor-toolbar .heading-4 .fa-header::after { content: '4' !important; font-size: 7px; position: absolute; top: 7px; right: -5px; }
    .EasyMDEContainer .editor-toolbar .heading-5 .fa-header::after { content: '5' !important; font-size: 7px; position: absolute; top: 7px; right: -5px; }
    .EasyMDEContainer .editor-toolbar .heading-6 .fa-header::after { content: '6' !important; font-size: 7px; position: absolute; top: 7px; right: -5px; }
    
    /* Font size display button styles */
    .EasyMDEContainer .editor-toolbar a.font-size-display {
        font-family: Arial, sans-serif;
        font-size: 12px;
        width: auto;
        min-width: 50px;
        text-align: center;
        padding: 0 8px;
        pointer-events: none;
    }
    
    /* Remove hover effect from font size display */
    .EasyMDEContainer .editor-toolbar a.font-size-display:hover {
        background: transparent;
        border-color: transparent;
    }
    /* Fallback: if class not present on anchor, use title attribute */
    .EasyMDEContainer .editor-toolbar .dropdown-menu a[title="Heading 4"] .fa-header::after { content: '4' !important; font-size: 7px; position: absolute; top: 7px; right: -5px; }
    .EasyMDEContainer .editor-toolbar .dropdown-menu a[title="Heading 5"] .fa-header::after { content: '5' !important; font-size: 7px; position: absolute; top: 7px; right: -5px; }
    .EasyMDEContainer .editor-toolbar .dropdown-menu a[title="Heading 6"] .fa-header::after { content: '6' !important; font-size: 7px; position: absolute; top: 7px; right: -5px; }
</style>
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
            const decoded_value = decodeURIComponent(pen_name_from_cookie);
            if (decoded_value) {
                pen_name_input.value = decoded_value;
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
                    title: @json(__('content-management.blog.editor.new_line')),
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
                    title: @json(__('content-management.blog.editor.underline')),
                },
                {
                    name: 'mark',
                    action: function(editor) {
                        const cm = editor.codemirror;
                        const selection = cm.getSelection();
                        cm.replaceSelection('<mark>' + selection + '</mark>');
                        const cursor = cm.getCursor();
                        const line = cm.getLine(cursor.line);
                        const mark_pos = line.indexOf('<mark>');
                        if (mark_pos >= 0) {
                            cm.setCursor({
                                line: cursor.line,
                                ch: mark_pos + 6
                            });
                            cm.focus();
                        }
                    },
                    className: 'fa fa-highlighter',
                    title: @json(__('content-management.blog.editor.mark')),
                },
                '|',
                {
                    name: 'headings',
                    className: 'fa fa-header',
                    title: @json(__('content-management.blog.editor.headings')),
                    children: [
                        {
                            name: 'heading-1',
                            action: function(editor) {
                                const cm = editor.codemirror;
                                const selection = cm.getSelection();
                                cm.replaceSelection('# ' + selection);
                                const cursor = cm.getCursor();
                                cm.setCursor({
                                    line: cursor.line,
                                    ch: cursor.ch + 2
                                });
                                cm.focus();
                            },
                            className: 'fa fa-header heading-1',
                            title: @json(__('content-management.blog.editor.heading', ['level' => 1])),
                        },
                        {
                            name: 'heading-2',
                            action: function(editor) {
                                const cm = editor.codemirror;
                                const selection = cm.getSelection();
                                cm.replaceSelection('## ' + selection);
                                const cursor = cm.getCursor();
                                cm.setCursor({
                                    line: cursor.line,
                                    ch: cursor.ch + 3
                                });
                                cm.focus();
                            },
                            className: 'fa fa-header heading-2',
                            title: @json(__('content-management.blog.editor.heading', ['level' => 2])),
                        },
                        {
                            name: 'heading-3',
                            action: function(editor) {
                                const cm = editor.codemirror;
                                const selection = cm.getSelection();
                                cm.replaceSelection('### ' + selection);
                                const cursor = cm.getCursor();
                                cm.setCursor({
                                    line: cursor.line,
                                    ch: cursor.ch + 4
                                });
                                cm.focus();
                            },
                            className: 'fa fa-header heading-3',
                            title: @json(__('content-management.blog.editor.heading', ['level' => 3])),
                        },
                        {
                            name: 'heading-4',
                            action: function(editor) {
                                const cm = editor.codemirror;
                                const selection = cm.getSelection();
                                cm.replaceSelection('#### ' + selection);
                                const cursor = cm.getCursor();
                                cm.setCursor({
                                    line: cursor.line,
                                    ch: cursor.ch + 5
                                });
                                cm.focus();
                            },
                            className: 'fa fa-header heading-4',
                            title: @json(__('content-management.blog.editor.heading', ['level' => 4])),
                        },
                        {
                            name: 'heading-5',
                            action: function(editor) {
                                const cm = editor.codemirror;
                                const selection = cm.getSelection();
                                cm.replaceSelection('##### ' + selection);
                                const cursor = cm.getCursor();
                                cm.setCursor({
                                    line: cursor.line,
                                    ch: cursor.ch + 6
                                });
                                cm.focus();
                            },
                            className: 'fa fa-header heading-5',
                            title: @json(__('content-management.blog.editor.heading', ['level' => 5])),
                        },
                        {
                            name: 'heading-6',
                            action: function(editor) {
                                const cm = editor.codemirror;
                                const selection = cm.getSelection();
                                cm.replaceSelection('###### ' + selection);
                                const cursor = cm.getCursor();
                                cm.setCursor({
                                    line: cursor.line,
                                    ch: cursor.ch + 7
                                });
                                cm.focus();
                            },
                            className: 'fa fa-header heading-6',
                            title: @json(__('content-management.blog.editor.heading', ['level' => 6])),
                        }
                    ],
                },
                '|',
                {
                    name: 'text-color',
                    action: function(editor) {
                        const cm = editor.codemirror;
                        const color_picker = document.createElement('input');
                        color_picker.type = 'color';
                        color_picker.value = '#000000';
                        color_picker.style.position = 'absolute';
                        color_picker.style.left = '-1000px';
                        document.body.appendChild(color_picker);
                        color_picker.addEventListener('change', function() {
                            const selection = cm.getSelection();
                            const color = this.value;
                            cm.replaceSelection('<span style="color:' + color + '">' + selection + '</span>');
                            const cursor = cm.getCursor();
                            const line = cm.getLine(cursor.line);
                            const span_pos = line.indexOf('<span style="color:' + color + '">');
                            if (span_pos >= 0) {
                                cm.setCursor({
                                    line: cursor.line,
                                    ch: span_pos + ('<span style="color:' + color + '">').length
                                });
                            }
                            document.body.removeChild(this);
                            cm.focus();
                        });
                        color_picker.click();
                    },
                    className: 'fa fa-tint',
                    title: @json(__('content-management.blog.editor.text_color')),
                },
                {
                    name: 'background-color',
                    action: function(editor) {
                        const cm = editor.codemirror;
                        const color_picker = document.createElement('input');
                        color_picker.type = 'color';
                        color_picker.value = '#FFFF00';
                        color_picker.style.position = 'absolute';
                        color_picker.style.left = '-1000px';
                        document.body.appendChild(color_picker);
                        color_picker.addEventListener('change', function() {
                            const selection = cm.getSelection();
                            const color = this.value;
                            const cursor = cm.getCursor();
                            cm.replaceSelection('<span style="background-color:' + color + '">' + selection + '</span>');
                            const line = cm.getLine(cursor.line);
                            const span_pos = line.indexOf('<span style="background-color:' + color + '">');
                            if (span_pos >= 0) {
                                cm.setCursor({
                                    line: cursor.line,
                                    ch: span_pos + ('<span style="background-color:' + color + '">').length
                                });
                            }
                            document.body.removeChild(this);
                            cm.focus();
                        });
                        color_picker.click();
                    },
                    className: 'fa fa-paint-brush',
                    title: @json(__('content-management.blog.editor.background_color')),
                },
                '|',
                {
                    name: 'font-size-decrease',
                    action: function(editor) {
                        const cm = editor.codemirror;
                        const wrapper = cm.getWrapperElement();
                        const current_size = parseInt(window.getComputedStyle(wrapper).fontSize);
                        const new_size = Math.max(current_size - 2, 10);
                        wrapper.style.fontSize = new_size + 'px';
                        document.querySelector('.font-size-display').textContent = new_size + 'px';
                    },
                    className: 'fa fa-minus',
                    title: @json(__('content-management.blog.editor.decrease_font_size')),
                },
                {
                    name: 'font-size-display',
                    action: function() {
                    },
                    className: 'font-size-display',
                    text: '16px',
                    title: @json(__('content-management.blog.editor.font_size')),
                },
                {
                    name: 'font-size-increase',
                    action: function(editor) {
                        const cm = editor.codemirror;
                        const wrapper = cm.getWrapperElement();
                        const current_size = parseInt(window.getComputedStyle(wrapper).fontSize);
                        const new_size = Math.min(current_size + 2, 48);
                        wrapper.style.fontSize = new_size + 'px';
                        document.querySelector('.font-size-display').textContent = new_size + 'px';
                    },
                    className: 'fa fa-plus',
                    title: @json(__('content-management.blog.editor.increase_font_size')),
                },
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
                    title: @json(__('content-management.blog.editor.video')),
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
                'heading-4': 'Ctrl-Alt-4',
                'heading-5': 'Ctrl-Alt-5',
                'heading-6': 'Ctrl-Alt-6',
                'text-color': 'Ctrl-Alt-Z',
                'background-color': 'Ctrl-Alt-X',
                'font-size-decrease': 'Ctrl-Alt-Down',
                'font-size-increase': 'Ctrl-Alt-Up',
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
                        upload_status.html(@json(__('content-management.blog.editor.upload_success'))).css('background-color', '#d4edda').css('color', '#155724').css('border-color', '#c3e6cb');
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
                        upload_status.html(@json(__('content-management.blog.editor.upload_failed'))).css('background-color', '#f8d7da').css('color', '#721c24').css('border-color', '#f5c6cb');
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
            },
            paste: {
                cleanPastedHTML: false,
                forcePlainText: false
            }
        });

                // ตั้งค่าขนาดฟอนต์เริ่มต้นหลังจากสร้าง EasyMDE
        setTimeout(function() {
            const cm = easy_mde.codemirror;
            const wrapper = cm.getWrapperElement();
            const default_font_size = 16; // ขนาดฟอนต์เริ่มต้น 16px ตามรูปตัวอย่าง
            wrapper.style.fontSize = default_font_size + 'px';
            
            // อัพเดทการแสดงผลขนาดฟอนต์ปัจจุบัน
            const font_size_display = document.querySelector('.font-size-display');
            if (font_size_display) {
                font_size_display.textContent = default_font_size + 'px';
            }
        }, 100);

        document.getElementById('thumbnail').addEventListener('change', function() {
            const file = this.files[0];
            const maxSize = 5 * 1024 * 1024; // 5MB in bytes

            if (file && file.size > maxSize) {
                alert(@json(__('content-management.blog.editor.file_too_large')));
                this.value = '';
            }
        });

        // บันทึก pen_name ใน cookie เมื่อส่งฟอร์ม
        const blog_form = document.querySelector('#blog-form'); // ระบุ ID ของฟอร์มให้ชัดเจน
        
        if (blog_form) {
            console.log('Found blog form:', blog_form); // เพิ่ม debug log
            
            blog_form.addEventListener('submit', function(event) {
                console.log('Form submitted'); // เพิ่ม debug log
                
                const pen_name_input = document.getElementById('pen_name');
                if (pen_name_input && pen_name_input.value) {
                    console.log('Saving pen_name to cookie:', pen_name_input.value); // เพิ่ม debug log
                    
                    const expiry_date = new Date();
                    expiry_date.setTime(expiry_date.getTime() + (3 * 365 * 24 * 60 * 60 * 1000)); // 3 ปี
                    document.cookie = `blog_pen_name=${encodeURIComponent(pen_name_input.value)}; expires=${expiry_date.toUTCString()}; path=/`;
                    
                    // ทดสอบว่าบันทึก cookie สำเร็จ
                    console.log('Cookie set:', document.cookie);
                }
            });
            
            // เพิ่มการบันทึกทันทีเมื่อพิมพ์เสร็จ
            const pen_name_input = document.getElementById('pen_name');
            if (pen_name_input) {
                pen_name_input.addEventListener('change', function() {
                    if (this.value) {
                        const expiry_date = new Date();
                        expiry_date.setTime(expiry_date.getTime() + (3 * 365 * 24 * 60 * 60 * 1000)); // 3 ปี
                        document.cookie = `blog_pen_name=${encodeURIComponent(this.value)}; expires=${expiry_date.toUTCString()}; path=/`;
                        console.log('Pen name saved on change:', this.value);
                    }
                });
            }
        } else {
            console.error('Blog form not found!');
        }
    });
</script>
@endsection
