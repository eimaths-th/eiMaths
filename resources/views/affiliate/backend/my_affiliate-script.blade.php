<script src="{{asset('backend/js/datatable_extra.js')}}" type="application/javascript"></script>
<script src="{{asset('backend/js/plugin.js')}}" type="application/javascript"></script>

<script>
    if ($('#main-nav-for-chat').length) {} else {
        $('#main-content').append('<div id="main-nav-for-chat" style="visibility: hidden;"></div>');
    }

    if ($('#admin-visitor-area').length) {} else {
        $('#main-content').append('<div id="admin-visitor-area" style="visibility: hidden;"></div>');
    }
</script>

<script>
    function copyToClipboard(element) {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val($(element).val()).select();
        document.execCommand("copy");
        $temp.remove();

        // Change the icon and text to indicate success
        let button = document.getElementById('copyBtn');
        let icon = button.querySelector('i');

        icon.classList.remove('fa-copy');
        icon.classList.add('fa-check'); // Change icon to checkmark
        button.innerHTML = '<i class="fa fa-check"></i> Copied!';

        // Revert back after 2 seconds
        setTimeout(() => {
            icon.classList.remove('fa-check');
            icon.classList.add('fa-copy');
            button.innerHTML = '<i class="fa fa-copy"></i> Copy';
        }, 2000);
    }
</script>

<script>
    $(document).on('click', '.unread_notification', function(e) {
        e.preventDefault();
        $('.preloader').fadeIn('slow');
        let notification_id = $(this).attr('data-notification_id');
        let count_txt = $('.notificationCount');
        let count = count_txt.text();
        if (count > 0) {
            count--;
            count_txt.text(count);
        }
        $(this).closest('.single_notify').remove();


        let url = $('#url').val();

        let formData = {
            id: notification_id
        };
        $.ajax({
            type: "GET",
            data: formData,
            dataType: 'json',
            url: url + '/' + 'ajaxNotificationMakeRead',
            success: function(data) {
                if (data != '') {
                    window.location.href = data;

                } else {
                    $('.preloader').fadeOut('slow');
                }

            },
            error: function(error) {
                console.log('Error:', error);
            }
        });
    });
</script>


<script>
    // $('.Crm_table_active3').DataTable(dataTableOptions{
    //     scrollX: true,
    // });
    $('.Crm_table_active3').DataTable(dataTableOptions);
    // $('.Crm_table_active3').DataTable();
</script>

<script></script>
<script>
    setTimeout(function() {
        $('.preloader').fadeOut('slow', function() {
            // $(this).remove();
        });
    }, 0);
</script>

<script>
    if ($('#main-nav-for-chat').length) {} else {
        $('#main-content').append('<div id="main-nav-for-chat" style="display: none;"></div>');
    }

    if ($('#admin-visitor-area').length) {} else {
        $('#main-content').append('<div id="admin-visitor-area" style="visibility: hidden;"></div>');
    }
</script>

