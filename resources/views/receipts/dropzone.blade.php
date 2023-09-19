<script>
    var myDropzone = {};

    Dropzone.options.myAwesomeDropzone = {
        url: '{{ route('file.upload') }}',
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        paramName: "file", // ชื่อไฟล์ปลายทางเมื่อ upload แบบ mutiple จะเป็น array
        autoProcessQueue: true, // ใส่เพื่อไม่ให้อัพโหลดทันที หลังจากเลือกไฟล์
        uploadMultiple: true, // อัพโหลดไฟล์หลายไฟล์
        parallelUploads: 2, // ให้ทำงานพร้อมกัน 10 ไฟล์
        maxFiles: 5, // ไฟล์สูงสุด 5 ไฟล์
        maxfilesexceeded: function(file) {
            //this.removeAllFiles();
            //this.addFile(file);
        },
        addRemoveLinks: true, // อนุญาตให้ลบไฟล์ก่อนการอัพโหลด
        maxFilesize: 10, // MB
        renameFile: function(file) {
            let newName = new Date().getTime() + '_' + file.name;
            file.newName = newName;
            return newName;
        },
        previewsContainer: "#dropzone_preview", // ระบุ element เป้าหลาย
        //previewTemplate: $('#template-preview').html(),
        dictRemoveFile: "Remove", // ชื่อ ปุ่ม remove
        dictCancelUpload: "Cancel", // ชื่อ ปุ่ม ยกเลิก
        dictDefaultMessage: "<img height='60' src='https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSNsug8XTE5KVJEMECVvm8p43BZTdvZExoQ9Q&usqp=CAU'><br><font size='3'>Select Receipt File.</font>", // ข้อความบนพื้นที่แสดงรูปจะแสดงหลังจากโหลดเพจเสร็จ
        dictFileTooBig: "ไม่อนุญาตให้อัพโหลดไฟล์เกิน 2 MB", //ข้อความแสดงเมื่อเลือกไฟล์ขนาดเกินที่กำหนด
        acceptedFiles: "image/*", // อนุญาตให้เลือกไฟล์ประเภทรูปภาพได้
        // The setting up of the dropzone
        init: function() {
            var myDropzone = this;
            // var mockFile = { name: 'public/images/infinite.jpg', size: 12345, type: 'image/jpeg' };
            // myDropzone.emit("addedfile", mockFile);
            // myDropzone.emit("success", mockFile);
            // myDropzone.emit("thumbnail", mockFile, "https://example.com")
            // myDropzone = this;
            this.on("addedfile", function(file) {
                $('#dropzone_preview').css("display", "block");
                //$('#infinite').append("<input type='text' class='form_none' name='imgFiles[]' value='" + file.name + "'/>");
                $('#create_form').append("<input type='text' id='" + file.newName +
                    "' class='form_none'  name='imgFiles[]' value='" + file.newName + "'/>");
                //class='form_none'
                file.previewElement.id = file.newName;
            }).on("removedfile", function(file) {

                //var name = file.name;
                var name = file.previewElement.id;

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    type: 'POST',
                    url: '{{ route('file.delete') }}',
                    data: {
                        name: name,
                    },
                    success: function(data) {
                        if (data !== 'none') {
                            console.log('success: ' + data);

                        }
                    }
                });

                var element = document.getElementById("create_form");
                var child = document.getElementById(name);
                element.removeChild(child);
                if (!document.getElementsByName('imgFiles[]').length) {
                    $('#dropzone_preview').css("display", "none");
                    /* $('#dropzone-att').addClass('form-focus-error');
                    $('#dropzone-att')
                        .find('.form-error-message').css("display", "block");
                    $('#dropzone-att').css('border', 'solid 1px red'); */
                }



            }).on("maxfilesexceeded", function(file) {
                alert('allow maximum 5 file');
                this.removeFile(file);
                /*  var lastFile = myDropzone.files[myDropzone.files.length - 1];
                 if (lastFile) {
                     myDropzone.removeFile(lastFile);
                     console.log("Max files exceeded. Removed the last file: " + lastFile.name);
                 } */
            }).on('complete', function(file) {
                let val = file.accepted;
                if (file.accepted == true) {
                    obj = JSON.parse(file.xhr.response);

                }
                let val1 = file.name;
                $('#dropzone_preview').css("display", "block");
                /* $('#dropzone-att').removeClass('form-focus-error');
                $('#dropzone-att')
                    .find('.form-error-message').css("display", "none");
                $('#dropzone-att').css('border', 'solid 1px green'); */
                if (document.getElementsByName('imgFiles[]').length) {

                }
            }).on("success", function(file) {
                //var responseText = file.id // or however you would point to your assigned file ID here;
                //console.log(response); // console should show the ID you pointed to
                // do stuff with file.id ...
            });
            /*this.on("addedfile", function(file) {

            }).on("removedfile", function(file) {

            }).on("thumbnail", function(file) {

            }).on("error", function(file) {

            }).on("processing", function(file) {

            }).on("uploadprogress", function(file) {

            });*/

        }
    }


    Dropzone.options.myAwesomeDropzone2 = {
        url: '{{ route('file.upload') }}',
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        paramName: "file", // ชื่อไฟล์ปลายทางเมื่อ upload แบบ mutiple จะเป็น array
        autoProcessQueue: true, // ใส่เพื่อไม่ให้อัพโหลดทันที หลังจากเลือกไฟล์
        uploadMultiple: true, // อัพโหลดไฟล์หลายไฟล์
        parallelUploads: 2, // ให้ทำงานพร้อมกัน 10 ไฟล์
        maxFiles: 5, // ไฟล์สูงสุด 5 ไฟล์
        maxfilesexceeded: function(file) {
            //this.removeAllFiles();
            //this.addFile(file);
        },
        addRemoveLinks: true, // อนุญาตให้ลบไฟล์ก่อนการอัพโหลด
        maxFilesize: 10, // MB
        renameFile: function(file) {
            let newName = new Date().getTime() + '_' + file.name;
            file.newName = newName;
            return newName;
        },
        previewsContainer: "#dropzone_preview2", // ระบุ element เป้าหลาย
        //previewTemplate: $('#template-preview').html(),
        dictRemoveFile: "Remove", // ชื่อ ปุ่ม remove
        dictCancelUpload: "Cancel", // ชื่อ ปุ่ม ยกเลิก
        dictDefaultMessage: "<img height='60' src='https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSNsug8XTE5KVJEMECVvm8p43BZTdvZExoQ9Q&usqp=CAU'><br><font size='3'>Select Receipt File.</font>", // ข้อความบนพื้นที่แสดงรูปจะแสดงหลังจากโหลดเพจเสร็จ
        dictFileTooBig: "ไม่อนุญาตให้อัพโหลดไฟล์เกิน 2 MB", //ข้อความแสดงเมื่อเลือกไฟล์ขนาดเกินที่กำหนด
        acceptedFiles: "image/*", // อนุญาตให้เลือกไฟล์ประเภทรูปภาพได้
        // The setting up of the dropzone
        init: function() {
            var myDropzone = this;
            // var mockFile = { name: 'public/images/infinite.jpg', size: 12345, type: 'image/jpeg' };
            // myDropzone.emit("addedfile", mockFile);
            // myDropzone.emit("success", mockFile);
            // myDropzone.emit("thumbnail", mockFile, "https://example.com")
            // myDropzone = this;
            this.on("addedfile", function(file) {
                $('#dropzone_preview2').css("display", "block");
                //$('#infinite').append("<input type='text' class='form_none' name='imgFiles[]' value='" + file.name + "'/>");
                $('#editdata').append("<input type='text' id='" + file.newName +
                    "' class='form_none' name='imgFiles2[]' value='" + file.newName + "'/>");
                file.previewElement.id = file.newName;
            }).on("removedfile", function(file) {
                //alert(file.id);
                //var name = file.name;
                var name = file.previewElement.id;

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    type: 'POST',
                    url: '{{ route('file.delete') }}',
                    data: {
                        name: name,
                    },
                    success: function(data) {
                        console.log('success: ' + data);

                    }
                });

                var element = document.getElementById("editdata");
                var child = document.getElementById(name);
                element.removeChild(child);
                if (!document.getElementsByName('imgFiles2[]').length) {
                    //alert();
                    $('#dropzone_preview2').css("display", "none");
                    //$('#dropzone-att').addClass('form-focus-error');
                    //$('#dropzone-att')
                    //    .find('.form-error-message').css("display", "block");
                    //$('#dropzone-att').css('border', 'solid 1px red');
                }

                //var _ref;
                //return (_ref = file.previewElement) != null ? _ref.parentNode.removeChild(file.previewElement) : void 0;

            }).on("maxfilesexceeded", function(file) {
                alert('allow maximum 5 file');
                this.removeFile(file);
            }).on('complete', function(file) {
                let val = file.accepted;
                if (file.accepted == true) {
                    obj = JSON.parse(file.xhr.response);
                    //file.previewElement.id = obj.success;
                    // console.log(obj.success);
                    //alert(obj.success);
                    //$('#infinite').append("<input type='text' id='" + obj.success +
                    //   "' class='form_none' name='imgFiles2[]' value='" + obj.success + "'/>");
                }
                let val1 = file.name;
                $('#dropzone_preview2').css("display", "block");
                //$('#dropzone-att').removeClass('form-focus-error');
                //$('#dropzone-att')
                //    .find('.form-error-message').css("display", "none");
                //$('#dropzone-att').css('border', 'solid 1px green');
                if (document.getElementsByName('imgFiles2[]').length) {
                    //$('#error_div').css('display', 'none');
                }
            }).on("success", function(file) {
                //var responseText = file.id // or however you would point to your assigned file ID here;
                //console.log(response); // console should show the ID you pointed to
                // do stuff with file.id ...
            });
            /*this.on("addedfile", function(file) {

            }).on("removedfile", function(file) {

            }).on("thumbnail", function(file) {

            }).on("error", function(file) {

            }).on("processing", function(file) {

            }).on("uploadprogress", function(file) {

            });*/

        }
    }
</script>
