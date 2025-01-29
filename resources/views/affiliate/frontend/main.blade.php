<!DOCTYPE html>
<html dir="" class="" lang="en" itemscope itemtype="{{ url('/') }}">

<head>
    @include('affiliate.frontend.head')
</head>

<body>
    {{-- preloader --}}
    {{-- <div class="preloader" dir="ltr">
        <div class="dot dot2 d21"></div>
        <div class="dot dot2 d22"></div>
        <div class="dot dot2 d23"></div>
    </div> --}}



    <script>
        window.Laravel = {
            "baseUrl": '{{ url('/') }}' + '/',
            "current_path_without_domain": '{{ request()->path() }}',
            "csrfToken": '{{ csrf_token() }}',
        }
    </script>


    {{-- <script>
        window._locale = 'th';
        window._translations = {
            "data_success": "\u0e2a\u0e33\u0e40\u0e23\u0e47\u0e08",
            "data_update": "\u0e2d\u0e31\u0e1b\u0e40\u0e14\u0e15",
            "data_showing": "\u0e41\u0e2a\u0e14\u0e07",
            "data_entries": "\u0e23\u0e32\u0e22\u0e01\u0e32\u0e23",
            "data_to": "\u0e16\u0e36\u0e07",
            "data_of": "\u0e02\u0e2d\u0e07",
            "data_no_data_available_in_table": "\u0e44\u0e21\u0e48\u0e21\u0e35\u0e02\u0e49\u0e2d\u0e21\u0e39\u0e25\u0e43\u0e19\u0e15\u0e32\u0e23\u0e32\u0e07\u0e19\u0e35\u0e49",
            "data_search": "\u0e04\u0e49\u0e19\u0e2b\u0e32",
            "data_drag_drop": "\u0e25\u0e32\u0e01 & \u0e27\u0e32\u0e07\u0e44\u0e1f\u0e25\u0e4c\u0e02\u0e2d\u0e07\u0e04\u0e38\u0e13\u0e2b\u0e23\u0e37\u0e2d",
            "data_browse": "\u0e40\u0e23\u0e35\u0e22\u0e01\u0e14\u0e39",
            "data_invalid_file": "\u0e1f\u0e34\u0e25\u0e14\u0e4c\u0e21\u0e35\u0e44\u0e1f\u0e25\u0e4c\u0e17\u0e35\u0e48\u0e44\u0e21\u0e48\u0e16\u0e39\u0e01\u0e15\u0e49\u0e2d\u0e07",
            "data_wait_for_size": "\u0e23\u0e2d\u0e02\u0e19\u0e32\u0e14",
            "data_upload": "\u0e2d\u0e31\u0e1b\u0e42\u0e2b\u0e25\u0e14",
            "data_retry": "\u0e25\u0e2d\u0e07\u0e2d\u0e35\u0e01\u0e04\u0e23\u0e31\u0e49\u0e07",
            "data_undo": "\u0e22\u0e01\u0e40\u0e25\u0e34\u0e01",
            "data_cancel": "\u0e22\u0e01\u0e40\u0e25\u0e34\u0e01",
            "data_abort": "\u0e22\u0e01\u0e40\u0e25\u0e34\u0e01",
            "data_remove": "\u0e25\u0e1a",
            "data_tap_to_undo": "\u0e41\u0e15\u0e30\u0e40\u0e1e\u0e37\u0e48\u0e2d\u0e22\u0e01\u0e40\u0e25\u0e34\u0e01",
            "data_tap_to_retry": "\u0e41\u0e15\u0e30\u0e40\u0e1e\u0e37\u0e48\u0e2d\u0e25\u0e2d\u0e07\u0e2d\u0e35\u0e01\u0e04\u0e23\u0e31\u0e49\u0e07",
            "data_tab_or_wait": "\u0e41\u0e15\u0e30\u0e40\u0e1e\u0e37\u0e48\u0e2d\u0e22\u0e01\u0e40\u0e25\u0e34\u0e01 | \u0e23\u0e2d\u0e08\u0e19\u0e01\u0e27\u0e48\u0e32\u0e08\u0e30\u0e40\u0e2b\u0e47\u0e19\u0e27\u0e48\u0e32\u0e2d\u0e31\u0e1b\u0e42\u0e2b\u0e25\u0e14\u0e40\u0e2a\u0e23\u0e47\u0e08\u0e2a\u0e21\u0e1a\u0e39\u0e23\u0e13\u0e4c",
            "data_error_during_revert": "\u0e40\u0e01\u0e34\u0e14\u0e02\u0e49\u0e2d\u0e1c\u0e34\u0e14\u0e1e\u0e25\u0e32\u0e14\u0e23\u0e30\u0e2b\u0e27\u0e48\u0e32\u0e07\u0e01\u0e32\u0e23\u0e04\u0e37\u0e19\u0e04\u0e48\u0e32",
            "data_error_during_upload": "\u0e40\u0e01\u0e34\u0e14\u0e02\u0e49\u0e2d\u0e1c\u0e34\u0e14\u0e1e\u0e25\u0e32\u0e14\u0e23\u0e30\u0e2b\u0e27\u0e48\u0e32\u0e07\u0e01\u0e32\u0e23\u0e2d\u0e31\u0e1b\u0e42\u0e2b\u0e25\u0e14",
            "data_upload_cancel": "\u0e01\u0e32\u0e23\u0e2d\u0e31\u0e1b\u0e42\u0e2b\u0e25\u0e14\u0e16\u0e39\u0e01\u0e22\u0e01\u0e40\u0e25\u0e34\u0e01",
            "data_upload_complete": "\u0e01\u0e32\u0e23\u0e2d\u0e31\u0e1b\u0e42\u0e2b\u0e25\u0e14\u0e40\u0e2a\u0e23\u0e47\u0e08\u0e2a\u0e21\u0e1a\u0e39\u0e23\u0e13\u0e4c",
            "data_uploading": "\u0e01\u0e33\u0e25\u0e31\u0e07\u0e2d\u0e31\u0e1b\u0e42\u0e2b\u0e25\u0e14",
            "data_error_during_remove": "\u0e40\u0e01\u0e34\u0e14\u0e02\u0e49\u0e2d\u0e1c\u0e34\u0e14\u0e1e\u0e25\u0e32\u0e14\u0e23\u0e30\u0e2b\u0e27\u0e48\u0e32\u0e07\u0e01\u0e32\u0e23\u0e25\u0e1a",
            "data_removed": "\u0e16\u0e39\u0e01\u0e25\u0e1a",
            "data_error_during_load": "\u0e40\u0e01\u0e34\u0e14\u0e02\u0e49\u0e2d\u0e1c\u0e34\u0e14\u0e1e\u0e25\u0e32\u0e14\u0e23\u0e30\u0e2b\u0e27\u0e48\u0e32\u0e07\u0e01\u0e32\u0e23\u0e42\u0e2b\u0e25\u0e14",
            "data_added": "\u0e40\u0e1e\u0e34\u0e48\u0e21\u0e41\u0e25\u0e49\u0e27",
            "data_loading": "\u0e01\u0e33\u0e25\u0e31\u0e07\u0e42\u0e2b\u0e25\u0e14...",
            "data_files_in_list": "\u0e44\u0e1f\u0e25\u0e4c\u0e43\u0e19\u0e23\u0e32\u0e22\u0e01\u0e32\u0e23",
            "data_file_in_list": "\u0e44\u0e1f\u0e25\u0e4c\u0e43\u0e19\u0e23\u0e32\u0e22\u0e01\u0e32\u0e23",
            "data_size_not_available": "\u0e02\u0e19\u0e32\u0e14\u0e44\u0e21\u0e48\u0e1e\u0e23\u0e49\u0e2d\u0e21\u0e43\u0e0a\u0e49\u0e07\u0e32\u0e19",
            "data_operation_success": "\u0e14\u0e33\u0e40\u0e19\u0e34\u0e19\u0e01\u0e32\u0e23\u0e2a\u0e33\u0e40\u0e23\u0e47\u0e08",
            "data_something_went_wrong": "\u0e40\u0e01\u0e34\u0e14\u0e02\u0e49\u0e2d\u0e1c\u0e34\u0e14\u0e1e\u0e25\u0e32\u0e14\u0e1a\u0e32\u0e07\u0e2d\u0e22\u0e48\u0e32\u0e07",
            "data_error": "\u0e02\u0e49\u0e2d\u0e1c\u0e34\u0e14\u0e1e\u0e25\u0e32\u0e14",
            "data_select_option": "\u0e42\u0e1b\u0e23\u0e14\u0e40\u0e25\u0e37\u0e2d\u0e01\u0e15\u0e31\u0e27\u0e40\u0e25\u0e37\u0e2d\u0e01",
            "data_select_match": "\u0e42\u0e1b\u0e23\u0e14\u0e40\u0e25\u0e37\u0e2d\u0e01\u0e15\u0e31\u0e27\u0e40\u0e25\u0e37\u0e2d\u0e01\u0e17\u0e35\u0e48\u0e15\u0e23\u0e07\u0e01\u0e31\u0e19",
            "data_write_ans": "\u0e42\u0e1b\u0e23\u0e14\u0e40\u0e02\u0e35\u0e22\u0e19\u0e04\u0e33\u0e15\u0e2d\u0e1a",
            "data_quick_search": "\u0e04\u0e49\u0e19\u0e2b\u0e32\u0e2d\u0e22\u0e48\u0e32\u0e07\u0e23\u0e27\u0e14\u0e40\u0e23\u0e47\u0e27",
            "data_characters_count": "\u0e08\u0e33\u0e19\u0e27\u0e19\u0e15\u0e31\u0e27\u0e2d\u0e31\u0e01\u0e29\u0e23",
            "no_data_available": "\u0e44\u0e21\u0e48\u0e21\u0e35\u0e02\u0e49\u0e2d\u0e21\u0e39\u0e25",
            "data_all": "\u0e17\u0e31\u0e49\u0e07\u0e2b\u0e21\u0e14",
            "data_info_filtered": "\u0e01\u0e23\u0e2d\u0e07\u0e08\u0e32\u0e01",
            "data_show": "\u0e41\u0e2a\u0e14\u0e07",
            "data_processing": "\u0e01\u0e33\u0e25\u0e31\u0e07\u0e14\u0e33\u0e40\u0e19\u0e34\u0e19\u0e01\u0e32\u0e23...",
            "data_no_match": "\u0e44\u0e21\u0e48\u0e1e\u0e1a\u0e23\u0e30\u0e40\u0e1a\u0e35\u0e22\u0e19\u0e17\u0e35\u0e48\u0e15\u0e23\u0e07\u0e01\u0e31\u0e19",
            "data_first": "\u0e41\u0e23\u0e01",
            "data_last": "\u0e2a\u0e38\u0e14\u0e17\u0e49\u0e32\u0e22",
            "data_col_visibility": "\u0e04\u0e27\u0e32\u0e21\u0e2a\u0e32\u0e21\u0e32\u0e23\u0e16\u0e43\u0e19\u0e01\u0e32\u0e23\u0e21\u0e2d\u0e07\u0e40\u0e2b\u0e47\u0e19\u0e04\u0e2d\u0e25\u0e31\u0e21\u0e19\u0e4c",
            "data_col_restore": "\u0e01\u0e39\u0e49\u0e04\u0e37\u0e19\u0e04\u0e27\u0e32\u0e21\u0e2a\u0e32\u0e21\u0e32\u0e23\u0e16\u0e43\u0e19\u0e01\u0e32\u0e23\u0e21\u0e2d\u0e07\u0e40\u0e2b\u0e47\u0e19",
            "data_collection": "\u0e01\u0e32\u0e23\u0e23\u0e27\u0e1a\u0e23\u0e27\u0e21",
            "data_copy": "\u0e04\u0e31\u0e14\u0e25\u0e2d\u0e01",
            "data_copy_keys": "\u0e01\u0e14 ctrl \u0e2b\u0e23\u0e37\u0e2d u2318 + C \u0e40\u0e1e\u0e37\u0e48\u0e2d\u0e04\u0e31\u0e14\u0e25\u0e2d\u0e01\u0e02\u0e49\u0e2d\u0e21\u0e39\u0e25\u0e15\u0e32\u0e23\u0e32\u0e07\u0e44\u0e1b\u0e22\u0e31\u0e07\u0e04\u0e25\u0e34\u0e1b\u0e1a\u0e2d\u0e23\u0e4c\u0e14\u0e02\u0e2d\u0e07\u0e23\u0e30\u0e1a\u0e1a\u0e04\u0e38\u0e13",
            "data_copy_cancel": "\u0e2b\u0e32\u0e01\u0e15\u0e49\u0e2d\u0e07\u0e01\u0e32\u0e23\u0e22\u0e01\u0e40\u0e25\u0e34\u0e01 \u0e04\u0e25\u0e34\u0e01\u0e17\u0e35\u0e48\u0e02\u0e49\u0e2d\u0e04\u0e27\u0e32\u0e21\u0e19\u0e35\u0e49\u0e2b\u0e23\u0e37\u0e2d\u0e01\u0e14 escape",
            "data_copy_success": "\u0e04\u0e31\u0e14\u0e25\u0e2d\u0e01 1 \u0e41\u0e16\u0e27\u0e44\u0e1b\u0e22\u0e31\u0e07\u0e04\u0e25\u0e34\u0e1b\u0e1a\u0e2d\u0e23\u0e4c\u0e14",
            "data_copied": "\u0e04\u0e31\u0e14\u0e25\u0e2d\u0e01\u0e41\u0e25\u0e49\u0e27",
            "data_rows_to_clipboard": "\u0e41\u0e16\u0e27\u0e44\u0e1b\u0e22\u0e31\u0e07\u0e04\u0e25\u0e34\u0e1b\u0e1a\u0e2d\u0e23\u0e4c\u0e14",
            "data_copy_to_clipboard": "\u0e04\u0e31\u0e14\u0e25\u0e2d\u0e01\u0e44\u0e1b\u0e22\u0e31\u0e07\u0e04\u0e25\u0e34\u0e1b\u0e1a\u0e2d\u0e23\u0e4c\u0e14",
            "data_csv": "CSV",
            "data_excel": "Excel",
            "data_show_all_rows": "\u0e41\u0e2a\u0e14\u0e07\u0e17\u0e38\u0e01\u0e41\u0e16\u0e27",
            "data_rows": "\u0e41\u0e16\u0e27",
            "data_pdf": "PDF",
            "data_print": "\u0e1e\u0e34\u0e21\u0e1e\u0e4c",
            "data_restore": "\u0e01\u0e39\u0e49\u0e04\u0e37\u0e19",
            "data_states": "\u0e2a\u0e16\u0e32\u0e19\u0e30",
            "data_rename": "\u0e40\u0e1b\u0e25\u0e35\u0e48\u0e22\u0e19\u0e0a\u0e37\u0e48\u0e2d",
            "data_remove_all": "\u0e25\u0e1a\u0e17\u0e31\u0e49\u0e07\u0e2b\u0e21\u0e14",
            "data_create": "\u0e2a\u0e23\u0e49\u0e32\u0e07",
            "data_previous": "\u0e01\u0e48\u0e2d\u0e19\u0e2b\u0e19\u0e49\u0e32",
            "data_next": "\u0e16\u0e31\u0e14\u0e44\u0e1b",
            "data_hours": "\u0e0a\u0e31\u0e48\u0e27\u0e42\u0e21\u0e07",
            "data_minutes": "\u0e19\u0e32\u0e17\u0e35",
            "data_seconds": "\u0e27\u0e34\u0e19\u0e32\u0e17\u0e35",
            "data_am": "AM",
            "data_pm": "PM",
            "data_Sun": "\u0e2d\u0e32.",
            "data_Mon": "\u0e08.",
            "data_Tue": "\u0e2d.",
            "data_Wed": "\u0e1e.",
            "data_Thu": "\u0e1e\u0e24.",
            "data_Fri": "\u0e28.",
            "data_Sat": "\u0e2a.",
            "data_January": "\u0e21\u0e01\u0e23\u0e32\u0e04\u0e21",
            "data_February": "\u0e01\u0e38\u0e21\u0e20\u0e32\u0e1e\u0e31\u0e19\u0e18\u0e4c",
            "data_March": "\u0e21\u0e35\u0e19\u0e32\u0e04\u0e21",
            "data_April": "\u0e40\u0e21\u0e29\u0e32\u0e22\u0e19",
            "data_May": "\u0e1e\u0e24\u0e29\u0e20\u0e32\u0e04\u0e21",
            "data_June": "\u0e21\u0e34\u0e16\u0e38\u0e19\u0e32\u0e22\u0e19",
            "data_July": "\u0e01\u0e23\u0e01\u0e0e\u0e32\u0e04\u0e21",
            "data_August": "\u0e2a\u0e34\u0e07\u0e2b\u0e32\u0e04\u0e21",
            "data_September": "\u0e01\u0e31\u0e19\u0e22\u0e32\u0e22\u0e19",
            "data_October": "\u0e15\u0e38\u0e25\u0e32\u0e04\u0e21",
            "data_November": "\u0e1e\u0e24\u0e28\u0e08\u0e34\u0e01\u0e32\u0e22\u0e19",
            "data_December": "\u0e18\u0e31\u0e19\u0e27\u0e32\u0e04\u0e21",
            "data_info_empty": "\u0e41\u0e2a\u0e14\u0e07 0 \u0e16\u0e36\u0e07 0 \u0e02\u0e2d\u0e07 0 \u0e23\u0e32\u0e22\u0e01\u0e32\u0e23",
            "data_aora_editor": "\u0e01\u0e33\u0e25\u0e31\u0e07\u0e41\u0e01\u0e49\u0e44\u0e02\u0e14\u0e49\u0e27\u0e22 Aora Editor",
            "data_view_mobile": "\u0e14\u0e39\u0e1a\u0e19\u0e21\u0e37\u0e2d\u0e16\u0e37\u0e2d",
            "data_view_tablet": "\u0e14\u0e39\u0e1a\u0e19\u0e41\u0e17\u0e47\u0e1a\u0e40\u0e25\u0e47\u0e15",
            "data_view_laptop": "\u0e14\u0e39\u0e1a\u0e19\u0e41\u0e25\u0e47\u0e1b\u0e17\u0e47\u0e2d\u0e1b",
            "data_view_desktop": "\u0e14\u0e39\u0e1a\u0e19\u0e40\u0e14\u0e2a\u0e01\u0e4c\u0e17\u0e47\u0e2d\u0e1b",
            "data_preview_on": "\u0e14\u0e39\u0e15\u0e31\u0e27\u0e2d\u0e22\u0e48\u0e32\u0e07\u0e40\u0e1b\u0e34\u0e14",
            "data_preview_off": "\u0e14\u0e39\u0e15\u0e31\u0e27\u0e2d\u0e22\u0e48\u0e32\u0e07\u0e1b\u0e34\u0e14",
            "data_fullscreen_on": "\u0e40\u0e15\u0e47\u0e21\u0e2b\u0e19\u0e49\u0e32\u0e08\u0e2d\u0e40\u0e1b\u0e34\u0e14",
            "data_fullscreen_off": "\u0e40\u0e15\u0e47\u0e21\u0e2b\u0e19\u0e49\u0e32\u0e08\u0e2d\u0e1b\u0e34\u0e14",
            "data_save": "\u0e1a\u0e31\u0e19\u0e17\u0e36\u0e01",
            "data_add_content": "\u0e40\u0e1e\u0e34\u0e48\u0e21\u0e40\u0e19\u0e37\u0e49\u0e2d\u0e2b\u0e32",
            "data_add_content_below": "\u0e40\u0e1e\u0e34\u0e48\u0e21\u0e40\u0e19\u0e37\u0e49\u0e2d\u0e2b\u0e32\u0e14\u0e49\u0e32\u0e19\u0e25\u0e48\u0e32\u0e07",
            "data_paste_content": "\u0e27\u0e32\u0e07\u0e40\u0e19\u0e37\u0e49\u0e2d\u0e2b\u0e32",
            "data_paste_content_below": "\u0e27\u0e32\u0e07\u0e40\u0e19\u0e37\u0e49\u0e2d\u0e2b\u0e32\u0e14\u0e49\u0e32\u0e19\u0e25\u0e48\u0e32\u0e07",
            "data_drag": ["\u0e25\u0e32\u0e01", "\u0e25\u0e32\u0e01\u0e44\u0e14\u0e49"],
            "data_move_up": "\u0e40\u0e25\u0e37\u0e48\u0e2d\u0e19\u0e02\u0e36\u0e49\u0e19",
            "data_move_down": "\u0e40\u0e25\u0e37\u0e48\u0e2d\u0e19\u0e25\u0e07",
            "data_setting": "\u0e01\u0e32\u0e23\u0e15\u0e31\u0e49\u0e07\u0e04\u0e48\u0e32",
            "data_cut": "\u0e15\u0e31\u0e14",
            "data_delete": "\u0e25\u0e1a",
            "data_category": "\u0e2b\u0e21\u0e27\u0e14\u0e2b\u0e21\u0e39\u0e48",
            "data_type_to_search": "\u0e1e\u0e34\u0e21\u0e1e\u0e4c\u0e40\u0e1e\u0e37\u0e48\u0e2d\u0e04\u0e49\u0e19\u0e2b\u0e32...",
            "data_drag_to_resize": "\u0e25\u0e32\u0e01\u0e40\u0e1e\u0e37\u0e48\u0e2d\u0e1b\u0e23\u0e31\u0e1a\u0e02\u0e19\u0e32\u0e14",
            "data_container_settings": "\u0e01\u0e32\u0e23\u0e15\u0e31\u0e49\u0e07\u0e04\u0e48\u0e32\u0e20\u0e32\u0e0a\u0e19\u0e30",
            "data_confirm_delete_container": "\u0e04\u0e38\u0e13\u0e41\u0e19\u0e48\u0e43\u0e08\u0e2b\u0e23\u0e37\u0e2d\u0e44\u0e21\u0e48\u0e27\u0e48\u0e32\u0e15\u0e49\u0e2d\u0e07\u0e01\u0e32\u0e23\u0e25\u0e1a\u0e20\u0e32\u0e0a\u0e19\u0e30\u0e19\u0e35\u0e49? \u0e01\u0e32\u0e23\u0e01\u0e23\u0e30\u0e17\u0e33\u0e19\u0e35\u0e49\u0e44\u0e21\u0e48\u0e2a\u0e32\u0e21\u0e32\u0e23\u0e16\u0e22\u0e49\u0e2d\u0e19\u0e01\u0e25\u0e31\u0e1a\u0e44\u0e14\u0e49!",
            "data_confirm_delete_component": "\u0e04\u0e38\u0e13\u0e41\u0e19\u0e48\u0e43\u0e08\u0e2b\u0e23\u0e37\u0e2d\u0e44\u0e21\u0e48\u0e27\u0e48\u0e32\u0e15\u0e49\u0e2d\u0e07\u0e01\u0e32\u0e23\u0e25\u0e1a\u0e2a\u0e48\u0e27\u0e19\u0e1b\u0e23\u0e30\u0e01\u0e2d\u0e1a\u0e19\u0e35\u0e49? \u0e01\u0e32\u0e23\u0e01\u0e23\u0e30\u0e17\u0e33\u0e19\u0e35\u0e49\u0e44\u0e21\u0e48\u0e2a\u0e32\u0e21\u0e32\u0e23\u0e16\u0e22\u0e49\u0e2d\u0e19\u0e01\u0e25\u0e31\u0e1a\u0e44\u0e14\u0e49!",
            "data_select_options": "\u0e40\u0e25\u0e37\u0e2d\u0e01\u0e15\u0e31\u0e27\u0e40\u0e25\u0e37\u0e2d\u0e01",
            "data_selected": "\u0e40\u0e25\u0e37\u0e2d\u0e01\u0e41\u0e25\u0e49\u0e27",
            "data_select_all": "\u0e40\u0e25\u0e37\u0e2d\u0e01\u0e17\u0e31\u0e49\u0e07\u0e2b\u0e21\u0e14",
            "data_unselect_all": "\u0e44\u0e21\u0e48\u0e40\u0e25\u0e37\u0e2d\u0e01\u0e17\u0e31\u0e49\u0e07\u0e2b\u0e21\u0e14",
            "data_none_selected": "\u0e44\u0e21\u0e48\u0e21\u0e35\u0e17\u0e35\u0e48\u0e40\u0e25\u0e37\u0e2d\u0e01",
            "data_all_selected": "\u0e40\u0e25\u0e37\u0e2d\u0e01\u0e17\u0e31\u0e49\u0e07\u0e2b\u0e21\u0e14",
            "data_select": "\u0e40\u0e25\u0e37\u0e2d\u0e01",
            "data_apply": "\u0e19\u0e33\u0e44\u0e1b\u0e43\u0e0a\u0e49",
            "data_from": "\u0e08\u0e32\u0e01",
            "data_custom": "\u0e01\u0e33\u0e2b\u0e19\u0e14\u0e40\u0e2d\u0e07",
            "data_today": "\u0e27\u0e31\u0e19\u0e19\u0e35\u0e49",
            "data_yesterday": "\u0e40\u0e21\u0e37\u0e48\u0e2d\u0e27\u0e32\u0e19",
            "data_last_7_days": "7 \u0e27\u0e31\u0e19\u0e17\u0e35\u0e48\u0e1c\u0e48\u0e32\u0e19\u0e21\u0e32",
            "data_last_30_days": "30 \u0e27\u0e31\u0e19\u0e17\u0e35\u0e48\u0e1c\u0e48\u0e32\u0e19\u0e21\u0e32",
            "data_this_month": "\u0e40\u0e14\u0e37\u0e2d\u0e19\u0e19\u0e35\u0e49",
            "data_last_month": "\u0e40\u0e14\u0e37\u0e2d\u0e19\u0e17\u0e35\u0e48\u0e41\u0e25\u0e49\u0e27",
            "data_this_year": "\u0e1b\u0e35\u0e19\u0e35\u0e49",
            "data_last_year": "\u0e1b\u0e35\u0e17\u0e35\u0e48\u0e41\u0e25\u0e49\u0e27",
            "data_browse_files": "\u0e40\u0e23\u0e35\u0e22\u0e01\u0e14\u0e39\u0e44\u0e1f\u0e25\u0e4c",
            "data_10": "10",
            "data_25": "25",
            "data_50": "50",
            "data_100": "100",
            "data_minimum_payout": "\u0e08\u0e33\u0e19\u0e27\u0e19\u0e40\u0e07\u0e34\u0e19\u0e02\u0e31\u0e49\u0e19\u0e15\u0e48\u0e33\u0e17\u0e35\u0e48\u0e16\u0e2d\u0e19\u0e04\u0e37\u0e2d",
            "data_your_balance": "\u0e22\u0e2d\u0e14\u0e40\u0e07\u0e34\u0e19\u0e02\u0e2d\u0e07\u0e04\u0e38\u0e13\u0e04\u0e37\u0e2d",
            "data_withdraw_request_submitted": "\u0e04\u0e33\u0e02\u0e2d\u0e16\u0e2d\u0e19\u0e40\u0e07\u0e34\u0e19\u0e16\u0e39\u0e01\u0e2a\u0e48\u0e07\u0e40\u0e23\u0e35\u0e22\u0e1a\u0e23\u0e49\u0e2d\u0e22\u0e41\u0e25\u0e49\u0e27",
            "data_link_copied": "\u0e25\u0e34\u0e07\u0e01\u0e4c\u0e16\u0e39\u0e01\u0e04\u0e31\u0e14\u0e25\u0e2d\u0e01\u0e40\u0e23\u0e35\u0e22\u0e1a\u0e23\u0e49\u0e2d\u0e22\u0e41\u0e25\u0e49\u0e27"
        }
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
    </script> --}}

    <script>
        window._locale = '{{ app()->getLocale() }}';
        window._translations = {!! $jsonLang ?? '' !!}
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



    <input type="hidden" id="url" value="{{ url('/') }}">
    <input type="hidden" name="lat" class="lat" value="13.809561">
    <input type="hidden" name="lng" class="lng" value="-619.551604">
    <input type="hidden" name="zoom" class="zoom" value="18">
    <input type="hidden" name="slider_transition_time" id="slider_transition_time" value="5">
    <input type="hidden" name="base_url" class="base_url" value="{{ url('/') }}">
    <input type="hidden" name="csrf_token" class="csrf_token" value="{{ csrf_token() }}">
    <input type="hidden" name="currency_symbol" class="currency_symbol" value="฿">
    <input type="hidden" name="app_debug" class="app_debug" value="1">
    @if (auth()->check())
        <input type="hidden" name="balance" class="user_balance" value="{{ auth()->user()->balance }}">
    @endif
    <div data-aoraeditor="html">
        <div class="aoraeditor-skip aoraeditor-header">
            <style>
                .header_area .main_menu ul li ul.leftcontrol_submenu {
                    left: auto !important;
                    right: 100% !important;
                }

                /* drop down menu index issue */


                @media (max-width: 768px) {
                    .header__right.login_user .profile_info_iner {
                        top: 40px;
                    }
                }

                @media (max-width: 576px) {
                    .header__right.login_user .profile_info_iner {
                        top: 70px;
                    }
                }
            </style>

            <!-- HEADER::START -->
            @include('affiliate.frontend.header')

            {{-- <a href="#" class="float notification_wrapper cart_icon">
                <div class="notify_icon cart_store" style="padding-top: 7px">
                    <img style="max-width: 30px;" src="{{ asset('frontend/infixlmstheme/img/svg/cart_white.svg') }}"
                        src="https://ecommerce.eimaths-th.com/public/frontend/infixlmstheme/img/svg/cart_white.svg" alt="">
                </div>
                <span class="notify_count">0</span>
            </a> --}}
        </div>
        @yield('content')

        {{-- @include('affiliate.frontend.footer') --}}
    </div>
    @include('affiliate.form_modal')
    @include('affiliate.modal_line_contact')
</body>
@include('affiliate.frontend.script')
@yield('script')
<input type="hidden" name="lat" class="lat" value="13.809561">
<input type="hidden" name="lng" class="lng" value="-619.551604">
<input type="hidden" name="zoom" class="zoom" value="18">


</html>
