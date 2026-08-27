<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">
<style>
    .EasyMDEContainer .editor-toolbar .fa-header {
        position: relative;
    }

    .EasyMDEContainer .editor-toolbar .heading-4 .fa-header::after {
        content: '4' !important;
        font-size: 7px;
        position: absolute;
        top: 7px;
        right: -5px;
    }

    .EasyMDEContainer .editor-toolbar .heading-5 .fa-header::after {
        content: '5' !important;
        font-size: 7px;
        position: absolute;
        top: 7px;
        right: -5px;
    }

    .EasyMDEContainer .editor-toolbar .heading-6 .fa-header::after {
        content: '6' !important;
        font-size: 7px;
        position: absolute;
        top: 7px;
        right: -5px;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editors = [];

        ['answer', 'answer_en'].forEach(function (element_id) {
            const answer_element = document.getElementById(element_id);

            if (!answer_element) {
                return;
            }

            const easy_mde = new EasyMDE({
            element: answer_element,
            toolbar: [
                {
                    name: 'newline',
                    action: function(editor) {
                        const cm = editor.codemirror;
                        const selection = cm.getSelection();
                        cm.replaceSelection('\n<br>\n' + selection);
                        cm.focus();
                    },
                    className: 'fa fa-grip-lines',
                    title: 'New Line',
                },
                'bold',
                'italic',
                'strikethrough',
                {
                    name: 'underline',
                    action: function(editor) {
                        const cm = editor.codemirror;
                        const selection = cm.getSelection();
                        cm.replaceSelection('<u>' + selection + '</u>');
                        cm.focus();
                    },
                    className: 'fa fa-underline',
                    title: 'Underline',
                },
                '|',
                {
                    name: 'headings',
                    className: 'fa fa-header',
                    title: 'Headings',
                    children: [
                        {
                            name: 'heading-1',
                            action: function(editor) {
                                const cm = editor.codemirror;
                                cm.replaceSelection('# ' + cm.getSelection());
                                cm.focus();
                            },
                            className: 'fa fa-header heading-1',
                            title: 'Heading 1',
                        },
                        {
                            name: 'heading-2',
                            action: function(editor) {
                                const cm = editor.codemirror;
                                cm.replaceSelection('## ' + cm.getSelection());
                                cm.focus();
                            },
                            className: 'fa fa-header heading-2',
                            title: 'Heading 2',
                        },
                        {
                            name: 'heading-3',
                            action: function(editor) {
                                const cm = editor.codemirror;
                                cm.replaceSelection('### ' + cm.getSelection());
                                cm.focus();
                            },
                            className: 'fa fa-header heading-3',
                            title: 'Heading 3',
                        },
                        {
                            name: 'heading-4',
                            action: function(editor) {
                                const cm = editor.codemirror;
                                cm.replaceSelection('#### ' + cm.getSelection());
                                cm.focus();
                            },
                            className: 'fa fa-header heading-4',
                            title: 'Heading 4',
                        },
                    ],
                },
                '|',
                'quote',
                'unordered-list',
                'ordered-list',
                '|',
                'link',
                'image',
                '|',
                'preview',
                'side-by-side',
                'fullscreen',
            ],
            uploadImage: true,
            imageUploadFunction: function(file, onSuccess, onError) {
                const upload_status_container = $('#upload-status-container-' + element_id);
                const upload_status = $('<div class="upload-status" style="background-color: #f8f9fa; border: 1px solid #ddd; color: #333; padding: 0px 6px; border-radius: 6px; font-size: 12px;">กำลังอัปโหลด... <span class="progress">0%</span></div>');
                const form_data = new FormData();

                upload_status_container.html(upload_status);
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
                        xhr.upload.addEventListener('progress', function(evt) {
                            if (evt.lengthComputable) {
                                const percent_complete = Math.round((evt.loaded / evt.total) * 100);
                                $('.upload-status .progress').text(percent_complete + '%');
                            }
                        }, false);
                        return xhr;
                    },
                    success: function(response) {
                        upload_status.html('อัปโหลดรูปภาพสำเร็จ')
                            .css('background-color', '#d4edda')
                            .css('color', '#155724')
                            .css('border-color', '#c3e6cb');

                        setTimeout(function() {
                            upload_status.fadeOut(function() {
                                upload_status_container.empty();
                            });
                        }, 2000);

                        onSuccess(response.url);
                        toastr.success('อัปโหลดรูปภาพสำเร็จ');
                    },
                    error: function(error) {
                        upload_status.html('อัปโหลดรูปภาพไม่สำเร็จ')
                            .css('background-color', '#f8d7da')
                            .css('color', '#721c24')
                            .css('border-color', '#f5c6cb');

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

            editors.push(easy_mde);

            setTimeout(function() {
                const wrapper = easy_mde.codemirror.getWrapperElement();
                wrapper.style.fontSize = '16px';
            }, 100);
        });

        const form = document.getElementById('answer')?.closest('form');
        if (form) {
            form.addEventListener('submit', function () {
                editors.forEach(function (editor) {
                    editor.codemirror.save();
                });
            });
        }
    });
</script>
