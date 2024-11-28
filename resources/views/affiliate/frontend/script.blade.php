
<script type="text/javascript" src="{{ asset('Modules/AoraPageBuilder/Resources/assets/js/bootstrap.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('Modules/AoraPageBuilder/Resources/assets/js/jquery-ui.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('Modules/AoraPageBuilder/Resources/assets/js/ckeditor.js') }}"></script>
<script type="text/javascript" src="{{ asset('Modules/AoraPageBuilder/Resources/assets/js/form-builder.min.js') }}">
</script>
<script type="text/javascript" src="{{ asset('Modules/AoraPageBuilder/Resources/assets/js/form-render.min.js') }}">
</script>
<script type="text/javascript" src="{{ asset('Modules/AoraPageBuilder/Resources/assets/js/aoraeditor.js') }}"></script>

<script type="text/javascript"
    src="{{ asset('Modules/AoraPageBuilder/Resources/assets/js/aoraeditor-components.js') }}"></script>


<script src="{{ asset('frontend/infixlmstheme/js/courses.js') }}"></script>

<script>
    $('.ui-resizable-resizer').remove()
</script>



<script type="text/javascript" data-aoraeditor="script">
    $(function() {
        // $('.dynamicData').each(function (i, obj) {
        //     aoraEditor.loadDynamicContent($(this));
        // });


    });
    // $(function () {
    //     if ($.isFunction($.fn.lazy)) {
    //         $('.lazy').lazy();
    //     }
    // });

    // setTimeout(function() {
    //     $('.preloader').fadeOut('hide', function() {
    //         // $(this).remove();

    //     });
    // }, 0);
</script>