<script>
    //datatable caching
    $.fn.dataTable.pipeline = function(opts) {
        // Configuration options
        var conf = $.extend({
            pages: 5, // number of pages to cache
            url: '', // script url
            data: null, // function or object with parameters to send to the server
            // matching how `ajax.data` works in DataTables
            method: 'GET' // Ajax HTTP method
        }, opts);
        // Private variables for storing the cache
        var cacheLower = -1;
        var cacheUpper = null;
        var cacheLastRequest = null;
        var cacheLastJson = null;
        return function(request, drawCallback, settings) {
            var ajax = false;
            var requestStart = request.start;
            var drawStart = request.start;
            var requestLength = request.length;
            var requestEnd = requestStart + requestLength;

            if (settings.clearCache) {
                // API requested that the cache be cleared
                ajax = true;
                settings.clearCache = false;
            } else if (cacheLower < 0 || requestStart < cacheLower || requestEnd > cacheUpper) {
                // outside cached data - need to make a request
                ajax = true;
            } else if (JSON.stringify(request.order) !== JSON.stringify(cacheLastRequest.order) ||
                JSON.stringify(request.columns) !== JSON.stringify(cacheLastRequest.columns) ||
                JSON.stringify(request.search) !== JSON.stringify(cacheLastRequest.search)
            ) {
                // properties changed (ordering, columns, searching)
                ajax = true;
            }

            // Store the request for checking next time around
            cacheLastRequest = $.extend(true, {}, request);

            if (ajax) {
                // Need data from the server
                if (requestStart < cacheLower) {
                    requestStart = requestStart - (requestLength * (conf.pages - 1));

                    if (requestStart < 0) {
                        requestStart = 0;
                    }
                }
                cacheLower = requestStart;
                cacheUpper = requestStart + (requestLength * conf.pages);

                request.start = requestStart;
                request.length = requestLength * conf.pages;

                // Provide the same `data` options as DataTables.
                if (typeof conf.data === 'function') {
                    // As a function it is executed with the data object as an arg
                    // for manipulation. If an object is returned, it is used as the
                    // data object to submit
                    var d = conf.data(request);
                    if (d) {
                        $.extend(request, d);
                    }
                } else if ($.isPlainObject(conf.data)) {
                    // As an object, the data given extends the default
                    $.extend(request, conf.data);
                }

                return $.ajax({
                    "type": conf.method,
                    "url": conf.url,
                    "data": request,
                    "dataType": "json",
                    "cache": false,
                    "success": function(json) {
                        cacheLastJson = $.extend(true, {}, json);

                        if (cacheLower != drawStart) {
                            json.data.splice(0, drawStart - cacheLower);
                        }
                        if (requestLength >= -1) {
                            json.data.splice(requestLength, json.data.length);
                        }

                        drawCallback(json);
                    }
                });
            } else {
                var json = $.extend(true, {}, cacheLastJson);
                json.draw = request.draw; // Update the echo for each response
                json.data.splice(0, requestStart - cacheLower);
                json.data.splice(requestLength, json.data.length);

                drawCallback(json);
            }
        }
    };

    // Register an API method that will empty the pipelined data, forcing an Ajax
    // fetch on the next draw (i.e. `table.clearPipeline().draw()`)
    $.fn.dataTable.Api.register('clearPipeline()', function() {
        return this.iterator('table', function(settings) {
            settings.clearCache = true;
        });
    });
</script>

<script>
    $(function() {
        initFilePond();
    });

    function initFilePond() {
        FilePond.registerPlugin(FilePondPluginFileValidateType);

        let inputs = $('.filepond');
        const filePondInstances = [];
        inputs.each(function(i, obj) {
            let existingFileUrl = $(this).data('file');
            let fileType = $(this).data('type');
            let data_accepts = $(this).data('accepts') ?? "";
            let allowFileTypeValidation = false;
            let accepts = data_accepts != "" ? data_accepts.split(',') : '';
            if (accepts.length > 0) {
                allowFileTypeValidation = true;
            }
            const pond = FilePond.create(obj, {
                allowRevert: true,

                allowFileTypeValidation: allowFileTypeValidation,
                acceptedFileTypes: accepts,
                labelFileTypeNotAllowed: 'Invalid File Format',

                chunkUploads: true,
                'allowMultiple': $('#multipleForm').length ? true : false,
                server: {
                    url: 'http://127.0.0.1:8001/filepond/api/process',
                    headers: {
                        'X-CSRF-TOKEN': 'S2b9x1qm9PItqnuUewh2B7IN3c7KcABR0nbRYaFy'
                    }
                },
                files: existingFileUrl ? [{
                    source: existingFileUrl,
                    options: {
                        type: fileType ? fileType : 'local',
                    },
                }, ] : null,
                // Add any desired plugins
                plugins: [FilePondPluginFileValidateType],

            });
            if (existingFileUrl) {
                pond.setOptions({
                    data: {
                        fileUrl: existingFileUrl,
                    },
                });
            }
            filePondInstances.push(pond);
        });
    }
</script>
{{-- <script src="http://127.0.0.1:8001/chat/js/custom.js?v=3917"></script>
    <!-- Load Uppy JS bundle. -->
    <script src="http://127.0.0.1:8001/vendor/uppy/uppy.min.js"></script>
    <script src="http://127.0.0.1:8001/vendor/uppy/uppy.legacy.min.js"></script>
    <script src="http://127.0.0.1:8001/vendor/uppy/ru_RU.min.js"></script> --}}