{{-- <script type="text/javascript">
    var phpdebugbar = new PhpDebugBar.DebugBar();
    phpdebugbar.addIndicator("php_version", new PhpDebugBar.DebugBar.Indicator({
        "icon": "code",
        "tooltip": "PHP Version"
    }), "right");
    phpdebugbar.addIndicator("time", new PhpDebugBar.DebugBar.Indicator({
        "icon": "clock-o",
        "tooltip": "Request Duration"
    }), "right");
    phpdebugbar.addTab("timeline", new PhpDebugBar.DebugBar.Tab({
        "icon": "tasks",
        "title": "Timeline",
        "widget": new PhpDebugBar.Widgets.TimelineWidget()
    }));
    phpdebugbar.addIndicator("memory", new PhpDebugBar.DebugBar.Indicator({
        "icon": "cogs",
        "tooltip": "Memory Usage"
    }), "right");
    phpdebugbar.addTab("exceptions", new PhpDebugBar.DebugBar.Tab({
        "icon": "bug",
        "title": "Exceptions",
        "widget": new PhpDebugBar.Widgets.ExceptionsWidget()
    }));
    phpdebugbar.addTab("views", new PhpDebugBar.DebugBar.Tab({
        "icon": "leaf",
        "title": "Views",
        "widget": new PhpDebugBar.Widgets.LaravelViewTemplatesWidget()
    }));
    phpdebugbar.addTab("route", new PhpDebugBar.DebugBar.Tab({
        "icon": "share",
        "title": "Route",
        "widget": new PhpDebugBar.Widgets.HtmlVariableListWidget()
    }));
    phpdebugbar.addIndicator("currentroute", new PhpDebugBar.DebugBar.Indicator({
        "icon": "share",
        "tooltip": "Route"
    }), "right");
    phpdebugbar.addTab("queries", new PhpDebugBar.DebugBar.Tab({
        "icon": "database",
        "title": "Queries",
        "widget": new PhpDebugBar.Widgets.LaravelSQLQueriesWidget()
    }));
    phpdebugbar.addTab("models", new PhpDebugBar.DebugBar.Tab({
        "icon": "cubes",
        "title": "Models",
        "widget": new PhpDebugBar.Widgets.HtmlVariableListWidget()
    }));
    phpdebugbar.addTab("session", new PhpDebugBar.DebugBar.Tab({
        "icon": "archive",
        "title": "Session",
        "widget": new PhpDebugBar.Widgets.VariableListWidget()
    }));
    phpdebugbar.addTab("request", new PhpDebugBar.DebugBar.Tab({
        "icon": "tags",
        "title": "Request",
        "widget": new PhpDebugBar.Widgets.HtmlVariableListWidget()
    }));
    phpdebugbar.setDataMap({
        "php_version": ["php.version", ],
        "time": ["time.duration_str", '0ms'],
        "timeline": ["time", {}],
        "memory": ["memory.peak_usage_str", '0B'],
        "exceptions": ["exceptions.exceptions", []],
        "exceptions:badge": ["exceptions.count", null],
        "views": ["views", []],
        "views:badge": ["views.nb_templates", 0],
        "route": ["route", {}],
        "currentroute": ["route.uri", ],
        "queries": ["queries", []],
        "queries:badge": ["queries.nb_statements", 0],
        "models": ["models.data", {}],
        "models:badge": ["models.count", 0],
        "session": ["session", {}],
        "request": ["request", {}]
    });
    phpdebugbar.restoreState();
    phpdebugbar.ajaxHandler = new PhpDebugBar.AjaxHandler(phpdebugbar, undefined, true);
    phpdebugbar.ajaxHandler.bindToFetch();
    phpdebugbar.ajaxHandler.bindToXHR();
    phpdebugbar.setOpenHandler(new PhpDebugBar.OpenHandler({
        "url": "https:\/\/ecommerce.eimaths-th.com\/_debugbar\/open"
    }));
    phpdebugbar.addDataSet({
        "__meta": {
            "id": "Xe03d2b2dd0d22ab32b2cee001983f83f",
            "datetime": "2024-11-13 09:36:47",
            "utime": 1731465407.947257,
            "method": "GET",
            "uri": "\/affiliate",
            "ip": "49.49.224.104"
        },
        "php": {
            "version": "8.2.9",
            "interface": "apache2handler"
        },
        "time": {
            "start": 1731465407.399853,
            "end": 1731465407.94727,
            "duration": 0.5474169254302979,
            "duration_str": "547ms",
            "measures": [{
                "label": "Booting",
                "start": 1731465407.399853,
                "relative_start": 0,
                "end": 1731465407.870043,
                "relative_end": 1731465407.870043,
                "duration": 0.47019004821777344,
                "duration_str": "470ms",
                "params": [],
                "collector": null
            }, {
                "label": "Application",
                "start": 1731465407.870312,
                "relative_start": 0.470458984375,
                "end": 1731465407.947273,
                "relative_end": 3.0994415283203125e-6,
                "duration": 0.07696104049682617,
                "duration_str": "76.96ms",
                "params": [],
                "collector": null
            }, {
                "label": "select * from `front_pages` where `slug` = '\/affiliate' limit 1",
                "start": 1731465407.897166,
                "relative_start": 0.4973130226135254,
                "end": 1731465407.898856,
                "relative_end": 1731465407.898856,
                "duration": 0.001689910888671875,
                "duration_str": "1.69ms",
                "params": [],
                "collector": null
            }, {
                "label": "select * from `front_pages` where `slug` = '\/affiliate' limit 1",
                "start": 1731465407.899767,
                "relative_start": 0.49991393089294434,
                "end": 1731465407.900457,
                "relative_end": 1731465407.900457,
                "duration": 0.0006899833679199219,
                "duration_str": "690\u03bcs",
                "params": [],
                "collector": null
            }]
        },
        "memory": {
            "peak_usage": 37091328,
            "peak_usage_str": "35MB"
        },
        "exceptions": {
            "count": 0,
            "exceptions": []
        },
        "views": {
            "nb_templates": 13,
            "templates": [{
                    "name": "aorapagebuilder::pages.show (Modules\/AoraPageBuilder\/Resources\/views\/pages\/show.blade.php)",
                    "param_count": 3,
                    "params": ["row", "details", "jsonLang"],
                    "type": "blade",
                    "editorLink": "phpstorm:\/\/open?file=\/var\/www\/html\/ecommerce\/Modules\/AoraPageBuilder\/Resources\/views\/pages\/show.blade.php&line=0"
                }, {
                    "name": "aorapagebuilder::layouts.master (Modules\/AoraPageBuilder\/Resources\/views\/layouts\/master.blade.php)",
                    "param_count": 7,
                    "params": ["__env", "app", "errors", "row", "details", "jsonLang", "route"],
                    "type": "blade",
                    "editorLink": "phpstorm:\/\/open?file=\/var\/www\/html\/ecommerce\/Modules\/AoraPageBuilder\/Resources\/views\/layouts\/master.blade.php&line=0"
                }, {
                    "name": "laravelpwa::meta (resources\/views\/vendor\/laravelpwa\/meta.blade.php)",
                    "param_count": 2,
                    "params": ["config", "jsonLang"],
                    "type": "blade",
                    "editorLink": "phpstorm:\/\/open?file=\/var\/www\/html\/ecommerce\/resources\/views\/vendor\/laravelpwa\/meta.blade.php&line=0"
                }, {
                    "name": "frontend.infixlmstheme.components.frontend-dynamic-style-color (resources\/views\/frontend\/infixlmstheme\/components\/frontend-dynamic-style-color.blade.php)",
                    "param_count": 12,
                    "params": ["color", "componentName", "attributes", "resolve", "slot", "__laravel_slots",
                        "homeContent", "frontendContent", "about_page", "become_instructor",
                        "work_progress", "jsonLang"
                    ],
                    "type": "blade",
                    "editorLink": "phpstorm:\/\/open?file=\/var\/www\/html\/ecommerce\/resources\/views\/frontend\/infixlmstheme\/components\/frontend-dynamic-style-color.blade.php&line=0"
                }, {
                    "name": "backend.components.dynamic-color (resources\/views\/backend\/components\/dynamic-color.blade.php)",
                    "param_count": 6,
                    "params": ["componentName", "attributes", "resolve", "slot", "__laravel_slots",
                        "jsonLang"
                    ],
                    "type": "blade",
                    "editorLink": "phpstorm:\/\/open?file=\/var\/www\/html\/ecommerce\/resources\/views\/backend\/components\/dynamic-color.blade.php&line=0"
                },
                // {
                //     "name": "preloader (resources\/views\/preloader.blade.php)",
                //     "param_count": 10,
                //     "params": ["__env", "app", "errors", "row", "details", "jsonLang", "route", "config",
                //         "component", "currentTheme"
                //     ],
                //     "type": "blade",
                //     "editorLink": "phpstorm:\/\/open?file=\/var\/www\/html\/ecommerce\/resources\/views\/preloader.blade.php&line=0"
                // }, 
                {
                    "name": "frontend.infixlmstheme.partials._menu (resources\/views\/frontend\/infixlmstheme\/partials\/_menu.blade.php)",
                    "param_count": 18,
                    "params": ["__env", "app", "errors", "row", "details", "jsonLang", "route", "config",
                        "component", "currentTheme", "categories", "languages", "menus", "homeContent",
                        "frontendContent", "about_page", "become_instructor", "work_progress"
                    ],
                    "type": "blade",
                    "editorLink": "phpstorm:\/\/open?file=\/var\/www\/html\/ecommerce\/resources\/views\/frontend\/infixlmstheme\/partials\/_menu.blade.php&line=0"
                }, {
                    "name": "frontend.infixlmstheme.partials._footer (resources\/views\/frontend\/infixlmstheme\/partials\/_footer.blade.php)",
                    "param_count": 15,
                    "params": ["__env", "app", "errors", "row", "details", "jsonLang", "route", "config",
                        "component", "currentTheme", "homeContent", "frontendContent", "about_page",
                        "become_instructor", "work_progress"
                    ],
                    "type": "blade",
                    "editorLink": "phpstorm:\/\/open?file=\/var\/www\/html\/ecommerce\/resources\/views\/frontend\/infixlmstheme\/partials\/_footer.blade.php&line=0"
                }, {
                    "name": "frontend.infixlmstheme.components.popup-content (resources\/views\/frontend\/infixlmstheme\/components\/popup-content.blade.php)",
                    "param_count": 13,
                    "params": ["popup", "modal", "componentName", "attributes", "resolve", "slot",
                        "__laravel_slots", "homeContent", "frontendContent", "about_page",
                        "become_instructor", "work_progress", "jsonLang"
                    ],
                    "type": "blade",
                    "editorLink": "phpstorm:\/\/open?file=\/var\/www\/html\/ecommerce\/resources\/views\/frontend\/infixlmstheme\/components\/popup-content.blade.php&line=0"
                }, {
                    "name": "frontend.infixlmstheme.components.footer-news-letter (resources\/views\/frontend\/infixlmstheme\/components\/footer-news-letter.blade.php)",
                    "param_count": 12,
                    "params": ["newsletterSetting", "componentName", "attributes", "resolve", "slot",
                        "__laravel_slots", "homeContent", "frontendContent", "about_page",
                        "become_instructor", "work_progress", "jsonLang"
                    ],
                    "type": "blade",
                    "editorLink": "phpstorm:\/\/open?file=\/var\/www\/html\/ecommerce\/resources\/views\/frontend\/infixlmstheme\/components\/footer-news-letter.blade.php&line=0"
                }, {
                    "name": "frontend.infixlmstheme.components.footer-social-links (resources\/views\/frontend\/infixlmstheme\/components\/footer-social-links.blade.php)",
                    "param_count": 13,
                    "params": ["social_links", "componentName", "attributes", "resolve", "institute",
                        "slot", "__laravel_slots", "homeContent", "frontendContent", "about_page",
                        "become_instructor", "work_progress", "jsonLang"
                    ],
                    "type": "blade",
                    "editorLink": "phpstorm:\/\/open?file=\/var\/www\/html\/ecommerce\/resources\/views\/frontend\/infixlmstheme\/components\/footer-social-links.blade.php&line=0"
                }, {
                    "name": "frontend.infixlmstheme.components.footer-section-widgets (resources\/views\/frontend\/infixlmstheme\/components\/footer-section-widgets.blade.php)",
                    "param_count": 12,
                    "params": ["sectionWidgets", "componentName", "attributes", "resolve", "slot",
                        "__laravel_slots", "homeContent", "frontendContent", "about_page",
                        "become_instructor", "work_progress", "jsonLang"
                    ],
                    "type": "blade",
                    "editorLink": "phpstorm:\/\/open?file=\/var\/www\/html\/ecommerce\/resources\/views\/frontend\/infixlmstheme\/components\/footer-section-widgets.blade.php&line=0"
                }, {
                    "name": "frontend.infixlmstheme.components.footer-cookie (resources\/views\/frontend\/infixlmstheme\/components\/footer-cookie.blade.php)",
                    "param_count": 12,
                    "params": ["cookie", "componentName", "attributes", "resolve", "slot",
                        "__laravel_slots", "homeContent", "frontendContent", "about_page",
                        "become_instructor", "work_progress", "jsonLang"
                    ],
                    "type": "blade",
                    "editorLink": "phpstorm:\/\/open?file=\/var\/www\/html\/ecommerce\/resources\/views\/frontend\/infixlmstheme\/components\/footer-cookie.blade.php&line=0"
                }
            ]
        },
        "route": {
            "uri": "GET affiliate",
            "middleware": "web",
            "controller": "Modules\\Affiliate\\Http\\Controllers\\AffiliateController@frontend",
            "namespace": "Modules\\Affiliate\\Http\\Controllers",
            "prefix": "",
            "where": [],
            "as": "affiliate.frontend",
            "file": "<a href=\"phpstorm:\/\/open?file=\/var\/www\/html\/ecommerce\/Modules\/Affiliate\/Http\/Controllers\/AffiliateController.php&line=124\">Modules\/Affiliate\/Http\/Controllers\/AffiliateController.php:124-139<\/a>"
        },
        "queries": {
            "nb_statements": 2,
            "nb_failed_statements": 0,
            "accumulated_duration": 0.0023799999999999997,
            "accumulated_duration_str": "2.38ms",
            "statements": [{
                "sql": "select * from `front_pages` where `slug` = '\/affiliate' limit 1",
                "type": "query",
                "params": [],
                "bindings": ["\/affiliate"],
                "hints": ["Use <code>SELECT *<\/code> only if you need all columns from table",
                    "<code>LIMIT<\/code> without <code>ORDER BY<\/code> causes non-deterministic results, depending on the query execution plan"
                ],
                "show_copy": false,
                "backtrace": [{
                    "index": 15,
                    "namespace": null,
                    "name": "\/Modules\/Affiliate\/Http\/Controllers\/AffiliateController.php",
                    "line": 126
                }, {
                    "index": 16,
                    "namespace": null,
                    "name": "\/vendor\/laravel\/framework\/src\/Illuminate\/Routing\/Controller.php",
                    "line": 54
                }, {
                    "index": 17,
                    "namespace": null,
                    "name": "\/vendor\/laravel\/framework\/src\/Illuminate\/Routing\/ControllerDispatcher.php",
                    "line": 43
                }, {
                    "index": 18,
                    "namespace": null,
                    "name": "\/vendor\/laravel\/framework\/src\/Illuminate\/Routing\/Route.php",
                    "line": 259
                }, {
                    "index": 19,
                    "namespace": null,
                    "name": "\/vendor\/laravel\/framework\/src\/Illuminate\/Routing\/Route.php",
                    "line": 205
                }],
                "duration": 0.0016899999999999999,
                "duration_str": "1.69ms",
                "stmt_id": "\/Modules\/Affiliate\/Http\/Controllers\/AffiliateController.php:126",
                "connection": "ecommerce"
            }, {
                "sql": "select * from `front_pages` where `slug` = '\/affiliate' limit 1",
                "type": "query",
                "params": [],
                "bindings": ["\/affiliate"],
                "hints": ["Use <code>SELECT *<\/code> only if you need all columns from table",
                    "<code>LIMIT<\/code> without <code>ORDER BY<\/code> causes non-deterministic results, depending on the query execution plan"
                ],
                "show_copy": false,
                "backtrace": [{
                    "index": 13,
                    "namespace": null,
                    "name": "\/Modules\/Affiliate\/Http\/Controllers\/AffiliateController.php",
                    "line": 128
                }, {
                    "index": 14,
                    "namespace": null,
                    "name": "\/vendor\/laravel\/framework\/src\/Illuminate\/Routing\/Controller.php",
                    "line": 54
                }, {
                    "index": 15,
                    "namespace": null,
                    "name": "\/vendor\/laravel\/framework\/src\/Illuminate\/Routing\/ControllerDispatcher.php",
                    "line": 43
                }, {
                    "index": 16,
                    "namespace": null,
                    "name": "\/vendor\/laravel\/framework\/src\/Illuminate\/Routing\/Route.php",
                    "line": 259
                }, {
                    "index": 17,
                    "namespace": null,
                    "name": "\/vendor\/laravel\/framework\/src\/Illuminate\/Routing\/Route.php",
                    "line": 205
                }],
                "duration": 0.00069,
                "duration_str": "690\u03bcs",
                "stmt_id": "\/Modules\/Affiliate\/Http\/Controllers\/AffiliateController.php:128",
                "connection": "ecommerce"
            }]
        },
        "models": {
            "data": {
                "Modules\\FrontendManage\\Entities\\FrontPage": 1
            },
            "count": 1
        },
        "session": {
            "domain": "main",
            "_token": "UXqxMUzO3NAybCH6WduKA4cNN4PbjD0GdofYpyEl",
            "_previous": "array:1 [\n  \"url\" => \"https:\/\/ecommerce.eimaths-th.com\/affiliate\"\n]",
            "_flash": "array:2 [\n  \"old\" => []\n  \"new\" => []\n]",
            "PHPDEBUGBAR_STACK_DATA": "[]"
        },
        "request": {
            "path_info": "\/affiliate",
            "status_code": "<pre class=sf-dump id=sf-dump-1385656747 data-indent-pad=\"  \"><span class=sf-dump-num>200<\/span>\n<\/pre><script>Sfdump(\"sf-dump-1385656747\", {\"maxDepth\":0})<\/script>\n",
            "status_text": "OK",
            "format": "html",
            "content_type": "text\/html; charset=UTF-8",
            "request_query": "<pre class=sf-dump id=sf-dump-1062989474 data-indent-pad=\"  \">[]\n<\/pre><script>Sfdump(\"sf-dump-1062989474\", {\"maxDepth\":0})<\/script>\n",
            "request_request": "<pre class=sf-dump id=sf-dump-966953105 data-indent-pad=\"  \">[]\n<\/pre><script>Sfdump(\"sf-dump-966953105\", {\"maxDepth\":0})<\/script>\n",
            "request_headers": "<pre class=sf-dump id=sf-dump-1434198212 data-indent-pad=\"  \"><span class=sf-dump-note>array:15<\/span> [<samp data-depth=1 class=sf-dump-expanded>\n  \"<span class=sf-dump-key>host<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"24 characters\">ecommerce.eimaths-th.com<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>connection<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"10 characters\">keep-alive<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>sec-ch-ua<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"65 characters\">&quot;Chromium&quot;;v=&quot;130&quot;, &quot;Google Chrome&quot;;v=&quot;130&quot;, &quot;Not?A_Brand&quot;;v=&quot;99&quot;<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>sec-ch-ua-mobile<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"2 characters\">?0<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>sec-ch-ua-platform<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"9 characters\">&quot;Windows&quot;<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>upgrade-insecure-requests<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str>1<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>user-agent<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"111 characters\">Mozilla\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/130.0.0.0 Safari\/537.36<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>accept<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"135 characters\">text\/html,application\/xhtml+xml,application\/xml;q=0.9,image\/avif,image\/webp,image\/apng,*\/*;q=0.8,application\/signed-exchange;v=b3;q=0.7<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>sec-fetch-site<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"4 characters\">none<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>sec-fetch-mode<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"8 characters\">navigate<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>sec-fetch-user<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"2 characters\">?1<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>sec-fetch-dest<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"8 characters\">document<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>accept-encoding<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"23 characters\">gzip, deflate, br, zstd<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>accept-language<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"35 characters\">th-TH,th;q=0.9,en-US;q=0.8,en;q=0.7<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>cookie<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"985 characters\">_gcl_au=1.1.1340509618.1731293720; _ga=GA1.1.436658098.1731293720; _ga_0CDRZY1XT1=GS1.1.1731465288.3.0.1731465296.0.0.0; _ga_ZV46434GKP=GS1.1.1731465288.3.0.1731465296.0.0.0; strictly_allow=1; performance_allow=1; functional_allow=1; targeting_allow=1; allow=1; XSRF-TOKEN=eyJpdiI6Im5hTWVyL0JKNjhqZ2Z1QjBXMGF3V0E9PSIsInZhbHVlIjoiU1NLU1VXY1k2Rmh0UUUydVNlYzRMNlluN01ucFh0MkVPeEFFbHFQVy9vMHpSNGgyanVQbjljdnVFdjVMSE9QUkxVMnlNQWJocml5a0Z2RE1PN0xaQ3U4clJ2MEtCZVVuQUhuZ2ZYYkFwOVpYcFlCVUR0dkhqY1BiUXRQTUJ3WjUiLCJtYWMiOiIxYTVjOTM3YTU2ZTFjNDNmYjAzMDNhYmExODBmNTdiMzBmMGE3YTYyNzUxOWU5YTI4MGYyNGMwYmE2MTJkMjkxIiwidGFnIjoiIn0%3D; eimaths_ecommerce_session=eyJpdiI6Im1Mem14K0c5NVhCaDVSemo3UnFRNkE9PSIsInZhbHVlIjoiaUZKYlRqOXBhNktIc1RHVTloMDl4U2VzNHFESEltSFJwZ3NFelpnNW1MRnpjV3lJNDFQZU9MMmxMd3pLcVdFM2xmd2RheXQxSDg4YjRWTlZ0bXRTT2Q5QTBLcVBqdFAyVHlhTy9ZTGJlK3F6eUFhTW1IaUdYNUNBWmFlZU5CU1giLCJtYWMiOiI5OGNiYjc2NjQ5YzZmMmQ0ZDQ1MDk3M2Y5YTNjYjdmMGRmY2JjNDI3NGQ5ZTQwYmVkYjZiYmVlMzYzMDRlYWM0IiwidGFnIjoiIn0%3D<\/span>\"\n  <\/samp>]\n<\/samp>]\n<\/pre><script>Sfdump(\"sf-dump-1434198212\", {\"maxDepth\":0})<\/script>\n",
            "request_server": "<pre class=sf-dump id=sf-dump-1735167352 data-indent-pad=\"  \"><span class=sf-dump-note>array:46<\/span> [<samp data-depth=1 class=sf-dump-expanded>\n  \"<span class=sf-dump-key>REDIRECT_UNIQUE_ID<\/span>\" => \"<span class=sf-dump-str title=\"27 characters\">ZzQQvy0gghpXBD9rKtrsGwAAAAQ<\/span>\"\n  \"<span class=sf-dump-key>REDIRECT_HTTPS<\/span>\" => \"<span class=sf-dump-str title=\"2 characters\">on<\/span>\"\n  \"<span class=sf-dump-key>REDIRECT_SSL_TLS_SNI<\/span>\" => \"<span class=sf-dump-str title=\"24 characters\">ecommerce.eimaths-th.com<\/span>\"\n  \"<span class=sf-dump-key>REDIRECT_STATUS<\/span>\" => \"<span class=sf-dump-str title=\"3 characters\">200<\/span>\"\n  \"<span class=sf-dump-key>UNIQUE_ID<\/span>\" => \"<span class=sf-dump-str title=\"27 characters\">ZzQQvy0gghpXBD9rKtrsGwAAAAQ<\/span>\"\n  \"<span class=sf-dump-key>HTTPS<\/span>\" => \"<span class=sf-dump-str title=\"2 characters\">on<\/span>\"\n  \"<span class=sf-dump-key>SSL_TLS_SNI<\/span>\" => \"<span class=sf-dump-str title=\"24 characters\">ecommerce.eimaths-th.com<\/span>\"\n  \"<span class=sf-dump-key>HTTP_HOST<\/span>\" => \"<span class=sf-dump-str title=\"24 characters\">ecommerce.eimaths-th.com<\/span>\"\n  \"<span class=sf-dump-key>HTTP_CONNECTION<\/span>\" => \"<span class=sf-dump-str title=\"10 characters\">keep-alive<\/span>\"\n  \"<span class=sf-dump-key>HTTP_SEC_CH_UA<\/span>\" => \"<span class=sf-dump-str title=\"65 characters\">&quot;Chromium&quot;;v=&quot;130&quot;, &quot;Google Chrome&quot;;v=&quot;130&quot;, &quot;Not?A_Brand&quot;;v=&quot;99&quot;<\/span>\"\n  \"<span class=sf-dump-key>HTTP_SEC_CH_UA_MOBILE<\/span>\" => \"<span class=sf-dump-str title=\"2 characters\">?0<\/span>\"\n  \"<span class=sf-dump-key>HTTP_SEC_CH_UA_PLATFORM<\/span>\" => \"<span class=sf-dump-str title=\"9 characters\">&quot;Windows&quot;<\/span>\"\n  \"<span class=sf-dump-key>HTTP_UPGRADE_INSECURE_REQUESTS<\/span>\" => \"<span class=sf-dump-str>1<\/span>\"\n  \"<span class=sf-dump-key>HTTP_USER_AGENT<\/span>\" => \"<span class=sf-dump-str title=\"111 characters\">Mozilla\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/130.0.0.0 Safari\/537.36<\/span>\"\n  \"<span class=sf-dump-key>HTTP_ACCEPT<\/span>\" => \"<span class=sf-dump-str title=\"135 characters\">text\/html,application\/xhtml+xml,application\/xml;q=0.9,image\/avif,image\/webp,image\/apng,*\/*;q=0.8,application\/signed-exchange;v=b3;q=0.7<\/span>\"\n  \"<span class=sf-dump-key>HTTP_SEC_FETCH_SITE<\/span>\" => \"<span class=sf-dump-str title=\"4 characters\">none<\/span>\"\n  \"<span class=sf-dump-key>HTTP_SEC_FETCH_MODE<\/span>\" => \"<span class=sf-dump-str title=\"8 characters\">navigate<\/span>\"\n  \"<span class=sf-dump-key>HTTP_SEC_FETCH_USER<\/span>\" => \"<span class=sf-dump-str title=\"2 characters\">?1<\/span>\"\n  \"<span class=sf-dump-key>HTTP_SEC_FETCH_DEST<\/span>\" => \"<span class=sf-dump-str title=\"8 characters\">document<\/span>\"\n  \"<span class=sf-dump-key>HTTP_ACCEPT_ENCODING<\/span>\" => \"<span class=sf-dump-str title=\"23 characters\">gzip, deflate, br, zstd<\/span>\"\n  \"<span class=sf-dump-key>HTTP_ACCEPT_LANGUAGE<\/span>\" => \"<span class=sf-dump-str title=\"35 characters\">th-TH,th;q=0.9,en-US;q=0.8,en;q=0.7<\/span>\"\n  \"<span class=sf-dump-key>HTTP_COOKIE<\/span>\" => \"<span class=sf-dump-str title=\"985 characters\">_gcl_au=1.1.1340509618.1731293720; _ga=GA1.1.436658098.1731293720; _ga_0CDRZY1XT1=GS1.1.1731465288.3.0.1731465296.0.0.0; _ga_ZV46434GKP=GS1.1.1731465288.3.0.1731465296.0.0.0; strictly_allow=1; performance_allow=1; functional_allow=1; targeting_allow=1; allow=1; XSRF-TOKEN=eyJpdiI6Im5hTWVyL0JKNjhqZ2Z1QjBXMGF3V0E9PSIsInZhbHVlIjoiU1NLU1VXY1k2Rmh0UUUydVNlYzRMNlluN01ucFh0MkVPeEFFbHFQVy9vMHpSNGgyanVQbjljdnVFdjVMSE9QUkxVMnlNQWJocml5a0Z2RE1PN0xaQ3U4clJ2MEtCZVVuQUhuZ2ZYYkFwOVpYcFlCVUR0dkhqY1BiUXRQTUJ3WjUiLCJtYWMiOiIxYTVjOTM3YTU2ZTFjNDNmYjAzMDNhYmExODBmNTdiMzBmMGE3YTYyNzUxOWU5YTI4MGYyNGMwYmE2MTJkMjkxIiwidGFnIjoiIn0%3D; eimaths_ecommerce_session=eyJpdiI6Im1Mem14K0c5NVhCaDVSemo3UnFRNkE9PSIsInZhbHVlIjoiaUZKYlRqOXBhNktIc1RHVTloMDl4U2VzNHFESEltSFJwZ3NFelpnNW1MRnpjV3lJNDFQZU9MMmxMd3pLcVdFM2xmd2RheXQxSDg4YjRWTlZ0bXRTT2Q5QTBLcVBqdFAyVHlhTy9ZTGJlK3F6eUFhTW1IaUdYNUNBWmFlZU5CU1giLCJtYWMiOiI5OGNiYjc2NjQ5YzZmMmQ0ZDQ1MDk3M2Y5YTNjYjdmMGRmY2JjNDI3NGQ5ZTQwYmVkYjZiYmVlMzYzMDRlYWM0IiwidGFnIjoiIn0%3D<\/span>\"\n  \"<span class=sf-dump-key>PATH<\/span>\" => \"<span class=sf-dump-str title=\"49 characters\">\/usr\/local\/sbin:\/usr\/local\/bin:\/usr\/sbin:\/usr\/bin<\/span>\"\n  \"<span class=sf-dump-key>SERVER_SIGNATURE<\/span>\" => \"\"\n  \"<span class=sf-dump-key>SERVER_SOFTWARE<\/span>\" => \"<span class=sf-dump-str title=\"51 characters\">Apache\/2.4.6 (CentOS) OpenSSL\/1.0.2k-fips PHP\/8.2.9<\/span>\"\n  \"<span class=sf-dump-key>SERVER_NAME<\/span>\" => \"<span class=sf-dump-str title=\"24 characters\">ecommerce.eimaths-th.com<\/span>\"\n  \"<span class=sf-dump-key>SERVER_ADDR<\/span>\" => \"<span class=sf-dump-str title=\"10 characters\">10.148.0.3<\/span>\"\n  \"<span class=sf-dump-key>SERVER_PORT<\/span>\" => \"<span class=sf-dump-str title=\"3 characters\">443<\/span>\"\n  \"<span class=sf-dump-key>REMOTE_ADDR<\/span>\" => \"<span class=sf-dump-str title=\"13 characters\">49.49.224.104<\/span>\"\n  \"<span class=sf-dump-key>DOCUMENT_ROOT<\/span>\" => \"<span class=sf-dump-str title=\"23 characters\">\/var\/www\/html\/ecommerce<\/span>\"\n  \"<span class=sf-dump-key>REQUEST_SCHEME<\/span>\" => \"<span class=sf-dump-str title=\"5 characters\">https<\/span>\"\n  \"<span class=sf-dump-key>CONTEXT_PREFIX<\/span>\" => \"\"\n  \"<span class=sf-dump-key>CONTEXT_DOCUMENT_ROOT<\/span>\" => \"<span class=sf-dump-str title=\"23 characters\">\/var\/www\/html\/ecommerce<\/span>\"\n  \"<span class=sf-dump-key>SERVER_ADMIN<\/span>\" => \"<span class=sf-dump-str title=\"14 characters\">root@localhost<\/span>\"\n  \"<span class=sf-dump-key>SCRIPT_FILENAME<\/span>\" => \"<span class=sf-dump-str title=\"33 characters\">\/var\/www\/html\/ecommerce\/index.php<\/span>\"\n  \"<span class=sf-dump-key>REMOTE_PORT<\/span>\" => \"<span class=sf-dump-str title=\"5 characters\">56432<\/span>\"\n  \"<span class=sf-dump-key>REDIRECT_URL<\/span>\" => \"<span class=sf-dump-str title=\"10 characters\">\/affiliate<\/span>\"\n  \"<span class=sf-dump-key>GATEWAY_INTERFACE<\/span>\" => \"<span class=sf-dump-str title=\"7 characters\">CGI\/1.1<\/span>\"\n  \"<span class=sf-dump-key>SERVER_PROTOCOL<\/span>\" => \"<span class=sf-dump-str title=\"8 characters\">HTTP\/1.1<\/span>\"\n  \"<span class=sf-dump-key>REQUEST_METHOD<\/span>\" => \"<span class=sf-dump-str title=\"3 characters\">GET<\/span>\"\n  \"<span class=sf-dump-key>QUERY_STRING<\/span>\" => \"\"\n  \"<span class=sf-dump-key>REQUEST_URI<\/span>\" => \"<span class=sf-dump-str title=\"10 characters\">\/affiliate<\/span>\"\n  \"<span class=sf-dump-key>SCRIPT_NAME<\/span>\" => \"<span class=sf-dump-str title=\"10 characters\">\/index.php<\/span>\"\n  \"<span class=sf-dump-key>PHP_SELF<\/span>\" => \"<span class=sf-dump-str title=\"10 characters\">\/index.php<\/span>\"\n  \"<span class=sf-dump-key>REQUEST_TIME_FLOAT<\/span>\" => <span class=sf-dump-num>1731465407.3999<\/span>\n  \"<span class=sf-dump-key>REQUEST_TIME<\/span>\" => <span class=sf-dump-num>1731465407<\/span>\n<\/samp>]\n<\/pre><script>Sfdump(\"sf-dump-1735167352\", {\"maxDepth\":0})<\/script>\n",
            "request_cookies": "<pre class=sf-dump id=sf-dump-165731796 data-indent-pad=\"  \"><span class=sf-dump-note>array:11<\/span> [<samp data-depth=1 class=sf-dump-expanded>\n  \"<span class=sf-dump-key>_gcl_au<\/span>\" => <span class=sf-dump-const>null<\/span>\n  \"<span class=sf-dump-key>_ga<\/span>\" => <span class=sf-dump-const>null<\/span>\n  \"<span class=sf-dump-key>_ga_0CDRZY1XT1<\/span>\" => <span class=sf-dump-const>null<\/span>\n  \"<span class=sf-dump-key>_ga_ZV46434GKP<\/span>\" => <span class=sf-dump-const>null<\/span>\n  \"<span class=sf-dump-key>strictly_allow<\/span>\" => <span class=sf-dump-const>null<\/span>\n  \"<span class=sf-dump-key>performance_allow<\/span>\" => <span class=sf-dump-const>null<\/span>\n  \"<span class=sf-dump-key>functional_allow<\/span>\" => <span class=sf-dump-const>null<\/span>\n  \"<span class=sf-dump-key>targeting_allow<\/span>\" => <span class=sf-dump-const>null<\/span>\n  \"<span class=sf-dump-key>allow<\/span>\" => <span class=sf-dump-const>null<\/span>\n  \"<span class=sf-dump-key>XSRF-TOKEN<\/span>\" => \"<span class=sf-dump-str title=\"40 characters\">UXqxMUzO3NAybCH6WduKA4cNN4PbjD0GdofYpyEl<\/span>\"\n  \"<span class=sf-dump-key>eimaths_ecommerce_session<\/span>\" => \"<span class=sf-dump-str title=\"40 characters\">FPrOMKAZWterAqd4vP8gQSeeDSTQXMQ2THOWLnri<\/span>\"\n<\/samp>]\n<\/pre><script>Sfdump(\"sf-dump-165731796\", {\"maxDepth\":0})<\/script>\n",
            "response_headers": "<pre class=sf-dump id=sf-dump-100347884 data-indent-pad=\"  \"><span class=sf-dump-note>array:5<\/span> [<samp data-depth=1 class=sf-dump-expanded>\n  \"<span class=sf-dump-key>content-type<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"24 characters\">text\/html; charset=UTF-8<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>cache-control<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"17 characters\">no-cache, private<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>date<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"29 characters\">Wed, 13 Nov 2024 02:36:47 GMT<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>set-cookie<\/span>\" => <span class=sf-dump-note>array:2<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"414 characters\">XSRF-TOKEN=eyJpdiI6InVYdjdKMEU4eXNyZWJ3SHh2UnU1NVE9PSIsInZhbHVlIjoibkhvOHViZVB3TXpITGxlSEN6eEtCMjdKMFd0aUx3L1B0NVczQ2FhZ05jS2tjc3htUzE2ODZNTVUzaGFUd2NOTHYvV3dlTitiV2I5QWY5U0g2aW5BSXQ2UUs0WDZJekNDLzlLVGw2eDZqcytnUXZhbUpDOHY1MUlNcHI3ZVZnQ28iLCJtYWMiOiI4M2M5NDJmYmJlMjg1ODIxNjk3OTE0MjVjZGZiZDQ4ZTQwOTQ5MjcxMzI0YzFjZThjMDMzYThhNjhkYmY1ZDY2IiwidGFnIjoiIn0%3D; expires=Wed, 13 Nov 2024 04:36:47 GMT; Max-Age=7200; path=\/<\/span>\"\n    <span class=sf-dump-index>1<\/span> => \"<span class=sf-dump-str title=\"439 characters\">eimaths_ecommerce_session=eyJpdiI6ImF0SmFnNFcvaUFCMzltdlczZ3F6ZUE9PSIsInZhbHVlIjoiYnc5RmFOWE02cUFkTmpoSHNVTWhzRkd3Y3F0L0RueXNnWHpBVitRKzRFMkpYWk5FUUJCZlUreDUyZHZNdFFhcEc0QjZvVkR5RUFvNDVodWFkcXJiWlVwN2F5Mng4WndtUWhJRERKUGVQdXY5d0NncEp1TlFnV0k1M1ZSUzZhY2ciLCJtYWMiOiI2YTc1NDY1NWRiMmM0NWM4YjM0NDkwNmZkZTYzZDQ4Y2JkNjIyOGI5YmExMzNlNTQ5ZWQ3MjYzMWUxYzVlNWRkIiwidGFnIjoiIn0%3D; expires=Wed, 13 Nov 2024 04:36:47 GMT; Max-Age=7200; path=\/; httponly<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>Set-Cookie<\/span>\" => <span class=sf-dump-note>array:2<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    <span class=sf-dump-index>0<\/span> => \"<span class=sf-dump-str title=\"400 characters\">XSRF-TOKEN=eyJpdiI6InVYdjdKMEU4eXNyZWJ3SHh2UnU1NVE9PSIsInZhbHVlIjoibkhvOHViZVB3TXpITGxlSEN6eEtCMjdKMFd0aUx3L1B0NVczQ2FhZ05jS2tjc3htUzE2ODZNTVUzaGFUd2NOTHYvV3dlTitiV2I5QWY5U0g2aW5BSXQ2UUs0WDZJekNDLzlLVGw2eDZqcytnUXZhbUpDOHY1MUlNcHI3ZVZnQ28iLCJtYWMiOiI4M2M5NDJmYmJlMjg1ODIxNjk3OTE0MjVjZGZiZDQ4ZTQwOTQ5MjcxMzI0YzFjZThjMDMzYThhNjhkYmY1ZDY2IiwidGFnIjoiIn0%3D; expires=Wed, 13-Nov-2024 04:36:47 GMT; path=\/<\/span>\"\n    <span class=sf-dump-index>1<\/span> => \"<span class=sf-dump-str title=\"425 characters\">eimaths_ecommerce_session=eyJpdiI6ImF0SmFnNFcvaUFCMzltdlczZ3F6ZUE9PSIsInZhbHVlIjoiYnc5RmFOWE02cUFkTmpoSHNVTWhzRkd3Y3F0L0RueXNnWHpBVitRKzRFMkpYWk5FUUJCZlUreDUyZHZNdFFhcEc0QjZvVkR5RUFvNDVodWFkcXJiWlVwN2F5Mng4WndtUWhJRERKUGVQdXY5d0NncEp1TlFnV0k1M1ZSUzZhY2ciLCJtYWMiOiI2YTc1NDY1NWRiMmM0NWM4YjM0NDkwNmZkZTYzZDQ4Y2JkNjIyOGI5YmExMzNlNTQ5ZWQ3MjYzMWUxYzVlNWRkIiwidGFnIjoiIn0%3D; expires=Wed, 13-Nov-2024 04:36:47 GMT; path=\/; httponly<\/span>\"\n  <\/samp>]\n<\/samp>]\n<\/pre><script>Sfdump(\"sf-dump-100347884\", {\"maxDepth\":0})<\/script>\n",
            "session_attributes": "<pre class=sf-dump id=sf-dump-178392542 data-indent-pad=\"  \"><span class=sf-dump-note>array:5<\/span> [<samp data-depth=1 class=sf-dump-expanded>\n  \"<span class=sf-dump-key>domain<\/span>\" => \"<span class=sf-dump-str title=\"4 characters\">main<\/span>\"\n  \"<span class=sf-dump-key>_token<\/span>\" => \"<span class=sf-dump-str title=\"40 characters\">UXqxMUzO3NAybCH6WduKA4cNN4PbjD0GdofYpyEl<\/span>\"\n  \"<span class=sf-dump-key>_previous<\/span>\" => <span class=sf-dump-note>array:1<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    \"<span class=sf-dump-key>url<\/span>\" => \"<span class=sf-dump-str title=\"42 characters\">https:\/\/ecommerce.eimaths-th.com\/affiliate<\/span>\"\n  <\/samp>]\n  \"<span class=sf-dump-key>_flash<\/span>\" => <span class=sf-dump-note>array:2<\/span> [<samp data-depth=2 class=sf-dump-compact>\n    \"<span class=sf-dump-key>old<\/span>\" => []\n    \"<span class=sf-dump-key>new<\/span>\" => []\n  <\/samp>]\n  \"<span class=sf-dump-key>PHPDEBUGBAR_STACK_DATA<\/span>\" => []\n<\/samp>]\n<\/pre><script>Sfdump(\"sf-dump-178392542\", {\"maxDepth\":0})<\/script>\n"
        }
    }, "Xe03d2b2dd0d22ab32b2cee001983f83f");
</script> --}}