<script src="{{ asset('chat/js/custom.js') }}{{ assetVersion() }}"></script>
<script src="{{ asset('vendor/uppy/uppy.min.js') }}"></script>
<script src="{{ asset('vendor/uppy/uppy.legacy.min.js') }}"></script>
<script src="{{ asset('vendor/uppy/ru_RU.min.js') }}"></script>

<script src="{{ asset('Modules/Affiliate/Resources/assets/js/infix_affiliate_link.js') }}"></script>
<script src="{{ asset('Modules/Affiliate/Resources/assets/js/balance_transfer.js') }}"></script>
<script src="{{ asset('Modules/Affiliate/Resources/assets/js/daterangepicker.min.js') }}"></script>




<script>
    (function($) {
        "use strict";
        $(document).ready(function() {
            $('input[name="date_range_filter"]').daterangepicker({
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment()
                        .subtract(1, 'month').endOf('month')
                    ],
                }

            }, function(start, end, label) {
                $('#start').val(start.format('YYYY-MM-DD'))
                $('#end').val(end.format('YYYY-MM-DD'))
            });
            $("#reset-date-filter").on('click', function() {
                let filterRange = $('input[name="date_range_filter"]').val();
                let formatDate = filterRange.split('-');
                let startDate = dateFormat(formatDate[0]);
                let endDate = dateFormat(formatDate[1]);
                var params = [
                    "startDate=" + startDate,
                    "endDate=" + endDate
                ];
                window.location.href = window.location.protocol + "//" + window.location.host +
                    window.location.pathname + '?' + params.join('&');
            });

            function dateFormat(date) {
                var newdate = new Date(date);
                var dd = ("0" + (newdate.getDate())).slice(-2);
                var mm = ("0" + (newdate.getMonth() + 1)).slice(-2);
                var y = newdate.getFullYear();
                return y + '-' + mm + '-' + dd;
            }

        });
    })(jQuery);
</script>
<script>
    $('.dataTables_length label select').niceSelect();
    $('.dataTables_length label .nice-select').addClass('dataTable_select');
    $(document).on('click', '.dataTables_length label .nice-select', function() {
        $(this).toggleClass('open_selectlist');
    })
</script>

<script type="text/javascript"></script>

<!-- Livewire Scripts -->
<script src="{{ asset('vendor/livewire/livewire.js?id=90730a3b0e7144480175') }}" data-turbo-eval="false"
    data-turbolinks-eval="false"></script>
{{-- <script data-turbo-eval="false" data-turbolinks-eval="false">
        if (window.livewire) {
            console.warn(
                'Livewire: It looks like Livewire\'s @livewireScripts JavaScript assets have already been loaded. Make sure you aren\'t loading them twice.'
                )
        }

        window.livewire = new Livewire();
        window.livewire.devTools(true);
        window.Livewire = window.livewire;
        window.livewire_app_url = 'https://ecommerce.eimaths-th.com';
        window.livewire_token = 'S2b9x1qm9PItqnuUewh2B7IN3c7KcABR0nbRYaFy';

        /* Make sure Livewire loads first. */
        if (window.Alpine) {
            /* Defer showing the warning so it doesn't get buried under downstream errors. */
            document.addEventListener("DOMContentLoaded", function() {
                setTimeout(function() {
                    console.warn(
                        "Livewire: It looks like AlpineJS has already been loaded. Make sure Livewire\'s scripts are loaded before Alpine.\\n\\n Reference docs for more info: http://laravel-livewire.com/docs/alpine-js"
                        )
                })
            });
        }

        /* Make Alpine wait until Livewire is finished rendering to do its thing. */
        window.deferLoadingAlpine = function(callback) {
            window.addEventListener('livewire:load', function() {
                callback();
            });
        };

        let started = false;

        window.addEventListener('alpine:initializing', function() {
            if (!started) {
                window.livewire.start();

                started = true;
            }
        });

        document.addEventListener("DOMContentLoaded", function() {
            if (!started) {
                window.livewire.start();

                started = true;
            }
        });
    </script> --}}
{{-- <script src="http://127.0.0.1:8001/js/alpine.min.js?v=8891"></script> --}}
<script src="{{ asset('js/alpine.min.js') }}{{ assetVersion() }}"></script>

<script>
    window.jsLang = function(key, replace) {
        let translation = true

        let json_file = window._translations;
        translation = json_file[key] ?
            json_file[key] :
            key


        $.each(replace, (value, key) => {
            translation = translation.replace(':' + key, value)
        })

        return translation
    }
</script>
<script>
    const Amaz = new Object();
    Amaz.data = {
        csrf: $('meta[name="_token"]').attr("content"),
        appUrl: 'http://127.0.0.1:8001',
        fileBaseUrl: 'http://127.0.0.1:8001' + '/'
    };
    Amaz.uploader = {
        data: {
            selectedFiles: [],
            selectedFilesObject: [],
            clickedForDelete: null,
            allFiles: [],
            multiple: false,
            type: "all",
            next_page_url: null,
            prev_page_url: null,
            for_name: ''
        },
        amazUppy: function() {
            if ($(".AmazUppyDragDrop").length > 0) {
                let uppy = new Uppy.Core({
                    autoProceed: true,
                    restrictions: {
                        maxFileSize: 200000000,
                        maxNumberOfFiles: 20,
                        minNumberOfFiles: 1,
                        // allowedFileTypes: ['image/*']
                    }
                });
                uppy.use(Uppy.Dashboard, {
                    target: ".AmazUppyDragDrop",
                    inline: true,
                    showLinkToFileUploadResult: false,
                    showProgressDetails: true,
                    hideCancelButton: true,
                    hidePauseResumeButton: true,
                    hideUploadButton: true,
                    proudlyDisplayPoweredByUppy: false,
                    locale: {
                        strings: {}
                    }
                });
                uppy.use(Uppy.XHRUpload, {
                    endpoint: 'http://127.0.0.1:8001/media-manager/create',
                    fieldName: "file",
                    formData: true,
                    headers: {
                        'X-CSRF-TOKEN': Amaz.data.csrf,
                    },
                });
                uppy.on("upload-success", function() {
                    Amaz.uploader.getAllUploads(
                        "http://127.0.0.1:8001/media-manager/get-files-modal"
                    );
                });
            }
        },
        getAllUploads: function(url, search_key = null, sort_key = null) {
            $("#all_files_div").html(
                '<div class="loader_media"><div class="hhhdots_1"></div></div>'
            );
            let params = {};
            if (search_key != null && search_key.length > 0) {
                params["search"] = search_key;
            }
            if (sort_key != null && sort_key.length > 0) {
                params["sort"] = sort_key;
            } else {
                params["sort"] = 'newest';
            }
            $.get(url, params, function(data, status) {

                if (typeof data == 'string') {
                    data = JSON.parse(data);
                }
                Amaz.uploader.data.allFiles = data.files.data;
                Amaz.uploader.allowedFileType();
                Amaz.uploader.addSelectedValue();
                Amaz.uploader.addHiddenValue();
                Amaz.uploader.updateUploaderFiles();
                if (data.files.next_page_url != null) {
                    Amaz.uploader.data.next_page_url = data.files.next_page_url;
                    $("#uploader_next_btn").removeAttr("disabled");
                } else {
                    $("#uploader_next_btn").attr("disabled", true);
                }
                if (data.files.prev_page_url != null) {
                    Amaz.uploader.data.prev_page_url = data.files.prev_page_url;
                    $("#uploader_prev_btn").removeAttr("disabled");
                } else {
                    $("#uploader_prev_btn").attr("disabled", true);
                }
            });
        },
        allowedFileType: function() {
            if (Amaz.uploader.data.type !== "all") {
                let type = Amaz.uploader.data.type.split(',')
                Amaz.uploader.data.allFiles = Amaz.uploader.data.allFiles.filter(
                    function(item) {
                        return type.includes(item.type);
                    }
                );
            }
        },
        addHiddenValue: function() {
            for (let i = 0; i < Amaz.uploader.data.allFiles.length; i++) {
                Amaz.uploader.data.allFiles[i].aria_hidden = false;
            }
        },
        addSelectedValue: function() {
            for (let i = 0; i < Amaz.uploader.data.allFiles.length; i++) {
                if (
                    !Amaz.uploader.data.selectedFiles.includes(
                        Amaz.uploader.data.allFiles[i].id
                    )
                ) {
                    Amaz.uploader.data.allFiles[i].selected = false;
                } else {
                    Amaz.uploader.data.allFiles[i].selected = true;
                }
            }
        },
        updateUploaderSelected: function() {
            $(".upload_files_selected").html(
                Amaz.uploader.updateFileHtml(Amaz.uploader.data.selectedFiles)
            );
        },
        updateFileHtml: function(array) {
            let fileText = "";
            if (array.length > 1) {
                fileText = 'File Selected';
            } else {
                fileText = 'Files Selected';
            }
            return array.length + " " + fileText;
        },
        updateUploaderFiles: function() {
            $("#all_files_div").html(
                '<div class="loader_media"><div class="hhhdots_1"></div></div>'
            );

            let data = Amaz.uploader.data.allFiles;

            setTimeout(function() {
                $("#all_files_div").html(null);

                if (data.length > 0) {
                    for (let i = 0; i < data.length; i++) {
                        let thumb = "";
                        let hidden = "";
                        if (data[i].type === "image") {
                            if (data[i].storage == 'local') {
                                thumb =
                                    '<img src="' +
                                    Amaz.data.fileBaseUrl +
                                    data[i].file_name +
                                    '" class="">';
                            } else {
                                thumb =
                                    '<img src="' + data[i].file_name + '" class="">';
                            }

                        } else {
                            thumb = '<i class="ti-files"></i>';
                        }
                        let html = `
                            <div class="infixlms_file_body single_files" aria-hidden="${data[i].aria_hidden}" data-selected="${data[i].selected}">
                                <div class="modal_file_box" data-value="${data[i].id}">
                                    <div class="img-box">
                                        ${thumb}
                                    </div>
                                    <div class="infixlms_file_content-box">
                                        <div class="file-content-wrapper">
                                            <h5>${data[i].original_name}</h5>
                                            <p>${data[i].size} kb</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        $("#all_files_div").append(html);
                    }
                } else {
                    $("#all_files_div").html(
                        '<div class="align-items-center d-flex justify-content-center w-100"><div class="text-center"><h3>No files found</h3></div></div>'
                    );
                }
                Amaz.uploader.uploadSelect();
                // Amaz.uploader.deleteUploaderFile();
            }, 300);
        },
        searchUploaderFiles: function() {
            let timeout;
            $('[name="amaz_media_search"]').on("keyup", function() {
                let value = $(this).val();

                if (timeout) {
                    clearTimeout(timeout);
                }
                timeout = setTimeout(function() {
                    Amaz.uploader.getAllUploads(
                        "http://127.0.0.1:8001/media-manager/get-files-modal",
                        value,
                        $('[name="Amaz_media_sort"]').val()
                    );
                }, 300);
            });

        },
        sortUploaderFiles: function() {
            $('[name="Amaz_media_sort"]').on("change", function() {
                let value = $(this).val();
                Amaz.uploader.getAllUploads(
                    "http://127.0.0.1:8001/media-manager/get-files-modal",
                    $('[name="amaz_media_search"]').val(),
                    value
                );
            });
        },
        uploadSelect: function() {
            $(".modal_file_box").each(function() {
                let elem = $(this);
                elem.on("click", function(e) {
                    e.preventDefault();
                    let value = $(this).data("value");

                    let valueObject =
                        Amaz.uploader.data.allFiles[
                            Amaz.uploader.data.allFiles.findIndex(
                                (x) => x.id === value
                            )
                        ];

                    // elem.closest(".single_files").toggleAttr(
                    //     "data-selected",
                    //     "true",
                    //     "false"
                    // );
                    let closestSingleFiles = elem.closest(".single_files");
                    let isSelected = closestSingleFiles.attr("data-selected");

                    if (isSelected === "true") {
                        closestSingleFiles.attr("data-selected", "false");
                    } else {
                        closestSingleFiles.attr("data-selected", "true");
                    }
                    if (!Amaz.uploader.data.multiple) {
                        elem.closest(".single_files")
                            .siblings()
                            .attr("data-selected", "false");
                    }
                    if (!Amaz.uploader.data.selectedFiles.includes(value)) {
                        if (!Amaz.uploader.data.multiple) {
                            Amaz.uploader.data.selectedFiles = [];
                            Amaz.uploader.data.selectedFilesObject = [];
                        }
                        Amaz.uploader.data.selectedFiles.push(value);
                        Amaz.uploader.data.selectedFilesObject.push(valueObject);
                    } else {
                        Amaz.uploader.data.selectedFiles = Amaz.uploader.data.selectedFiles
                            .filter(
                                function(item) {
                                    return item !== value;
                                }
                            );
                        Amaz.uploader.data.selectedFilesObject = Amaz.uploader.data
                            .selectedFilesObject.filter(
                                function(item) {
                                    return item !== valueObject;
                                }
                            );
                    }
                    Amaz.uploader.addSelectedValue();
                    Amaz.uploader.updateUploaderSelected();
                });
            });
        },
        showSelectedFiles: function() {
            $('[name="selected_only"]').on("change", function() {
                if ($(this).is(":checked")) {
                    Amaz.uploader.data.allFiles = Amaz.uploader.data.selectedFilesObject;
                    Amaz.uploader.updateUploaderFiles();
                } else {
                    Amaz.uploader.getAllUploads(
                        "http://127.0.0.1:8001/media-manager/get-files-modal",
                        $('[name="amaz_media_search"]').val(),
                        $('[name="Amaz_media_sort"]').val()
                    );
                }
            });
        },
        clearUploaderSelected: function() {
            $(".reset_selected").on("click", function(e) {
                e.preventDefault();
                Amaz.uploader.data.selectedFiles = [];
                Amaz.uploader.addSelectedValue();
                Amaz.uploader.addHiddenValue();
                Amaz.uploader.resetFilter();
                // Amaz.uploader.updateUploaderSelected();
                // Amaz.uploader.updateUploaderFiles();

                Amaz.uploader.getAllUploads(
                    "http://127.0.0.1:8001/media-manager/get-files-modal",
                    $('[name="amaz_media_search"]').val(),
                    $('[name="Amaz_media_sort"]').val()
                );

            });
        },
        resetFilter: function() {
            $('[name="amaz_media_search"]').val("");
            $('[name="selected_only"]').prop("checked", false);
            $('[name="Amaz_media_sort"] option[value=newest]').prop(
                "selected",
                true
            );
            $('[name="Amaz_media_sort"]').niceSelect('update');
        },


        trigger: function(
            elem = null,
            from = "",
            type = "all",
            selected = "",
            multiple = false,
            callback = null
        ) {
            var elem = $(elem);
            var multiple = multiple;
            var type = type;
            var oldSelectedFiles = selected;
            if (oldSelectedFiles !== "") {
                Amaz.uploader.data.selectedFiles = oldSelectedFiles
                    .split(",")
                    .map(Number);

            } else {
                Amaz.uploader.data.selectedFiles = [];
            }
            if ("undefined" !== typeof type && type.length > 0) {
                Amaz.uploader.data.type = type;
            }
            if (multiple) {
                Amaz.uploader.data.multiple = true;
            } else {
                Amaz.uploader.data.multiple = false;
            }
            $('#pre-loader').removeClass('d-none');

            $.post(
                Amaz.data.appUrl + "/media-manager/get-modal", {
                    _token: Amaz.data.csrf
                },
                function(data) {
                    $('#pre-loader').addClass('d-none');
                    $("#mediaManagerDiv").html(data);
                    $('.modal-backdrop').remove()
                    $("#media_modal").modal("show");
                    $('#sortStatus').niceSelect();
                    console.log('update')
                    Amaz.uploader.amazUppy();
                    Amaz.uploader.getAllUploads(
                        Amaz.data.appUrl + "/media-manager/get-files-modal",
                        null,
                        $('[name="Amaz_media_sort"]').val()
                    );

                    Amaz.uploader.updateUploaderSelected();
                    Amaz.uploader.clearUploaderSelected();
                    Amaz.uploader.sortUploaderFiles();
                    Amaz.uploader.searchUploaderFiles();
                    Amaz.uploader.showSelectedFiles();

                    $("#uploader_next_btn").on("click", function(e) {
                        e.preventDefault();
                        if (Amaz.uploader.data.next_page_url != null) {
                            $('[name="aiz-show-selected"]').prop(
                                "checked",
                                false
                            );
                            Amaz.uploader.getAllUploads(
                                Amaz.uploader.data.next_page_url
                            );
                        }
                    });

                    $("#uploader_prev_btn").on("click", function(e) {
                        e.preventDefault();
                        if (Amaz.uploader.data.prev_page_url != null) {
                            $('[name="aiz-show-selected"]').prop(
                                "checked",
                                false
                            );
                            Amaz.uploader.getAllUploads(
                                Amaz.uploader.data.prev_page_url
                            );
                        }
                    });

                    $(".aiz-uploader-search i").on("click", function(e) {
                        e.preventDefault();
                        $(this).parent().toggleClass("open");
                    });

                    $('[data-bs-toggle="infixUploaderAddSelected"]').on(
                        "click",
                        function(e) {
                            e.preventDefault();
                            if (from === "input") {
                                Amaz.uploader.inputSelectPreviewGenerate(elem);
                            } else if (from === "direct") {
                                callback(Amaz.uploader.data.selectedFiles);
                            }
                            $("#media_modal").modal("hide");
                            $('#pre-loader').removeClass('d-none');
                        }
                    );
                }
            );
        },
        initForInput: function() {
            $(document).on("click", '[data-bs-toggle="infixUploader"]', function(e) {
                e.preventDefault();
                if (e.detail === 1) {
                    var elem = $(this);
                    var multiple = elem.data("multiple");
                    var type = elem.data("type");
                    var oldSelectedFiles = elem.find(".selected_files").val();
                    multiple = !multiple ? "" : multiple;
                    type = !type ? "" : type;
                    oldSelectedFiles = !oldSelectedFiles ? "" : oldSelectedFiles;
                    Amaz.uploader.data.for_name = elem.data('name');
                    Amaz.uploader.trigger(
                        this,
                        "input",
                        type,
                        oldSelectedFiles,
                        multiple
                    );
                }
            });
        },
        inputSelectPreviewGenerate: function(elem) {
            var prev_data = elem.find(".selected_files").val();
            elem.find(".selected_files").val(Amaz.uploader.data.selectedFiles);
            elem.next(".product_image_all_div").html(null);

            if (Amaz.uploader.data.selectedFiles.length > 0) {
                $.post(
                    "http://127.0.0.1:8001/media-manager/get_media_by_id", {
                        _token: Amaz.data.csrf,
                        ids: Amaz.uploader.data.selectedFiles,
                        prev_ids: prev_data
                    },
                    function(data) {
                        $('#pre-loader').addClass('d-none');

                        elem.closest('.single-uploader').find(".product_image_all_div").html(null);

                        if (data.length > 0) {
                            elem.find(".file_amount").attr('placeholder', Amaz.uploader.updateFileHtml(
                                data));
                            for (let i = 0; i < data.length; i++) {


                                let thumb = "";
                                let imag = data[i].file_name;
                                let id = data[i].id;


                                if (data[i].type == 'image') {
                                    if (data[i].storage == 'local') {
                                        $('#show_path').text('http://127.0.0.1:8001' + '/' + data[i]
                                            .file_name);
                                        var image_path = "http://127.0.0.1:8001/" + "/" + imag;
                                        imag = image_path;
                                    } else {
                                        $('#show_path').text(data[i].file_name);
                                        imag = imag;
                                    }
                                } else {
                                    if (data[i].storage == 'local') {
                                        $('#show_path').text('http://127.0.0.1:8001' + '/' + data[i]
                                            .file_name);
                                    } else {
                                        $('#show_path').text(data[i].file_name);
                                    }
                                    imag = "http://127.0.0.1:8001/" + "public/preview/" + data[i].type +
                                        ".png";
                                }
                                thumb = `<img id="ThumbnailImg${id}" src="${imag}" alt="">`;

                                let html = `
                                    <div class="thumb_img_div" data-id="${data[i].id}">
                                        <div class="img_remove_btn">
                                            <i class="fas fa-times"></i>
                                        </div>
                                        ${thumb}
                                        <input type="hidden" class="product_images_hidden" name="${Amaz.uploader.data.for_name}" value="${data[i].id}">
                                    </div>
                                `;

                                elem.closest('.single-uploader').find(".product_image_all_div").append(
                                    html);
                            }
                        } else {
                            elem.closest('.single-uploader').find(".file_amount").html('Choose File');
                        }
                    });
            } else {
                elem.closest('.single-uploader').find(".file_amount").html('Choose File');
            }
        },
        previewGenerate: function() {


            $('[data-bs-toggle="infixUploader"]').each(function() {
                let $this = $(this);
                let files = $this.find(".selected_files").val().split(",").map(Number);
                if (files != "") {
                    $.post(
                        "http://127.0.0.1:8001/media-manager/get_media_by_id", {
                            _token: Amaz.data.csrf,
                            ids: files
                        },
                        function(datas) {
                            Amaz.uploader.data.for_name = $this.data('name');
                            data = [];
                            files.forEach(function(key) {
                                let found = false;
                                datas = datas.filter(function(file) {
                                    if (!found && file.id == key) {
                                        data.push(file);
                                        found = true;
                                        return false;
                                    } else
                                        return true;
                                });
                            });
                            Amaz.uploader.data.selectedFilesObject = data;
                            if (data.length > 0) {
                                $this.next(".product_image_all_div").html(null);
                                $this.find(".file_amount").attr('placeholder', Amaz.uploader
                                    .updateFileHtml(data));
                                for (let i = 0; i < data.length; i++) {
                                    let thumb = "";
                                    let imag = data[i].file_name;
                                    let id = data[i].id;


                                    if (data[i].type == 'image') {
                                        if (data[i].storage == 'local') {
                                            $('#show_path').text('http://127.0.0.1:8001' + '/' +
                                                data[i].file_name);
                                            var image_path = "http://127.0.0.1:8001/" + "/" + imag;
                                            imag = image_path;
                                        } else {
                                            $('#show_path').text(data[i].file_name);
                                            imag = imag;
                                        }
                                    } else {
                                        if (data[i].storage == 'local') {
                                            $('#show_path').text('http://127.0.0.1:8001' + '/' +
                                                data[i].file_name);
                                        } else {
                                            $('#show_path').text(data[i].file_name);
                                        }
                                        imag = "http://127.0.0.1:8001/" + "public/preview/" + data[
                                            i].type + ".png";
                                    }
                                    thumb = `<img id="ThumbnailImg${id}" src="${imag}" alt="">`;

                                    let html = `
                                    <div class="thumb_img_div" data-id="${data[i].id}">
                                        <div class="img_remove_btn">
                                            <i class="fas fa-times"></i>
                                        </div>
                                        ${thumb}
                                        <input type="hidden" name="${Amaz.uploader.data.for_name}" class="product_images_hidden" value="${data[i].id}">
                                    </div>
                                `;

                                    $this.next(".product_image_all_div").append(html);
                                }
                            } else {
                                $this.find(".file_amount").html('Choose File');
                            }
                        });
                }
            });


        },
        removeAttachment: function() {
            $(document).on("click", '.img_remove_btn', function(e) {
                e.preventDefault();
                let value = $(this)
                    .closest(".thumb_img_div")
                    .data("id");
                let selected = $(this)
                    .closest(".product_image_all_div")
                    .prev('[data-bs-toggle="infixUploader"]')
                    .find(".selected_files")
                    .val()
                    .split(",")
                    .map(Number);

                Amaz.uploader.removeInputValue(
                    value,
                    selected,
                    $(this)
                    .closest(".product_image_all_div")
                    .prev('[data-bs-toggle="infixUploader"]')
                );
                $(this).closest(".thumb_img_div").remove();
            });
        },
        removeInputValue: function(id, array, elem) {
            let selected = array.filter(function(item) {
                return item !== id;
            });
            if (selected.length > 0) {
                $(elem)
                    .find(".file_amount")
                    .attr('placeholder', Amaz.uploader.updateFileHtml(selected));
            } else {
                elem.find(".file_amount").attr('placeholder', 'Choose File');
            }
            $(elem).find(".selected_files").val(selected);
        },
        sortImage: function() {
            $(".product_image_all_div").sortable({
                cursor: "move",
                connectWith: ".thumb_img_div",
                update: function(event, ui) {
                    let imageids = [];
                    $(this).find('.product_images_hidden').each(function(id) {
                        imageids.push($(this).val());
                    });
                    $(this).prev('[data-bs-toggle="infixUploader"]').find('.selected_files').val(
                        imageids.join(','));
                }
            });
        }

    };

    Amaz.uploader.clearUploaderSelected();
    Amaz.uploader.initForInput();
    Amaz.uploader.removeAttachment();
    Amaz.uploader.sortImage();
    Amaz.uploader.previewGenerate();
</script>

<script>
    $(function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
