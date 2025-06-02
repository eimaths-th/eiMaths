{{-- <div id="et_pb_contact_form_0" class="et_pb_module et_pb_contact_form_0 et_pb_contact_form_container clearfix"
    data-form_unique_num="0" data-form_unique_id="d2497ece-0381-41d0-baef-98a2b5aceb12">

    <h1 class="et_pb_contact_main_title">

        @if (__('messages.contact_title_2_1'))
            {{ __('messages.contact_title_2_1') }}
        @else
            Contact Us
        @endif

    </h1>
    <div id="error-container" class="et-pb-contact-message" style="display: none;"></div>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

  
    <p id="response-container"> </p>
    <div class="et_pb_contact">
       <form class="et_pb_contact_form clearfix" method="POST" id="form_contact" action="/send-email">
            @csrf
            <p class="et_pb_contact_field et_pb_contact_field_0 et_pb_contact_field_half" data-id="fname"
                data-type="input">

                <label for="et_pb_contact_fname_0" class="et_pb_contact_form_label">Firs Name</label>
                <input type="text" id="fname" class="input" value="" name="fname" required
                    data-field_type="input" data-original_id="fname" placeholder="Firs Name">


            <p class="et_pb_contact_field et_pb_contact_field_1 et_pb_contact_field_half et_pb_contact_field_last"
                data-id="lname" data-type="input">

                <label for="et_pb_contact_lname_0" class="et_pb_contact_form_label">Last Name</label>
                <input type="text" id="lname" class="input" value="" name="lname"
                    data-required_mark="required" data-field_type="input" data-original_id="lname"
                    placeholder="Last Name">
            </p>
            <p class="et_pb_contact_field et_pb_contact_field_2 et_pb_contact_field_half" data-id="phone"
                data-type="input">

                <label for="et_pb_contact_phone_0" class="et_pb_contact_form_label">Phone
                    Number</label>
                <input type="text" id="telp" class="input" value="" name="telp"
                    data-required_mark="required" data-field_type="input" data-original_id="phone"
                    placeholder="Phone Number">
            </p>

            <p class="et_pb_contact_field et_pb_contact_field_1 et_pb_contact_field_half et_pb_contact_field_last"
                data-id="email" data-type="email">

                <label for="et_pb_contact_email_0" class="et_pb_contact_form_label">Email
                    Address</label>
                <input type="text" id="email" class="input" value="" name="email"
                    data-required_mark="required" data-field_type="email" data-original_id="email"
                    placeholder="Email Address">
            </p>

            <p class="et_pb_contact_field et_pb_contact_field_3 et_pb_contact_field_half " data-id="topic"
                data-type="select">

                <label for="et_pb_contact_topic_0" class="et_pb_contact_form_label">Topic Of
                    Enquiry</label>
                <select id="topic" class="et_pb_contact_select input" name="topic"
                    data-required_mark="not_required" data-field_type="select" data-original_id="topic">
                    <option value="">Topic Of Enquiry</option>
                    <option value="Parents Enquiries">Parents Enquiries
                    </option>
                    <option value="Franchising Enquiries">Franchising
                        Enquiries</option>
                    <option value="Career Enquiries">Career Enquiries
                    </option>
                    <option value="General Enquiries">General Enquiries
                    </option>
                    <option value="Trial Class">Trial Class</option>
                </select>
            </p>
            <p class="et_pb_contact_field et_pb_contact_field_4 et_pb_contact_field_half et_pb_contact_field_last"
                data-id="level" data-type="select">

                <label for="et_pb_contact_level_0" class="et_pb_contact_form_label">My Child&#039;s
                    Level in School</label>
                <select id="level" class="et_pb_contact_select input" name="level" data-required_mark="required"
                    data-field_type="select" data-original_id="level">
                    <option value="">My Child&#039;s Level in
                        School</option>
                    <option value="K1">K1</option>
                    <option value="K2">K2</option>
                    <option value="P1">P1</option>
                    <option value="P2">P2</option>
                    <option value="P3">P3</option>
                    <option value="P4">P4</option>
                    <option value="P5">P5</option>
                    <option value="Not Applicable">Not Applicable
                    </option>
                </select>
            </p>
            <p class="et_pb_contact_field et_pb_contact_field_6 et_pb_contact_field_last" data-id="message"
                data-type="text">

                <label for="et_pb_contact_message_0" class="et_pb_contact_form_label">Message</label>
                <textarea name="message" id="message" class="et_pb_contact_message input" data-required_mark="not_required"
                    data-field_type="text" data-original_id="message" placeholder="Message"></textarea>
            </p>
            <p class="et_pb_contact_field et_pb_contact_field_7 et_pb_contact_field_last" data-id="pdpa"
                data-type="checkbox">

                <label for="et_pb_contact_pdpa_0" class="et_pb_contact_form_label">

                    @if (__('messages.contact_title_2_1_det'))
                        {{ __('messages.contact_title_2_1_det') }}
                    @else
                        Please Tick
                    @endif

                </label>
                <input class="et_pb_checkbox_handle" type="hidden" 
                    data-required_mark="required" data-field_type="checkbox" data-original_id="pdpa">
                <span class="et_pb_contact_field_options_wrapper">
                    <span class="et_pb_contact_field_options_title">
                        @if (__('messages.contact_title_2_1_det'))
                            {{ __('messages.contact_title_2_1_det') }}
                        @else
                            Please Tick
                        @endif
                    </span>
                    <span class="et_pb_contact_field_options_list">
                        <span class="et_pb_contact_field_checkbox">
                            <input type="checkbox" id="et_pb_contact_pdpa_7_0" class="input"
                                name="pdpa" required value="1" data-id="-1">
                            <label for="et_pb_contact_pdpa_7_0"><i></i>

                                @if (__('messages.contact_title_2_1_pdpa'))
                                    {{ __('messages.contact_title_2_1_pdpa') }}
                                @else
                                    I
                                    acknowledge and agree that teachers
                                    from
                                    eiMaths will contact me to share
                                    information about the program and
                                    any
                                    future promotion in accordance with
                                    PDPA
                                    guidelines.
                                @endif

                            </label>
                        </span>
                    </span>
                </span>
            </p><div class="et_contact_bottom_container">

                <button type="submit" name="submit_button" id="SubmitCreateForm"
                    class="et_pb_contact_submit et_pb_button" data-icon="&#xe076;">Submit enquiry</button>
            </div>
        </form>
        <script>
            //ถึง pdpa
            $('#SubmitCreateForm').click(function(e) {
                e.preventDefault();
                var fname = $('#fname').val();
                var lname = $('#lname').val();
                var telp = $('#telp').val();
                var email = $('#email').val();
                var topic = $('#topic').val();
                var level = $('#level').val();
                var message = $('#message').val();
                var pdpa = $('#et_pb_contact_pdpa_7_0').prop('checked');
                // var pdpa = $('#et_pb_contact_pdpa_7_0').val();
                var token = $('meta[name="csrf-token"]').attr('content');

                // var pdpa = $('#et_pb_contact_pdpa_7_0').val();
                if (pdpa) {
                    // Checkbox is checked
                    pdpa = 1;

                } else {
                    // Checkbox is not checked
                    var pdpa = 0;

                }
                if (pdpa === 0) {
                    alert('You have not tick verified.');
                } else {

                    $.ajax({
                        url: "/send-email",
                        method: 'post',
                        data: {
                            fname: fname,
                            lname: lname,
                            telp: telp,
                            email: email,
                            topic: topic,
                            level: level,
                            message: message,
                            pdpa: pdpa,
                            _token: token,
                        },
                        success: function(result) {

                            var pdpa = $('#form_contact').hide();
                            var pdpa = $('#error-container').hide();
                            // $('#msg').text(result.msg);
                            // $('#msg').text(result.msg);
                            // $('#msg2').text(result.msg2);
                            $('#response-container').html(result.html);


                        },
                        error: function(xhr) {
                            // Handle errors here

                            // Parse the JSON response (if the server returns JSON error messages)
                            var errorResponse;
                            try {
                                errorResponse = JSON.parse(xhr.responseText);
                            } catch (e) {
                                // If the response is not JSON, handle the error appropriately (optional)
                                console.error('Error parsing JSON response:', e);
                                return; // Exit the error handling function
                            }

                            if (errorResponse && errorResponse.errors) {
                                // Display the errors in the error container
                                var errorContainer = $('#error-container');
                                errorContainer.empty();
                                errorContainer.show();

                                // Append each error message to the container
                                $.each(errorResponse.errors, function(field, messages) {
                                    $.each(messages, function(index, message) {
                                        errorContainer.append('<li>' + message + '</li>');
                                    });
                                });
                            } else {
                                // Handle the case where there are no errors but other error responses (optional)
                                console.error('Unexpected error response:', errorResponse);
                            }
                        }

                    });

                }
                console.log(pdpa);


            });
        </script>
    </div>
</div> --}}



<!DOCTYPE html>
<html lang="en-US">

<!-- Mirrored from eimaths.com/contact-us/ by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 03 Jul 2023 08:14:14 GMT -->
<!-- Added by HTTrack -->
<meta http-equiv="content-type" content="text/html;charset=UTF-8" /><!-- /Added by HTTrack -->

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="pingback" href="../xmlrpc.php" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script type="text/javascript">
        document.documentElement.className = 'js';
    </script>

    <meta name='robots' content='index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' />
    <script type="text/javascript">
        let jqueryParams = [],
            jQuery = function(r) {
                return jqueryParams = [...jqueryParams, r], jQuery
            },
            $ = function(r) {
                return jqueryParams = [...jqueryParams, r], $
            };
        window.jQuery = jQuery, window.$ = jQuery;
        let customHeadScripts = !1;
        jQuery.fn = jQuery.prototype = {}, $.fn = jQuery.prototype = {}, jQuery.noConflict = function(r) {
            if (window.jQuery) return jQuery = window.jQuery, $ = window.jQuery, customHeadScripts = !0, jQuery
                .noConflict
        }, jQuery.ready = function(r) {
            jqueryParams = [...jqueryParams, r]
        }, $.ready = function(r) {
            jqueryParams = [...jqueryParams, r]
        }, jQuery.load = function(r) {
            jqueryParams = [...jqueryParams, r]
        }, $.load = function(r) {
            jqueryParams = [...jqueryParams, r]
        }, jQuery.fn.ready = function(r) {
            jqueryParams = [...jqueryParams, r]
        }, $.fn.ready = function(r) {
            jqueryParams = [...jqueryParams, r]
        };
    </script>
    <!-- This site is optimized with the Yoast SEO plugin v19.13 - https://yoast.com/wordpress/plugins/seo/ -->
    <title>Form - eiMaths TH</title>
    <meta name="description"
        content="Time adjustment is one of the biggest reasons Because of this, most parents use internet-based math tutorials to save time. If you wish to have that perfect maths class for your kid then contact us now!" />
    <link rel="canonical" href="index.html" />
    <meta property="og:locale" content="en_US" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="Form- eiMaths" />
    <meta property="og:description"
        content="Time adjustment is one of the biggest reasons Because of this, most parents use internet-based math tutorials to save time. If you wish to have that perfect maths class for your kid then contact us now!" />
    <meta property="og:url" content="https://eimaths.com/contact-us/" />
    <meta property="og:site_name" content="eiMaths" />
    <meta property="article:modified_time" content="2023-05-05T04:35:45+00:00" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:label1" content="Est. reading time" />
    <meta name="twitter:data1" content="23 minutes" />
    <script type="application/ld+json" class="yoast-schema-graph">{"@context":"https://schema.org","@graph":[{"@type":"WebPage","@id":"https://eimaths.com/contact-us/","url":"https://eimaths.com/contact-us/","name":"Contact us - eiMaths","isPartOf":{"@id":"https://eimaths.com/#website"},"datePublished":"2019-09-30T13:05:43+00:00","dateModified":"2023-05-05T04:35:45+00:00","description":"Time adjustment is one of the biggest reasons Because of this, most parents use internet-based math tutorials to save time. If you wish to have that perfect maths class for your kid then contact us now!","breadcrumb":{"@id":"https://eimaths.com/contact-us/#breadcrumb"},"inLanguage":"en-US","potentialAction":[{"@type":"ReadAction","target":["https://eimaths.com/contact-us/"]}]},{"@type":"BreadcrumbList","@id":"https://eimaths.com/contact-us/#breadcrumb","itemListElement":[{"@type":"ListItem","position":1,"name":"Home","item":"https://eimaths.com/"},{"@type":"ListItem","position":2,"name":"Contact Us"}]},{"@type":"WebSite","@id":"https://eimaths.com/#website","url":"https://eimaths.com/","name":"eiMaths","description":"Best Maths Enrichment and Learning School","potentialAction":[{"@type":"SearchAction","target":{"@type":"EntryPoint","urlTemplate":"https://eimaths.com/?s={search_term_string}"},"query-input":"required name=search_term_string"}],"inLanguage":"en-US"}]}</script>
    <!-- / Yoast SEO plugin. -->


    <link rel='dns-prefetch' href='http://maxcdn.bootstrapcdn.com/' />
    <link rel='dns-prefetch' href='http://cdnjs.cloudflare.com/' />
    <link rel='dns-prefetch' href='http://fonts.googleapis.com/' />
    <link rel="alternate" type="application/rss+xml" title="eiMaths &raquo; Feed" href="../feed/index.html" />
    <link rel="alternate" type="application/rss+xml" title="eiMaths &raquo; Comments Feed"
        href="../comments/feed/index.html" />
    <meta content="Divi v.4.19.1" name="generator" />
    <style id='wp-block-library-theme-inline-css' type='text/css'>
        .wp-block-audio figcaption {
            color: #555;
            font-size: 13px;
            text-align: center
        }

        .is-dark-theme .wp-block-audio figcaption {
            color: hsla(0, 0%, 100%, .65)
        }

        .wp-block-code {
            border: 1px solid #ccc;
            border-radius: 4px;
            font-family: Menlo, Consolas, monaco, monospace;
            padding: .8em 1em
        }

        .wp-block-embed figcaption {
            color: #555;
            font-size: 13px;
            text-align: center
        }

        .is-dark-theme .wp-block-embed figcaption {
            color: hsla(0, 0%, 100%, .65)
        }

        .blocks-gallery-caption {
            color: #555;
            font-size: 13px;
            text-align: center
        }

        .is-dark-theme .blocks-gallery-caption {
            color: hsla(0, 0%, 100%, .65)
        }

        .wp-block-image figcaption {
            color: #555;
            font-size: 13px;
            text-align: center
        }

        .is-dark-theme .wp-block-image figcaption {
            color: hsla(0, 0%, 100%, .65)
        }

        .wp-block-pullquote {
            border-top: 4px solid;
            border-bottom: 4px solid;
            margin-bottom: 1.75em;
            color: currentColor
        }

        .wp-block-pullquote__citation,
        .wp-block-pullquote cite,
        .wp-block-pullquote footer {
            color: currentColor;
            text-transform: uppercase;
            font-size: .8125em;
            font-style: normal
        }

        .wp-block-quote {
            border-left: .25em solid;
            margin: 0 0 1.75em;
            padding-left: 1em
        }

        .wp-block-quote cite,
        .wp-block-quote footer {
            color: currentColor;
            font-size: .8125em;
            position: relative;
            font-style: normal
        }

        .wp-block-quote.has-text-align-right {
            border-left: none;
            border-right: .25em solid;
            padding-left: 0;
            padding-right: 1em
        }

        .wp-block-quote.has-text-align-center {
            border: none;
            padding-left: 0
        }

        .wp-block-quote.is-large,
        .wp-block-quote.is-style-large,
        .wp-block-quote.is-style-plain {
            border: none
        }

        .wp-block-search .wp-block-search__label {
            font-weight: 700
        }

        :where(.wp-block-group.has-background) {
            padding: 1.25em 2.375em
        }

        .wp-block-separator.has-css-opacity {
            opacity: .4
        }

        .wp-block-separator {
            border: none;
            border-bottom: 2px solid;
            margin-left: auto;
            margin-right: auto
        }

        .wp-block-separator.has-alpha-channel-opacity {
            opacity: 1
        }

        .wp-block-separator:not(.is-style-wide):not(.is-style-dots) {
            width: 100px
        }

        .wp-block-separator.has-background:not(.is-style-dots) {
            border-bottom: none;
            height: 1px
        }

        .wp-block-separator.has-background:not(.is-style-wide):not(.is-style-dots) {
            height: 2px
        }

        .wp-block-table thead {
            border-bottom: 3px solid
        }

        .wp-block-table tfoot {
            border-top: 3px solid
        }

        .wp-block-table td,
        .wp-block-table th {
            padding: .5em;
            border: 1px solid;
            word-break: normal
        }

        .wp-block-table figcaption {
            color: #555;
            font-size: 13px;
            text-align: center
        }

        .is-dark-theme .wp-block-table figcaption {
            color: hsla(0, 0%, 100%, .65)
        }

        .wp-block-video figcaption {
            color: #555;
            font-size: 13px;
            text-align: center
        }

        .is-dark-theme .wp-block-video figcaption {
            color: hsla(0, 0%, 100%, .65)
        }

        .wp-block-template-part.has-background {
            padding: 1.25em 2.375em;
            margin-top: 0;
            margin-bottom: 0
        }
    </style>
    <link rel='stylesheet' id='wc-blocks-vendors-style-css'
        href='https://www.eimaths-th.com/wp-content/plugins/woocommerce/packages/woocommerce-blocks/build/wc-blocks-vendors-style1da8.css?ver=8.9.2'
        type='text/css' media='all' />
    <link rel='stylesheet' id='wc-blocks-style-css'
        href='https://www.eimaths-th.com/wp-content/plugins/woocommerce/packages/woocommerce-blocks/build/wc-blocks-style1da8.css?ver=8.9.2'
        type='text/css' media='all' />
    <style id='global-styles-inline-css' type='text/css'>
        body {
            --wp--preset--color--black: #000000;
            --wp--preset--color--cyan-bluish-gray: #abb8c3;
            --wp--preset--color--white: #ffffff;
            --wp--preset--color--pale-pink: #f78da7;
            --wp--preset--color--vivid-red: #cf2e2e;
            --wp--preset--color--luminous-vivid-orange: #ff6900;
            --wp--preset--color--luminous-vivid-amber: #fcb900;
            --wp--preset--color--light-green-cyan: #7bdcb5;
            --wp--preset--color--vivid-green-cyan: #00d084;
            --wp--preset--color--pale-cyan-blue: #8ed1fc;
            --wp--preset--color--vivid-cyan-blue: #0693e3;
            --wp--preset--color--vivid-purple: #9b51e0;
            --wp--preset--gradient--vivid-cyan-blue-to-vivid-purple: linear-gradient(135deg, rgba(6, 147, 227, 1) 0%, rgb(155, 81, 224) 100%);
            --wp--preset--gradient--light-green-cyan-to-vivid-green-cyan: linear-gradient(135deg, rgb(122, 220, 180) 0%, rgb(0, 208, 130) 100%);
            --wp--preset--gradient--luminous-vivid-amber-to-luminous-vivid-orange: linear-gradient(135deg, rgba(252, 185, 0, 1) 0%, rgba(255, 105, 0, 1) 100%);
            --wp--preset--gradient--luminous-vivid-orange-to-vivid-red: linear-gradient(135deg, rgba(255, 105, 0, 1) 0%, rgb(207, 46, 46) 100%);
            --wp--preset--gradient--very-light-gray-to-cyan-bluish-gray: linear-gradient(135deg, rgb(238, 238, 238) 0%, rgb(169, 184, 195) 100%);
            --wp--preset--gradient--cool-to-warm-spectrum: linear-gradient(135deg, rgb(74, 234, 220) 0%, rgb(151, 120, 209) 20%, rgb(207, 42, 186) 40%, rgb(238, 44, 130) 60%, rgb(251, 105, 98) 80%, rgb(254, 248, 76) 100%);
            --wp--preset--gradient--blush-light-purple: linear-gradient(135deg, rgb(255, 206, 236) 0%, rgb(152, 150, 240) 100%);
            --wp--preset--gradient--blush-bordeaux: linear-gradient(135deg, rgb(254, 205, 165) 0%, rgb(254, 45, 45) 50%, rgb(107, 0, 62) 100%);
            --wp--preset--gradient--luminous-dusk: linear-gradient(135deg, rgb(255, 203, 112) 0%, rgb(199, 81, 192) 50%, rgb(65, 88, 208) 100%);
            --wp--preset--gradient--pale-ocean: linear-gradient(135deg, rgb(255, 245, 203) 0%, rgb(182, 227, 212) 50%, rgb(51, 167, 181) 100%);
            --wp--preset--gradient--electric-grass: linear-gradient(135deg, rgb(202, 248, 128) 0%, rgb(113, 206, 126) 100%);
            --wp--preset--gradient--midnight: linear-gradient(135deg, rgb(2, 3, 129) 0%, rgb(40, 116, 252) 100%);
            --wp--preset--duotone--dark-grayscale: url('#wp-duotone-dark-grayscale');
            --wp--preset--duotone--grayscale: url('#wp-duotone-grayscale');
            --wp--preset--duotone--purple-yellow: url('#wp-duotone-purple-yellow');
            --wp--preset--duotone--blue-red: url('#wp-duotone-blue-red');
            --wp--preset--duotone--midnight: url('#wp-duotone-midnight');
            --wp--preset--duotone--magenta-yellow: url('#wp-duotone-magenta-yellow');
            --wp--preset--duotone--purple-green: url('#wp-duotone-purple-green');
            --wp--preset--duotone--blue-orange: url('#wp-duotone-blue-orange');
            --wp--preset--font-size--small: 13px;
            --wp--preset--font-size--medium: 20px;
            --wp--preset--font-size--large: 36px;
            --wp--preset--font-size--x-large: 42px;
        }

        body {
            margin: 0;
        }

        .wp-site-blocks>.alignleft {
            float: left;
            margin-right: 2em;
        }

        .wp-site-blocks>.alignright {
            float: right;
            margin-left: 2em;
        }

        .wp-site-blocks>.aligncenter {
            justify-content: center;
            margin-left: auto;
            margin-right: auto;
        }

        .has-black-color {
            color: var(--wp--preset--color--black) !important;
        }

        .has-cyan-bluish-gray-color {
            color: var(--wp--preset--color--cyan-bluish-gray) !important;
        }

        .has-white-color {
            color: var(--wp--preset--color--white) !important;
        }

        .has-pale-pink-color {
            color: var(--wp--preset--color--pale-pink) !important;
        }

        .has-vivid-red-color {
            color: var(--wp--preset--color--vivid-red) !important;
        }

        .has-luminous-vivid-orange-color {
            color: var(--wp--preset--color--luminous-vivid-orange) !important;
        }

        .has-luminous-vivid-amber-color {
            color: var(--wp--preset--color--luminous-vivid-amber) !important;
        }

        .has-light-green-cyan-color {
            color: var(--wp--preset--color--light-green-cyan) !important;
        }

        .has-vivid-green-cyan-color {
            color: var(--wp--preset--color--vivid-green-cyan) !important;
        }

        .has-pale-cyan-blue-color {
            color: var(--wp--preset--color--pale-cyan-blue) !important;
        }

        .has-vivid-cyan-blue-color {
            color: var(--wp--preset--color--vivid-cyan-blue) !important;
        }

        .has-vivid-purple-color {
            color: var(--wp--preset--color--vivid-purple) !important;
        }

        .has-black-background-color {
            background-color: var(--wp--preset--color--black) !important;
        }

        .has-cyan-bluish-gray-background-color {
            background-color: var(--wp--preset--color--cyan-bluish-gray) !important;
        }

        .has-white-background-color {
            background-color: var(--wp--preset--color--white) !important;
        }

        .has-pale-pink-background-color {
            background-color: var(--wp--preset--color--pale-pink) !important;
        }

        .has-vivid-red-background-color {
            background-color: var(--wp--preset--color--vivid-red) !important;
        }

        .has-luminous-vivid-orange-background-color {
            background-color: var(--wp--preset--color--luminous-vivid-orange) !important;
        }

        .has-luminous-vivid-amber-background-color {
            background-color: var(--wp--preset--color--luminous-vivid-amber) !important;
        }

        .has-light-green-cyan-background-color {
            background-color: var(--wp--preset--color--light-green-cyan) !important;
        }

        .has-vivid-green-cyan-background-color {
            background-color: var(--wp--preset--color--vivid-green-cyan) !important;
        }

        .has-pale-cyan-blue-background-color {
            background-color: var(--wp--preset--color--pale-cyan-blue) !important;
        }

        .has-vivid-cyan-blue-background-color {
            background-color: var(--wp--preset--color--vivid-cyan-blue) !important;
        }

        .has-vivid-purple-background-color {
            background-color: var(--wp--preset--color--vivid-purple) !important;
        }

        .has-black-border-color {
            border-color: var(--wp--preset--color--black) !important;
        }

        .has-cyan-bluish-gray-border-color {
            border-color: var(--wp--preset--color--cyan-bluish-gray) !important;
        }

        .has-white-border-color {
            border-color: var(--wp--preset--color--white) !important;
        }

        .has-pale-pink-border-color {
            border-color: var(--wp--preset--color--pale-pink) !important;
        }

        .has-vivid-red-border-color {
            border-color: var(--wp--preset--color--vivid-red) !important;
        }

        .has-luminous-vivid-orange-border-color {
            border-color: var(--wp--preset--color--luminous-vivid-orange) !important;
        }

        .has-luminous-vivid-amber-border-color {
            border-color: var(--wp--preset--color--luminous-vivid-amber) !important;
        }

        .has-light-green-cyan-border-color {
            border-color: var(--wp--preset--color--light-green-cyan) !important;
        }

        .has-vivid-green-cyan-border-color {
            border-color: var(--wp--preset--color--vivid-green-cyan) !important;
        }

        .has-pale-cyan-blue-border-color {
            border-color: var(--wp--preset--color--pale-cyan-blue) !important;
        }

        .has-vivid-cyan-blue-border-color {
            border-color: var(--wp--preset--color--vivid-cyan-blue) !important;
        }

        .has-vivid-purple-border-color {
            border-color: var(--wp--preset--color--vivid-purple) !important;
        }

        .has-vivid-cyan-blue-to-vivid-purple-gradient-background {
            background: var(--wp--preset--gradient--vivid-cyan-blue-to-vivid-purple) !important;
        }

        .has-light-green-cyan-to-vivid-green-cyan-gradient-background {
            background: var(--wp--preset--gradient--light-green-cyan-to-vivid-green-cyan) !important;
        }

        .has-luminous-vivid-amber-to-luminous-vivid-orange-gradient-background {
            background: var(--wp--preset--gradient--luminous-vivid-amber-to-luminous-vivid-orange) !important;
        }

        .has-luminous-vivid-orange-to-vivid-red-gradient-background {
            background: var(--wp--preset--gradient--luminous-vivid-orange-to-vivid-red) !important;
        }

        .has-very-light-gray-to-cyan-bluish-gray-gradient-background {
            background: var(--wp--preset--gradient--very-light-gray-to-cyan-bluish-gray) !important;
        }

        .has-cool-to-warm-spectrum-gradient-background {
            background: var(--wp--preset--gradient--cool-to-warm-spectrum) !important;
        }

        .has-blush-light-purple-gradient-background {
            background: var(--wp--preset--gradient--blush-light-purple) !important;
        }

        .has-blush-bordeaux-gradient-background {
            background: var(--wp--preset--gradient--blush-bordeaux) !important;
        }

        .has-luminous-dusk-gradient-background {
            background: var(--wp--preset--gradient--luminous-dusk) !important;
        }

        .has-pale-ocean-gradient-background {
            background: var(--wp--preset--gradient--pale-ocean) !important;
        }

        .has-electric-grass-gradient-background {
            background: var(--wp--preset--gradient--electric-grass) !important;
        }

        .has-midnight-gradient-background {
            background: var(--wp--preset--gradient--midnight) !important;
        }

        .has-small-font-size {
            font-size: var(--wp--preset--font-size--small) !important;
        }

        .has-medium-font-size {
            font-size: var(--wp--preset--font-size--medium) !important;
        }

        .has-large-font-size {
            font-size: var(--wp--preset--font-size--large) !important;
        }

        .has-x-large-font-size {
            font-size: var(--wp--preset--font-size--x-large) !important;
        }
    </style>
    <link rel='stylesheet' id='eae-css-css'
        href='https://www.eimaths-th.com/wp-content/plugins/addon-elements-for-elementor-page-builder/assets/css/eae.min8392.css?ver=1.11.16'
        type='text/css' media='all' />
    <link rel='stylesheet' id='font-awesome-4-shim-css'
        href='https://www.eimaths-th.com/wp-content/plugins/elementor/assets/lib/font-awesome/css/v4-shims.min5152.css?ver=1.0'
        type='text/css' media='all' />
    <link rel='stylesheet' id='font-awesome-5-all-css'
        href='https://www.eimaths-th.com/wp-content/plugins/elementor/assets/lib/font-awesome/css/all.min5152.css?ver=1.0'
        type='text/css' media='all' />
    <link rel='stylesheet' id='vegas-css-css'
        href='https://www.eimaths-th.com/wp-content/plugins/addon-elements-for-elementor-page-builder/assets/lib/vegas/vegas.min8d5a.css?ver=2.4.0'
        type='text/css' media='all' />
    <link rel='stylesheet' id='wpa-css-css'
        href='https://www.eimaths-th.com/wp-content/cache/asset-cleanup/css/item/wpa-css-v019aa15fe666821a33f6b1d5113c92ae8c3ed53a.css'
        type='text/css' media='all' />

    <link rel='stylesheet' id='rs-plugin-settings-css'
        href='https://www.eimaths-th.com/wp-content/cache/asset-cleanup/css/item/rs-plugin-settings-vd5692f20957576fffd53389a71bf20900149fd1e.css'
        type='text/css' media='all' />
    <style id='rs-plugin-settings-inline-css' type='text/css'>
        #rs-demo-id {}
    </style>
    <link rel='stylesheet' id='sc_merged-css'
        href='https://www.eimaths-th.com/wp-content/cache/asset-cleanup/css/item/sc_merged-v03af4f9795d30743f93d1a8088c926c23a5d1393.css'
        type='text/css' media='all' />
    <style id='sc_merged-inline-css' type='text/css'>
        .supercarousel235552 .supercarousel>div {}

        .supercarousel235552 .supercarousel>div .super_overlay0 {
            background-color: rgba(0, 0, 0, 0);
            transition: none ease-in-out 400ms 0ms;
            -wekbit-transition: none ease-in-out 400ms 0ms;
            -moz-transition: none ease-in-out 400ms 0ms;
            -ms-transition: none ease-in-out 400ms 0ms;
            -o-transition: none ease-in-out 400ms 0ms;
            border-radius: 0px;
        }

        .supercarousel235552 .supercarousel>div:hover .super_overlay0 {
            background-color: rgba(0, 0, 0, 0);
        }

        .supercarousel235552 .supercarousel>div .superelement0 {
            transition: none ease-in-out 400ms 0ms;
            -wekbit-transition: none ease-in-out 400ms 0ms;
            -moz-transition: none ease-in-out 400ms 0ms;
            -ms-transition: none ease-in-out 400ms 0ms;
            -o-transition: none ease-in-out 400ms 0ms;
            object-position: center;
            border-left-width: 0px;
            border-left-style: solid;
            border-left-color: #444444;
            border-top-width: 0px;
            border-top-style: solid;
            border-top-color: #444444;
            border-right-width: 0px;
            border-right-style: solid;
            border-right-color: #444444;
            border-bottom-width: 0px;
            border-bottom-style: solid;
            border-bottom-color: #444444;
            background-color: rgba(0, 0, 0, 0);
            box-shadow: 0px 0px 0px #444444;
            opacity: 1.00;
            margin-top: 0px;
            margin-right: 0px;
            margin-bottom: 0px;
            margin-left: 0px;
            padding-top: 0px;
            padding-right: 0px;
            padding-bottom: 0px;
            padding-left: 0px;
            border-radius: 0px;
            transform: none;
            -wekbit-transform: none;
            -moz-transform: none;
            -ms-transform: none;
            -o-transform: none;
            filter: grayscale(0%);
        }

        .supercarousel235552 .supercarousel>div .superelement0:hover {
            border-width: 0px;
            border-style: solid;
            border-color: #444444;
            background-color: rgba(0, 0, 0, 0);
            box-shadow: 0px 0px 0px #444444;
            opacity: 1.00;
            transform: none;
            -wekbit-transform: none;
            -moz-transform: none;
            -ms-transform: none;
            -o-transform: none;
            filter: grayscale(0%);
        }

        .supercarousel235552 .supercarousel>div superelement0 a {
            transition: none ease-in-out 400ms 0ms;
            -wekbit-transition: none ease-in-out 400ms 0ms;
            -moz-transition: none ease-in-out 400ms 0ms;
            -ms-transition: none ease-in-out 400ms 0ms;
            -o-transition: none ease-in-out 400ms 0ms;
            object-position: center;
            border-left-width: 0px;
            border-left-style: solid;
            border-left-color: #444444;
            border-top-width: 0px;
            border-top-style: solid;
            border-top-color: #444444;
            border-right-width: 0px;
            border-right-style: solid;
            border-right-color: #444444;
            border-bottom-width: 0px;
            border-bottom-style: solid;
            border-bottom-color: #444444;
            background-color: rgba(0, 0, 0, 0);
            box-shadow: 0px 0px 0px #444444;
            opacity: 1.00;
            margin-top: 0px;
            margin-right: 0px;
            margin-bottom: 0px;
            margin-left: 0px;
            padding-top: 0px;
            padding-right: 0px;
            padding-bottom: 0px;
            padding-left: 0px;
            border-radius: 0px;
            transform: none;
            -wekbit-transform: none;
            -moz-transform: none;
            -ms-transform: none;
            -o-transform: none;
            filter: grayscale(0%);
        }

        .supercarousel235552 .supercarousel>div superelement0 a:hover {
            border-width: 0px;
            border-style: solid;
            border-color: #444444;
            background-color: rgba(0, 0, 0, 0);
            box-shadow: 0px 0px 0px #444444;
            opacity: 1.00;
            transform: none;
            -wekbit-transform: none;
            -moz-transform: none;
            -ms-transform: none;
            -o-transform: none;
            filter: grayscale(0%);
        }

        .supercarousel235513 .supercarousel>div {}

        .supercarousel235513 .supercarousel>div .super_overlay0 {
            background-color: rgba(0, 0, 0, 0);
            transition: none ease-in-out 400ms 0ms;
            -wekbit-transition: none ease-in-out 400ms 0ms;
            -moz-transition: none ease-in-out 400ms 0ms;
            -ms-transition: none ease-in-out 400ms 0ms;
            -o-transition: none ease-in-out 400ms 0ms;
            border-radius: 0px;
        }

        .supercarousel235513 .supercarousel>div:hover .super_overlay0 {
            background-color: rgba(0, 0, 0, 0);
        }

        .supercarousel235513 .supercarousel>div .superelement0 {
            transition: none ease-in-out 400ms 0ms;
            -wekbit-transition: none ease-in-out 400ms 0ms;
            -moz-transition: none ease-in-out 400ms 0ms;
            -ms-transition: none ease-in-out 400ms 0ms;
            -o-transition: none ease-in-out 400ms 0ms;
            object-position: center;
            border-left-width: 0px;
            border-left-style: solid;
            border-left-color: #444444;
            border-top-width: 0px;
            border-top-style: solid;
            border-top-color: #444444;
            border-right-width: 0px;
            border-right-style: solid;
            border-right-color: #444444;
            border-bottom-width: 0px;
            border-bottom-style: solid;
            border-bottom-color: #444444;
            background-color: rgba(0, 0, 0, 0);
            box-shadow: 0px 0px 0px #444444;
            opacity: 1.00;
            margin-top: 0px;
            margin-right: 0px;
            margin-bottom: 0px;
            margin-left: 0px;
            padding-top: 10px;
            padding-right: 10px;
            padding-bottom: 10px;
            padding-left: 10px;
            border-radius: 0px;
            transform: none;
            -wekbit-transform: none;
            -moz-transform: none;
            -ms-transform: none;
            -o-transform: none;
            filter: grayscale(0%);
        }

        .supercarousel235513 .supercarousel>div .superelement0:hover {
            border-width: 0px;
            border-style: solid;
            border-color: #444444;
            background-color: rgba(0, 0, 0, 0);
            box-shadow: 0px 0px 0px #444444;
            opacity: 1.00;
            transform: none;
            -wekbit-transform: none;
            -moz-transform: none;
            -ms-transform: none;
            -o-transform: none;
            filter: grayscale(0%);
        }

        .supercarousel235513 .supercarousel>div superelement0 a {
            transition: none ease-in-out 400ms 0ms;
            -wekbit-transition: none ease-in-out 400ms 0ms;
            -moz-transition: none ease-in-out 400ms 0ms;
            -ms-transition: none ease-in-out 400ms 0ms;
            -o-transition: none ease-in-out 400ms 0ms;
            object-position: center;
            border-left-width: 0px;
            border-left-style: solid;
            border-left-color: #444444;
            border-top-width: 0px;
            border-top-style: solid;
            border-top-color: #444444;
            border-right-width: 0px;
            border-right-style: solid;
            border-right-color: #444444;
            border-bottom-width: 0px;
            border-bottom-style: solid;
            border-bottom-color: #444444;
            background-color: rgba(0, 0, 0, 0);
            box-shadow: 0px 0px 0px #444444;
            opacity: 1.00;
            margin-top: 0px;
            margin-right: 0px;
            margin-bottom: 0px;
            margin-left: 0px;
            padding-top: 10px;
            padding-right: 10px;
            padding-bottom: 10px;
            padding-left: 10px;
            border-radius: 0px;
            transform: none;
            -wekbit-transform: none;
            -moz-transform: none;
            -ms-transform: none;
            -o-transform: none;
            filter: grayscale(0%);
        }

        .supercarousel235513 .supercarousel>div superelement0 a:hover {
            border-width: 0px;
            border-style: solid;
            border-color: #444444;
            background-color: rgba(0, 0, 0, 0);
            box-shadow: 0px 0px 0px #444444;
            opacity: 1.00;
            transform: none;
            -wekbit-transform: none;
            -moz-transform: none;
            -ms-transform: none;
            -o-transform: none;
            filter: grayscale(0%);
        }

        .supercarousel235373 .supercarousel>div {}

        .supercarousel235373 .supercarousel>div .super_overlay0 {
            background-color: rgba(0, 0, 0, 0);
            transition: none ease-in-out 400ms 0ms;
            -wekbit-transition: none ease-in-out 400ms 0ms;
            -moz-transition: none ease-in-out 400ms 0ms;
            -ms-transition: none ease-in-out 400ms 0ms;
            -o-transition: none ease-in-out 400ms 0ms;
            border-radius: 0px;
        }

        .supercarousel235373 .supercarousel>div:hover .super_overlay0 {
            background-color: rgba(0, 0, 0, 0);
        }

        .supercarousel235373 .supercarousel>div .superelement0 {
            transition: none ease-in-out 400ms 0ms;
            -wekbit-transition: none ease-in-out 400ms 0ms;
            -moz-transition: none ease-in-out 400ms 0ms;
            -ms-transition: none ease-in-out 400ms 0ms;
            -o-transition: none ease-in-out 400ms 0ms;
            object-position: center;
            border-left-width: 0px;
            border-left-style: solid;
            border-left-color: #444444;
            border-top-width: 0px;
            border-top-style: solid;
            border-top-color: #444444;
            border-right-width: 0px;
            border-right-style: solid;
            border-right-color: #444444;
            border-bottom-width: 0px;
            border-bottom-style: solid;
            border-bottom-color: #444444;
            background-color: rgba(0, 0, 0, 0);
            box-shadow: 0px 0px 0px #444444;
            opacity: 1.00;
            margin-top: 0px;
            margin-right: 0px;
            margin-bottom: 0px;
            margin-left: 0px;
            padding-top: 0px;
            padding-right: 0px;
            padding-bottom: 0px;
            padding-left: 0px;
            border-radius: 0px;
            transform: none;
            -wekbit-transform: none;
            -moz-transform: none;
            -ms-transform: none;
            -o-transform: none;
            filter: grayscale(0%);
        }

        .supercarousel235373 .supercarousel>div .superelement0:hover {
            border-width: 0px;
            border-style: solid;
            border-color: #444444;
            background-color: rgba(0, 0, 0, 0);
            box-shadow: 0px 0px 0px #444444;
            opacity: 1.00;
            transform: none;
            -wekbit-transform: none;
            -moz-transform: none;
            -ms-transform: none;
            -o-transform: none;
            filter: grayscale(0%);
        }

        .supercarousel235373 .supercarousel>div superelement0 a {
            transition: none ease-in-out 400ms 0ms;
            -wekbit-transition: none ease-in-out 400ms 0ms;
            -moz-transition: none ease-in-out 400ms 0ms;
            -ms-transition: none ease-in-out 400ms 0ms;
            -o-transition: none ease-in-out 400ms 0ms;
            object-position: center;
            border-left-width: 0px;
            border-left-style: solid;
            border-left-color: #444444;
            border-top-width: 0px;
            border-top-style: solid;
            border-top-color: #444444;
            border-right-width: 0px;
            border-right-style: solid;
            border-right-color: #444444;
            border-bottom-width: 0px;
            border-bottom-style: solid;
            border-bottom-color: #444444;
            background-color: rgba(0, 0, 0, 0);
            box-shadow: 0px 0px 0px #444444;
            opacity: 1.00;
            margin-top: 0px;
            margin-right: 0px;
            margin-bottom: 0px;
            margin-left: 0px;
            padding-top: 0px;
            padding-right: 0px;
            padding-bottom: 0px;
            padding-left: 0px;
            border-radius: 0px;
            transform: none;
            -wekbit-transform: none;
            -moz-transform: none;
            -ms-transform: none;
            -o-transform: none;
            filter: grayscale(0%);
        }

        .supercarousel235373 .supercarousel>div superelement0 a:hover {
            border-width: 0px;
            border-style: solid;
            border-color: #444444;
            background-color: rgba(0, 0, 0, 0);
            box-shadow: 0px 0px 0px #444444;
            opacity: 1.00;
            transform: none;
            -wekbit-transform: none;
            -moz-transform: none;
            -ms-transform: none;
            -o-transform: none;
            filter: grayscale(0%);
        }

        .supercarousel235352 .supercarousel>div {}

        .supercarousel235352 .supercarousel>div .super_overlay0 {
            background-color: rgba(0, 0, 0, 0);
            transition: none ease-in-out 400ms 0ms;
            -wekbit-transition: none ease-in-out 400ms 0ms;
            -moz-transition: none ease-in-out 400ms 0ms;
            -ms-transition: none ease-in-out 400ms 0ms;
            -o-transition: none ease-in-out 400ms 0ms;
            border-radius: 0px;
        }

        .supercarousel235352 .supercarousel>div:hover .super_overlay0 {
            background-color: rgba(0, 0, 0, 0);
        }

        .supercarousel235352 .supercarousel>div .superelement0 {
            transition: none ease-in-out 400ms 0ms;
            -wekbit-transition: none ease-in-out 400ms 0ms;
            -moz-transition: none ease-in-out 400ms 0ms;
            -ms-transition: none ease-in-out 400ms 0ms;
            -o-transition: none ease-in-out 400ms 0ms;
            object-position: center;
            border-left-width: 0px;
            border-left-style: solid;
            border-left-color: #444444;
            border-top-width: 0px;
            border-top-style: solid;
            border-top-color: #444444;
            border-right-width: 0px;
            border-right-style: solid;
            border-right-color: #444444;
            border-bottom-width: 0px;
            border-bottom-style: solid;
            border-bottom-color: #444444;
            background-color: rgba(0, 0, 0, 0);
            box-shadow: 0px 0px 0px #444444;
            opacity: 1.00;
            margin-top: 0px;
            margin-right: 0px;
            margin-bottom: 0px;
            margin-left: 0px;
            padding-top: 0px;
            padding-right: 0px;
            padding-bottom: 0px;
            padding-left: 0px;
            border-radius: 0px;
            transform: none;
            -wekbit-transform: none;
            -moz-transform: none;
            -ms-transform: none;
            -o-transform: none;
            filter: grayscale(0%);
        }

        .supercarousel235352 .supercarousel>div .superelement0:hover {
            border-width: 0px;
            border-style: solid;
            border-color: #444444;
            background-color: rgba(0, 0, 0, 0);
            box-shadow: 0px 0px 0px #444444;
            opacity: 1.00;
            transform: none;
            -wekbit-transform: none;
            -moz-transform: none;
            -ms-transform: none;
            -o-transform: none;
            filter: grayscale(0%);
        }

        .supercarousel235352 .supercarousel>div superelement0 a {
            transition: none ease-in-out 400ms 0ms;
            -wekbit-transition: none ease-in-out 400ms 0ms;
            -moz-transition: none ease-in-out 400ms 0ms;
            -ms-transition: none ease-in-out 400ms 0ms;
            -o-transition: none ease-in-out 400ms 0ms;
            object-position: center;
            border-left-width: 0px;
            border-left-style: solid;
            border-left-color: #444444;
            border-top-width: 0px;
            border-top-style: solid;
            border-top-color: #444444;
            border-right-width: 0px;
            border-right-style: solid;
            border-right-color: #444444;
            border-bottom-width: 0px;
            border-bottom-style: solid;
            border-bottom-color: #444444;
            background-color: rgba(0, 0, 0, 0);
            box-shadow: 0px 0px 0px #444444;
            opacity: 1.00;
            margin-top: 0px;
            margin-right: 0px;
            margin-bottom: 0px;
            margin-left: 0px;
            padding-top: 0px;
            padding-right: 0px;
            padding-bottom: 0px;
            padding-left: 0px;
            border-radius: 0px;
            transform: none;
            -wekbit-transform: none;
            -moz-transform: none;
            -ms-transform: none;
            -o-transform: none;
            filter: grayscale(0%);
        }

        .supercarousel235352 .supercarousel>div superelement0 a:hover {
            border-width: 0px;
            border-style: solid;
            border-color: #444444;
            background-color: rgba(0, 0, 0, 0);
            box-shadow: 0px 0px 0px #444444;
            opacity: 1.00;
            transform: none;
            -wekbit-transform: none;
            -moz-transform: none;
            -ms-transform: none;
            -o-transform: none;
            filter: grayscale(0%);
        }

        .supercarousel2734 .supercarousel>div {}

        .supercarousel2734 .supercarousel>div .super_overlay0 {
            background-color: rgba(0, 0, 0, 0);
            transition: none ease-in-out 400ms 0ms;
            -wekbit-transition: none ease-in-out 400ms 0ms;
            -moz-transition: none ease-in-out 400ms 0ms;
            -ms-transition: none ease-in-out 400ms 0ms;
            -o-transition: none ease-in-out 400ms 0ms;
            border-radius: 0px;
        }

        .supercarousel2734 .supercarousel>div:hover .super_overlay0 {
            background-color: rgba(0, 0, 0, 0);
        }

        .supercarousel2734 .supercarousel>div .superelement0 {
            transition: none ease-in-out 400ms 0ms;
            -wekbit-transition: none ease-in-out 400ms 0ms;
            -moz-transition: none ease-in-out 400ms 0ms;
            -ms-transition: none ease-in-out 400ms 0ms;
            -o-transition: none ease-in-out 400ms 0ms;
            object-position: center;
            border-left-width: 0px;
            border-left-style: solid;
            border-left-color: #444444;
            border-top-width: 0px;
            border-top-style: solid;
            border-top-color: #444444;
            border-right-width: 0px;
            border-right-style: solid;
            border-right-color: #444444;
            border-bottom-width: 0px;
            border-bottom-style: solid;
            border-bottom-color: #444444;
            background-color: rgba(0, 0, 0, 0);
            box-shadow: 0px 0px 0px #444444;
            opacity: 1.00;
            margin-top: 0px;
            margin-right: 0px;
            margin-bottom: 0px;
            margin-left: 0px;
            padding-top: 0px;
            padding-right: 0px;
            padding-bottom: 0px;
            padding-left: 0px;
            border-radius: 0px;
            transform: none;
            -wekbit-transform: none;
            -moz-transform: none;
            -ms-transform: none;
            -o-transform: none;
            filter: grayscale(0%);
        }

        .supercarousel2734 .supercarousel>div .superelement0:hover {
            border-width: 0px;
            border-style: solid;
            border-color: #444444;
            background-color: rgba(0, 0, 0, 0);
            box-shadow: 0px 0px 0px #444444;
            opacity: 1.00;
            transform: none;
            -wekbit-transform: none;
            -moz-transform: none;
            -ms-transform: none;
            -o-transform: none;
            filter: grayscale(0%);
        }

        .supercarousel2734 .supercarousel>div superelement0 a {
            transition: none ease-in-out 400ms 0ms;
            -wekbit-transition: none ease-in-out 400ms 0ms;
            -moz-transition: none ease-in-out 400ms 0ms;
            -ms-transition: none ease-in-out 400ms 0ms;
            -o-transition: none ease-in-out 400ms 0ms;
            object-position: center;
            border-left-width: 0px;
            border-left-style: solid;
            border-left-color: #444444;
            border-top-width: 0px;
            border-top-style: solid;
            border-top-color: #444444;
            border-right-width: 0px;
            border-right-style: solid;
            border-right-color: #444444;
            border-bottom-width: 0px;
            border-bottom-style: solid;
            border-bottom-color: #444444;
            background-color: rgba(0, 0, 0, 0);
            box-shadow: 0px 0px 0px #444444;
            opacity: 1.00;
            margin-top: 0px;
            margin-right: 0px;
            margin-bottom: 0px;
            margin-left: 0px;
            padding-top: 0px;
            padding-right: 0px;
            padding-bottom: 0px;
            padding-left: 0px;
            border-radius: 0px;
            transform: none;
            -wekbit-transform: none;
            -moz-transform: none;
            -ms-transform: none;
            -o-transform: none;
            filter: grayscale(0%);
        }

        .supercarousel2734 .supercarousel>div superelement0 a:hover {
            border-width: 0px;
            border-style: solid;
            border-color: #444444;
            background-color: rgba(0, 0, 0, 0);
            box-shadow: 0px 0px 0px #444444;
            opacity: 1.00;
            transform: none;
            -wekbit-transform: none;
            -moz-transform: none;
            -ms-transform: none;
            -o-transform: none;
            filter: grayscale(0%);
        }

        .supercarousel2265 .supercarousel>div {}

        .supercarousel2265 .supercarousel>div .super_overlay0 {
            background-color: rgba(0, 0, 0, 0);
            transition: none ease-in-out 400ms 0ms;
            -wekbit-transition: none ease-in-out 400ms 0ms;
            -moz-transition: none ease-in-out 400ms 0ms;
            -ms-transition: none ease-in-out 400ms 0ms;
            -o-transition: none ease-in-out 400ms 0ms;
            border-radius: 0px;
        }

        .supercarousel2265 .supercarousel>div:hover .super_overlay0 {
            background-color: rgba(0, 0, 0, 0);
        }

        .supercarousel2265 .supercarousel>div .superelement0 {
            transition: none ease-in-out 400ms 0ms;
            -wekbit-transition: none ease-in-out 400ms 0ms;
            -moz-transition: none ease-in-out 400ms 0ms;
            -ms-transition: none ease-in-out 400ms 0ms;
            -o-transition: none ease-in-out 400ms 0ms;
            object-position: center;
            border-left-width: 0px;
            border-left-style: solid;
            border-left-color: #444444;
            border-top-width: 0px;
            border-top-style: solid;
            border-top-color: #444444;
            border-right-width: 0px;
            border-right-style: solid;
            border-right-color: #444444;
            border-bottom-width: 0px;
            border-bottom-style: solid;
            border-bottom-color: #444444;
            background-color: rgba(0, 0, 0, 0);
            box-shadow: 0px 0px 0px #444444;
            opacity: 1.00;
            margin-top: 0px;
            margin-right: 0px;
            margin-bottom: 0px;
            margin-left: 0px;
            padding-top: 0px;
            padding-right: 0px;
            padding-bottom: 0px;
            padding-left: 0px;
            border-radius: 0px;
            transform: none;
            -wekbit-transform: none;
            -moz-transform: none;
            -ms-transform: none;
            -o-transform: none;
            filter: grayscale(0%);
        }

        .supercarousel2265 .supercarousel>div .superelement0:hover {
            border-width: 0px;
            border-style: solid;
            border-color: #444444;
            background-color: rgba(0, 0, 0, 0);
            box-shadow: 0px 0px 0px #444444;
            opacity: 1.00;
            transform: none;
            -wekbit-transform: none;
            -moz-transform: none;
            -ms-transform: none;
            -o-transform: none;
            filter: grayscale(0%);
        }

        .supercarousel2265 .supercarousel>div superelement0 a {
            transition: none ease-in-out 400ms 0ms;
            -wekbit-transition: none ease-in-out 400ms 0ms;
            -moz-transition: none ease-in-out 400ms 0ms;
            -ms-transition: none ease-in-out 400ms 0ms;
            -o-transition: none ease-in-out 400ms 0ms;
            object-position: center;
            border-left-width: 0px;
            border-left-style: solid;
            border-left-color: #444444;
            border-top-width: 0px;
            border-top-style: solid;
            border-top-color: #444444;
            border-right-width: 0px;
            border-right-style: solid;
            border-right-color: #444444;
            border-bottom-width: 0px;
            border-bottom-style: solid;
            border-bottom-color: #444444;
            background-color: rgba(0, 0, 0, 0);
            box-shadow: 0px 0px 0px #444444;
            opacity: 1.00;
            margin-top: 0px;
            margin-right: 0px;
            margin-bottom: 0px;
            margin-left: 0px;
            padding-top: 0px;
            padding-right: 0px;
            padding-bottom: 0px;
            padding-left: 0px;
            border-radius: 0px;
            transform: none;
            -wekbit-transform: none;
            -moz-transform: none;
            -ms-transform: none;
            -o-transform: none;
            filter: grayscale(0%);
        }

        .supercarousel2265 .supercarousel>div superelement0 a:hover {
            border-width: 0px;
            border-style: solid;
            border-color: #444444;
            background-color: rgba(0, 0, 0, 0);
            box-shadow: 0px 0px 0px #444444;
            opacity: 1.00;
            transform: none;
            -wekbit-transform: none;
            -moz-transform: none;
            -ms-transform: none;
            -o-transform: none;
            filter: grayscale(0%);
        }
    </style>
    <link rel='stylesheet' id='tpro-slick-css'
        href='https://www.eimaths-th.com/wp-content/plugins/testimonial-pro/public/assets/css/slick.min5bf8.css?ver=2.2.5'
        type='text/css' media='all' />
    <link rel='stylesheet' id='tpro-font-awesome-css'
        href='https://www.eimaths-th.com/wp-content/plugins/testimonial-pro/public/assets/css/font-awesome.min5bf8.css?ver=2.2.5'
        type='text/css' media='all' />
    <link rel='stylesheet' id='tpro-magnific-popup-css'
        href='https://www.eimaths-th.com/wp-content/plugins/testimonial-pro/public/assets/css/magnific-popup.min5bf8.css?ver=2.2.5'
        type='text/css' media='all' />
    <link rel='stylesheet' id='tpro-style-css'
        href='https://www.eimaths-th.com/wp-content/plugins/testimonial-pro/public/assets/css/style.min5bf8.css?ver=2.2.5'
        type='text/css' media='all' />
    <link rel='stylesheet' id='tpro-custom-css'
        href='https://www.eimaths-th.com/wp-content/cache/asset-cleanup/css/item/tpro-custom-vdcf4065f69463e207e9594837ca775468606b817.css'
        type='text/css' media='all' />
    <style id='tpro-custom-inline-css' type='text/css'>
        .et-pb-contact-message p {
            margin-top: 0 !important;
        }
    </style>
    <link rel='stylesheet' id='tpro-responsive-css'
        href='https://www.eimaths-th.com/wp-content/plugins/testimonial-pro/public/assets/css/responsive.min5bf8.css?ver=2.2.5'
        type='text/css' media='all' />
    <link rel='stylesheet' id='wps_bootstrap-css'
        href='../../maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min52c7.css?ver=6.0.5' type='text/css'
        media='all' />
    <link rel='stylesheet' id='wps_fontawesome-css'
        href='../../cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min52c7.css?ver=6.0.5'
        type='text/css' media='all' />
    <link rel='stylesheet' id='store-styles-css'
        href='https://www.eimaths-th.com/wp-content/plugins/wc-pickup-store/assets/css/stores.min52c7.css?ver=6.0.5'
        type='text/css' media='all' />
    <link rel='stylesheet' id='woocommerce-layout-css'
        href='https://www.eimaths-th.com/wp-content/plugins/woocommerce/assets/css/woocommerce-layoutcb57.css?ver=7.2.2'
        type='text/css' media='all' />
    <link rel='stylesheet' id='woocommerce-smallscreen-css'
        href='https://www.eimaths-th.com/wp-content/plugins/woocommerce/assets/css/woocommerce-smallscreencb57.css?ver=7.2.2'
        type='text/css' media='only screen and (max-width: 768px)' />
    <link rel='stylesheet' id='woocommerce-general-css'
        href='https://www.eimaths-th.com/wp-content/plugins/woocommerce/assets/css/woocommercecb57.css?ver=7.2.2'
        type='text/css' media='all' />
    <style id='woocommerce-inline-inline-css' type='text/css'>
        .woocommerce form .form-row .required {
            visibility: visible;
        }
    </style>
    <link rel='stylesheet' id='dashicons-css'
        href='https://www.eimaths-th.com/wp-includes/css/dashicons.min52c7.css?ver=6.0.5' type='text/css'
        media='all' />
    <link rel='stylesheet' id='wpmi-icons-css'
        href='https://www.eimaths-th.com/wp-content/cache/asset-cleanup/css/item/wpmi-icons-v6f571eeb910023bef474ee84c5ca47f486faa7f9.css'
        type='text/css' media='all' />
    <link rel='stylesheet' id='hfe-style-css'
        href='https://www.eimaths-th.com/wp-content/cache/asset-cleanup/css/item/hfe-style-vd3075c15c443ea92321c86034087c5b0734256f0.css'
        type='text/css' media='all' />
    <link rel='stylesheet' id='elementor-icons-css'
        href='https://www.eimaths-th.com/wp-content/plugins/elementor/assets/lib/eicons/css/elementor-icons.min91ce.css?ver=5.16.0'
        type='text/css' media='all' />
    <link rel='stylesheet' id='elementor-frontend-legacy-css'
        href='https://www.eimaths-th.com/wp-content/plugins/elementor/assets/css/frontend-legacy.min2e46.css?ver=3.9.2'
        type='text/css' media='all' />
    <link rel='stylesheet' id='elementor-frontend-css'
        href='https://www.eimaths-th.com/wp-content/plugins/elementor/assets/css/frontend.min2e46.css?ver=3.9.2'
        type='text/css' media='all' />
    <style id='elementor-frontend-inline-css' type='text/css'>
        .elementor-kit-977 {
            --e-global-color-primary: #6EC1E4;
            --e-global-color-secondary: #54595F;
            --e-global-color-text: #7A7A7A;
            --e-global-color-accent: #61CE70;
            --e-global-typography-primary-font-family: "Roboto";
            --e-global-typography-primary-font-weight: 600;
            --e-global-typography-secondary-font-family: "Roboto Slab";
            --e-global-typography-secondary-font-weight: 400;
            --e-global-typography-text-font-family: "Roboto";
            --e-global-typography-text-font-weight: 400;
            --e-global-typography-accent-font-family: "Roboto";
            --e-global-typography-accent-font-weight: 500;
        }

        .elementor-section.elementor-section-boxed>.elementor-container {
            max-width: 1140px;
        }

        .e-con {
            --container-max-width: 1140px;
        }

        .elementor-widget:not(:last-child) {
            margin-bottom: 20px;
        }

        .elementor-element {
            --widgets-spacing: 20px;
        }

            {}

        h1.entry-title {
            display: var(--page-title-display);
        }

        @media(max-width:1024px) {
            .elementor-section.elementor-section-boxed>.elementor-container {
                max-width: 1024px;
            }

            .e-con {
                --container-max-width: 1024px;
            }
        }

        @media(max-width:767px) {
            .elementor-section.elementor-section-boxed>.elementor-container {
                max-width: 767px;
            }

            .e-con {
                --container-max-width: 767px;
            }
        }

        .elementor-widget-heading .elementor-heading-title {
            color: var(--e-global-color-primary);
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-image .widget-image-caption {
            color: var(--e-global-color-text);
            font-family: var(--e-global-typography-text-font-family), Sans-serif;
            font-weight: var(--e-global-typography-text-font-weight);
        }

        .elementor-widget-text-editor {
            color: var(--e-global-color-text);
            font-family: var(--e-global-typography-text-font-family), Sans-serif;
            font-weight: var(--e-global-typography-text-font-weight);
        }

        .elementor-widget-text-editor.elementor-drop-cap-view-stacked .elementor-drop-cap {
            background-color: var(--e-global-color-primary);
        }

        .elementor-widget-text-editor.elementor-drop-cap-view-framed .elementor-drop-cap,
        .elementor-widget-text-editor.elementor-drop-cap-view-default .elementor-drop-cap {
            color: var(--e-global-color-primary);
            border-color: var(--e-global-color-primary);
        }

        .elementor-widget-button .elementor-button {
            font-family: var(--e-global-typography-accent-font-family), Sans-serif;
            font-weight: var(--e-global-typography-accent-font-weight);
            background-color: var(--e-global-color-accent);
        }

        .elementor-widget-divider {
            --divider-color: var(--e-global-color-secondary);
        }

        .elementor-widget-divider .elementor-divider__text {
            color: var(--e-global-color-secondary);
            font-family: var(--e-global-typography-secondary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-secondary-font-weight);
        }

        .elementor-widget-divider.elementor-view-stacked .elementor-icon {
            background-color: var(--e-global-color-secondary);
        }

        .elementor-widget-divider.elementor-view-framed .elementor-icon,
        .elementor-widget-divider.elementor-view-default .elementor-icon {
            color: var(--e-global-color-secondary);
            border-color: var(--e-global-color-secondary);
        }

        .elementor-widget-divider.elementor-view-framed .elementor-icon,
        .elementor-widget-divider.elementor-view-default .elementor-icon svg {
            fill: var(--e-global-color-secondary);
        }

        .elementor-widget-image-box .elementor-image-box-title {
            color: var(--e-global-color-primary);
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-image-box .elementor-image-box-description {
            color: var(--e-global-color-text);
            font-family: var(--e-global-typography-text-font-family), Sans-serif;
            font-weight: var(--e-global-typography-text-font-weight);
        }

        .elementor-widget-icon.elementor-view-stacked .elementor-icon {
            background-color: var(--e-global-color-primary);
        }

        .elementor-widget-icon.elementor-view-framed .elementor-icon,
        .elementor-widget-icon.elementor-view-default .elementor-icon {
            color: var(--e-global-color-primary);
            border-color: var(--e-global-color-primary);
        }

        .elementor-widget-icon.elementor-view-framed .elementor-icon,
        .elementor-widget-icon.elementor-view-default .elementor-icon svg {
            fill: var(--e-global-color-primary);
        }

        .elementor-widget-icon-box.elementor-view-stacked .elementor-icon {
            background-color: var(--e-global-color-primary);
        }

        .elementor-widget-icon-box.elementor-view-framed .elementor-icon,
        .elementor-widget-icon-box.elementor-view-default .elementor-icon {
            fill: var(--e-global-color-primary);
            color: var(--e-global-color-primary);
            border-color: var(--e-global-color-primary);
        }

        .elementor-widget-icon-box .elementor-icon-box-title {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-icon-box .elementor-icon-box-title,
        .elementor-widget-icon-box .elementor-icon-box-title a {
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-icon-box .elementor-icon-box-description {
            color: var(--e-global-color-text);
            font-family: var(--e-global-typography-text-font-family), Sans-serif;
            font-weight: var(--e-global-typography-text-font-weight);
        }

        .elementor-widget-star-rating .elementor-star-rating__title {
            color: var(--e-global-color-text);
            font-family: var(--e-global-typography-text-font-family), Sans-serif;
            font-weight: var(--e-global-typography-text-font-weight);
        }

        .elementor-widget-image-gallery .gallery-item .gallery-caption {
            font-family: var(--e-global-typography-accent-font-family), Sans-serif;
            font-weight: var(--e-global-typography-accent-font-weight);
        }

        .elementor-widget-icon-list .elementor-icon-list-item:not(:last-child):after {
            border-color: var(--e-global-color-text);
        }

        .elementor-widget-icon-list .elementor-icon-list-icon i {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-icon-list .elementor-icon-list-icon svg {
            fill: var(--e-global-color-primary);
        }

        .elementor-widget-icon-list .elementor-icon-list-text {
            color: var(--e-global-color-secondary);
        }

        .elementor-widget-icon-list .elementor-icon-list-item>.elementor-icon-list-text,
        .elementor-widget-icon-list .elementor-icon-list-item>a {
            font-family: var(--e-global-typography-text-font-family), Sans-serif;
            font-weight: var(--e-global-typography-text-font-weight);
        }

        .elementor-widget-counter .elementor-counter-number-wrapper {
            color: var(--e-global-color-primary);
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-counter .elementor-counter-title {
            color: var(--e-global-color-secondary);
            font-family: var(--e-global-typography-secondary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-secondary-font-weight);
        }

        .elementor-widget-progress .elementor-progress-wrapper .elementor-progress-bar {
            background-color: var(--e-global-color-primary);
        }

        .elementor-widget-progress .elementor-title {
            color: var(--e-global-color-primary);
            font-family: var(--e-global-typography-text-font-family), Sans-serif;
            font-weight: var(--e-global-typography-text-font-weight);
        }

        .elementor-widget-testimonial .elementor-testimonial-content {
            color: var(--e-global-color-text);
            font-family: var(--e-global-typography-text-font-family), Sans-serif;
            font-weight: var(--e-global-typography-text-font-weight);
        }

        .elementor-widget-testimonial .elementor-testimonial-name {
            color: var(--e-global-color-primary);
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-testimonial .elementor-testimonial-job {
            color: var(--e-global-color-secondary);
            font-family: var(--e-global-typography-secondary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-secondary-font-weight);
        }

        .elementor-widget-tabs .elementor-tab-title,
        .elementor-widget-tabs .elementor-tab-title a {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-tabs .elementor-tab-title.elementor-active,
        .elementor-widget-tabs .elementor-tab-title.elementor-active a {
            color: var(--e-global-color-accent);
        }

        .elementor-widget-tabs .elementor-tab-title {
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-tabs .elementor-tab-content {
            color: var(--e-global-color-text);
            font-family: var(--e-global-typography-text-font-family), Sans-serif;
            font-weight: var(--e-global-typography-text-font-weight);
        }

        .elementor-widget-accordion .elementor-accordion-icon,
        .elementor-widget-accordion .elementor-accordion-title {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-accordion .elementor-accordion-icon svg {
            fill: var(--e-global-color-primary);
        }

        .elementor-widget-accordion .elementor-active .elementor-accordion-icon,
        .elementor-widget-accordion .elementor-active .elementor-accordion-title {
            color: var(--e-global-color-accent);
        }

        .elementor-widget-accordion .elementor-active .elementor-accordion-icon svg {
            fill: var(--e-global-color-accent);
        }

        .elementor-widget-accordion .elementor-accordion-title {
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-accordion .elementor-tab-content {
            color: var(--e-global-color-text);
            font-family: var(--e-global-typography-text-font-family), Sans-serif;
            font-weight: var(--e-global-typography-text-font-weight);
        }

        .elementor-widget-toggle .elementor-toggle-title,
        .elementor-widget-toggle .elementor-toggle-icon {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-toggle .elementor-toggle-icon svg {
            fill: var(--e-global-color-primary);
        }

        .elementor-widget-toggle .elementor-tab-title.elementor-active a,
        .elementor-widget-toggle .elementor-tab-title.elementor-active .elementor-toggle-icon {
            color: var(--e-global-color-accent);
        }

        .elementor-widget-toggle .elementor-toggle-title {
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-toggle .elementor-tab-content {
            color: var(--e-global-color-text);
            font-family: var(--e-global-typography-text-font-family), Sans-serif;
            font-weight: var(--e-global-typography-text-font-weight);
        }

        .elementor-widget-alert .elementor-alert-title {
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-alert .elementor-alert-description {
            font-family: var(--e-global-typography-text-font-family), Sans-serif;
            font-weight: var(--e-global-typography-text-font-weight);
        }

        .elementor-widget-eae-timeline .eae-tl-item-title {
            color: var(--e-global-color-primary);
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-eae-timeline .eae-tl-content {
            color: var(--e-global-color-text);
        }

        .elementor-widget-eae-timeline .eae-tl-item-content {
            font-family: var(--e-global-typography-text-font-family), Sans-serif;
            font-weight: var(--e-global-typography-text-font-weight);
        }

        .elementor-widget-eae-timeline .eae-tl-read-more a {
            color: var(--e-global-color-accent);
            font-family: var(--e-global-typography-accent-font-family), Sans-serif;
            font-weight: var(--e-global-typography-accent-font-weight);
        }

        .elementor-widget-eae-timeline .eae-tl-item-meta {
            color: var(--e-global-color-text);
        }

        .elementor-widget-eae-timeline .eae-tl-item-meta-inner {
            color: var(--e-global-color-text);
        }

        .elementor-widget-eae-timeline .eae-tl-item-meta,
        .elementor-widget-eae-timeline .eae-tl-item-meta-inner {
            font-family: var(--e-global-typography-text-font-family), Sans-serif;
            font-weight: var(--e-global-typography-text-font-weight);
        }

        .elementor-widget-eae-timeline .eae-timeline-item:hover .eae-tl-item-meta {
            color: var(--e-global-color-text);
        }

        .elementor-widget-eae-timeline .eae-timeline-item:hover .eae-tl-item-meta-inner {
            color: var(--e-global-color-text);
        }

        .elementor-widget-eae-timeline .eae-timeline-item:hover .eae-tl-item-meta,
        .elementor-widget-eae-timeline .eae-timeline-item:hover .eae-tl-item-meta-inner {
            font-family: var(--e-global-typography-text-font-family), Sans-serif;
            font-weight: var(--e-global-typography-text-font-weight);
        }

        .elementor-widget-eae-timeline .eae-tl-item-focused .eae-tl-item-meta {
            color: var(--e-global-color-text);
        }

        .elementor-widget-eae-timeline .eae-tl-item-focused .eae-tl-item-meta-inner {
            color: var(--e-global-color-text);
        }

        .elementor-widget-eae-timeline .eae-tl-item-focused .eae-tl-item-meta,
        .elementor-widget-eae-timeline .eae-tl-item-focused .eae-tl-item-meta-inner {
            font-family: var(--e-global-typography-text-font-family), Sans-serif;
            font-weight: var(--e-global-typography-text-font-weight);
        }

        .elementor-widget-eae-timeline .eae-timline-progress-bar {
            background: var(--e-global-color-primary);
        }

        .elementor-widget-eae-timeline .eae-timline-progress-bar .eae-pb-inner-line {
            background: var(--e-global-color-accent);
        }

        .elementor-widget-eae-timeline .eae-icon-item_icon.eae-icon-view-stacked {
            background-color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-timeline .eae-icon-item_icon.eae-icon-view-framed {
            border-color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-timeline .eae-icon-item_icon.eae-icon-view-framed i {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-timeline .eae-icon-item_icon.eae-icon-view-framed svg {
            fill: var(--e-global-color-primary);
        }

        .elementor-widget-eae-timeline .eae-icon-item_icon.eae-icon-view-default i {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-timeline .eae-icon-item_icon.eae-icon-view-default svg {
            fill: var(--e-global-color-primary);
        }

        .elementor-widget-eae-timeline .eae-tl-item-focused .eae-icon-item_icon.eae-icon-view-stacked {
            background-color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-timeline .eae-tl-item-focused .eae-icon-item_icon.eae-icon-view-framed {
            border-color: var(--e-global-color-primary);
            background-color: var(--e-global-color-accent);
        }

        .elementor-widget-eae-timeline .eae-tl-item-focused .eae-icon-item_icon.eae-icon-view-framed i {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-timeline .eae-tl-item-focused .eae-icon-item_icon.eae-icon-view-framed svg {
            fill: var(--e-global-color-primary);
        }

        .elementor-widget-eae-timeline .eae-tl-item-focused .eae-icon-item_icon.eae-icon-view-default i {
            color: var(--e-global-color-primary);
            border-color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-timeline .eae-tl-item-focused .eae-icon-item_icon.eae-icon-view-default svg {
            fill: var(--e-global-color-primary);
            border-color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-timeline .eae-tl-item-focused .eae-icon-item_icon.eae-icon-view-stacked i {
            color: var(--e-global-color-accent);
        }

        .elementor-widget-eae-timeline .eae-tl-item-focused .eae-icon-item_icon.eae-icon-view-stacked svg {
            fill: var(--e-global-color-accent);
        }

        .elementor-widget-eae-info-circle .eae-ic-heading {
            color: var(--e-global-color-primary);
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-eae-info-circle .eae-ic-description {
            color: var(--e-global-color-text);
            font-family: var(--e-global-typography-text-font-family), Sans-serif;
            font-weight: var(--e-global-typography-text-font-weight);
        }

        .elementor-widget-eae-info-circle .eae-icon-item_icon.eae-icon-view-stacked {
            background-color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-info-circle .eae-icon-item_icon.eae-icon-view-framed {
            border-color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-info-circle .eae-icon-item_icon.eae-icon-view-framed i {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-info-circle .eae-icon-item_icon.eae-icon-view-framed svg {
            fill: var(--e-global-color-primary);
        }

        .elementor-widget-eae-info-circle .eae-icon-item_icon.eae-icon-view-default i {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-info-circle .eae-icon-item_icon.eae-icon-view-default svg {
            fill: var(--e-global-color-primary);
        }

        .elementor-widget-eae-info-circle .eae-active .eae-icon-item_icon.eae-icon-view-stacked {
            background-color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-info-circle .eae-active .eae-icon-item_icon.eae-icon-view-framed {
            border-color: var(--e-global-color-primary);
            background-color: var(--e-global-color-accent);
        }

        .elementor-widget-eae-info-circle .eae-active .eae-icon-item_icon.eae-icon-view-framed i {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-info-circle .eae-active .eae-icon-item_icon.eae-icon-view-framed svg {
            fill: var(--e-global-color-primary);
        }

        .elementor-widget-eae-info-circle .eae-active .eae-icon-item_icon.eae-icon-view-default i {
            color: var(--e-global-color-primary);
            border-color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-info-circle .eae-active .eae-icon-item_icon.eae-icon-view-default svg {
            fill: var(--e-global-color-primary);
            border-color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-info-circle .eae-active .eae-icon-item_icon.eae-icon-view-stacked i {
            color: var(--e-global-color-accent);
        }

        .elementor-widget-eae-info-circle .eae-active .eae-icon-item_icon.eae-icon-view-stacked svg {
            fill: var(--e-global-color-accent);
        }

        .elementor-widget-eae-comparisontable .eae-table-1.eae-ct-heading {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-table-1.eae-ct-heading.active {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-ct-plan.eae-table-1 .eae-ct-price-wrapper .eae-ct-original-price {
            color: var(--e-global-color-primary);
            text-decoration-color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-table-2.eae-ct-heading {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-table-2.eae-ct-heading.active {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-ct-plan.eae-table-2 .eae-ct-price-wrapper .eae-ct-original-price {
            color: var(--e-global-color-primary);
            text-decoration-color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-table-3.eae-ct-heading {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-table-3.eae-ct-heading.active {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-ct-plan.eae-table-3 .eae-ct-price-wrapper .eae-ct-original-price {
            color: var(--e-global-color-primary);
            text-decoration-color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-table-4.eae-ct-heading {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-table-4.eae-ct-heading.active {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-ct-plan.eae-table-4 .eae-ct-price-wrapper .eae-ct-original-price {
            color: var(--e-global-color-primary);
            text-decoration-color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-table-5.eae-ct-heading {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-table-5.eae-ct-heading.active {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-ct-plan.eae-table-5 .eae-ct-price-wrapper .eae-ct-original-price {
            color: var(--e-global-color-primary);
            text-decoration-color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-table-6.eae-ct-heading {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-table-6.eae-ct-heading.active {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-ct-plan.eae-table-6 .eae-ct-price-wrapper .eae-ct-original-price {
            color: var(--e-global-color-primary);
            text-decoration-color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-table-7.eae-ct-heading {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-table-7.eae-ct-heading.active {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-ct-plan.eae-table-7 .eae-ct-price-wrapper .eae-ct-original-price {
            color: var(--e-global-color-primary);
            text-decoration-color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-table-8.eae-ct-heading {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-table-8.eae-ct-heading.active {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-ct-plan.eae-table-8 .eae-ct-price-wrapper .eae-ct-original-price {
            color: var(--e-global-color-primary);
            text-decoration-color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-table-9.eae-ct-heading {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-table-9.eae-ct-heading.active {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-ct-plan.eae-table-9 .eae-ct-price-wrapper .eae-ct-original-price {
            color: var(--e-global-color-primary);
            text-decoration-color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-table-10.eae-ct-heading {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-table-10.eae-ct-heading.active {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-ct-plan.eae-table-10 .eae-ct-price-wrapper .eae-ct-original-price {
            color: var(--e-global-color-primary);
            text-decoration-color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-ct-feature {
            font-family: var(--e-global-typography-text-font-family), Sans-serif;
            font-weight: var(--e-global-typography-text-font-weight);
        }

        .elementor-widget-eae-comparisontable .eae-ct-header .eae-fbox-heading {
            background-color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable tbody tr .eae-ct-hide.eae-fbox-heading {
            background-color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-ct-header .eae-fbox-heading,
        .elementor-widget-eae-comparisontable tbody tr .eae-ct-hide.eae-fbox-heading {
            font-family: var(--e-global-typography-secondary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-secondary-font-weight);
        }

        .elementor-widget-eae-comparisontable .eae-ct-feature .eae-icon {
            background-color: var(--e-global-color-secondary);
        }

        .elementor-widget-eae-comparisontable .eae-ct-feature .tooltip:hover .eae-icon {
            background-color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-ct-wrapper .tooltip .tooltiptext {
            background-color: var(--e-global-color-primary);
            font-family: var(--e-global-typography-secondary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-secondary-font-weight);
        }

        .elementor-widget-eae-comparisontable .eae-ct-wrapper .tooltip .tooltiptext::before {
            border-top-color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-ct-heading {
            color: var(--e-global-color-primary);
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-eae-comparisontable .eae-ct-txt {
            font-family: var(--e-global-typography-text-font-family), Sans-serif;
            font-weight: var(--e-global-typography-text-font-weight);
        }

        .elementor-widget-eae-comparisontable .eae-ct-wrapper .eae-ct-button-heading {
            background-color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable .eae-ct-wrapper .eae-button-heading {
            background-color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-comparisontable {
            font-family: var(--e-global-typography-secondary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-secondary-font-weight);
        }

        .elementor-widget-wts-ab-image .eae-slider-icon {
            color: var(--e-global-color-secondary);
        }

        .elementor-widget-wts-ab-image .eae-img-comp-slider svg {
            fill: var(--e-global-color-secondary);
        }

        .elementor-widget-wts-ab-image .eae-text-after,
        .elementor-widget-wts-ab-image .eae-text-before {
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-wts-ab-image .eae-text-after {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-wts-ab-image .eae-text-before {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-wts-AnimatedText .eae-at-pre-text {
            color: var(--e-global-color-primary);
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-wts-AnimatedText .eae-at-animation-text,
        .elementor-widget-wts-AnimatedText .eae-at-animation-text i {
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-wts-AnimatedText .eae-at-animation-text {
            color: var(--e-global-color-accent);
        }

        .elementor-widget-wts-AnimatedText .eae-at-animation-text-wrapper::after {
            background-color: var(--e-global-color-primary);
        }

        .elementor-widget-wts-AnimatedText .eae-at-post-text {
            color: var(--e-global-color-primary);
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-eae-dual-button .eae-button-1-wrapper {
            font-family: var(--e-global-typography-accent-font-family), Sans-serif;
            font-weight: var(--e-global-typography-accent-font-weight);
        }

        .elementor-widget-eae-dual-button .eae-button-2-wrapper {
            font-family: var(--e-global-typography-accent-font-family), Sans-serif;
            font-weight: var(--e-global-typography-accent-font-weight);
        }

        .elementor-widget-eae-dual-button .eae-button-separator-wrapper .eae-button-separator {
            font-family: var(--e-global-typography-text-font-family), Sans-serif;
            font-weight: var(--e-global-typography-text-font-weight);
            color: var(--e-global-color-secondary);
        }

        .elementor-widget-eae-dual-button .eae-button-separator-wrapper .eae-button-separator svg {
            fill: var(--e-global-color-secondary);
        }

        .eae-wrap .mfp-title {
            color: var(--e-global-color-primary);
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .eae-wrap .eae-modal-content {
            color: var(--e-global-color-text);
            font-family: var(--e-global-typography-text-font-family), Sans-serif;
            font-weight: var(--e-global-typography-text-font-weight);
        }

        .elementor-widget-wts-modal-popup .eae-popup-link {
            font-family: var(--e-global-typography-accent-font-family), Sans-serif;
            font-weight: var(--e-global-typography-accent-font-weight);
            background-color: var(--e-global-color-accent);
        }

        .elementor-widget-eae-progress-bar .eae-pb-bar-skill {
            color: var(--e-global-color-secondary);
            font-family: var(--e-global-typography-secondary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-secondary-font-weight);
        }

        .elementor-widget-eae-progress-bar .eae-pb-bar-value {
            color: var(--e-global-color-secondary);
            font-family: var(--e-global-typography-secondary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-secondary-font-weight);
        }

        .elementor-widget-wts-flipbox .front-icon-title {
            color: var(--e-global-color-primary);
            font-family: var(--e-global-typography-accent-font-family), Sans-serif;
            font-weight: var(--e-global-typography-accent-font-weight);
        }

        .elementor-widget-wts-flipbox .eae-flip-box-front p {
            color: var(--e-global-color-primary);
            font-family: var(--e-global-typography-accent-font-family), Sans-serif;
            font-weight: var(--e-global-typography-accent-font-weight);
        }

        .elementor-widget-wts-flipbox .eae-flip-box-front .icon-wrapper i {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-wts-flipbox .eae-flip-box-front .icon-wrapper svg {
            fill: var(--e-global-color-primary);
        }

        .elementor-widget-wts-flipbox .eae-fb-icon-view-stacked {
            background-color: var(--e-global-color-primary);
        }

        .elementor-widget-wts-flipbox .back-icon-title {
            color: var(--e-global-color-primary);
            font-family: var(--e-global-typography-accent-font-family), Sans-serif;
            font-weight: var(--e-global-typography-accent-font-weight);
        }

        .elementor-widget-wts-flipbox .eae-flip-box-back p {
            color: var(--e-global-color-primary);
            font-family: var(--e-global-typography-accent-font-family), Sans-serif;
            font-weight: var(--e-global-typography-accent-font-weight);
        }

        .elementor-widget-wts-flipbox .eae-flip-box-back .icon-wrapper i {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-wts-flipbox .eae-flip-box-back .icon-wrapper svg {
            fill: var(--e-global-color-primary);
        }

        .elementor-widget-wts-flipbox .eae-flip-box-back .eae-fb-icon-view-stacked {
            background-color: var(--e-global-color-primary);
        }

        .elementor-widget-wts-flipbox .eae-fb-button {
            font-family: var(--e-global-typography-accent-font-family), Sans-serif;
            font-weight: var(--e-global-typography-accent-font-weight);
            background-color: var(--e-global-color-accent);
        }

        .elementor-widget-wts-splittext .eae-st-split-text {
            color: var(--e-global-color-primary);
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-wts-splittext .eae-st-rest-text {
            color: var(--e-global-color-secondary);
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-wts-textseparator .eae-separator-title {
            color: var(--e-global-color-primary);
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-wts-textseparator .eae-sep-lines {
            border-top-color: var(--e-global-color-primary);
        }

        .elementor-widget-wts-textseparator.eae-icon-view-default i {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-wts-textseparator.eae-icon-view-default svg {
            fill: var(--e-global-color-primary);
        }

        .elementor-widget-wts-textseparator.eae-icon-view-stacked .eae-separator-icon-inner {
            background-color: var(--e-global-color-primary);
        }

        .elementor-widget-wts-textseparator.eae-icon-view-framed .eae-separator-icon-inner,
        .elementor-widget-wts-textseparator.eae-icon-view-framed .eae-separator-icon-inner i {
            color: var(--e-global-color-primary);
            border-color: var(--e-global-color-primary);
        }

        .elementor-widget-wts-textseparator.eae-icon-view-framed .eae-separator-icon-inner svg {
            fill: var(--e-global-color-primary);
        }

        .elementor-widget-wts-pricetable .eae-pt-heading {
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-wts-pricetable .eae-pt-sub-heading {
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-wts-pricetable .eae-pt-feature-list li {
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-wts-pricetable .eae-pt-action-button {
            font-family: var(--e-global-typography-accent-font-family), Sans-serif;
            font-weight: var(--e-global-typography-accent-font-weight);
        }

        .elementor-widget-wts-postlist .eae-pl-title a {
            color: var(--e-global-color-primary);
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-wts-postlist .eae-pl-content-box {
            color: var(--e-global-color-text);
            font-family: var(--e-global-typography-text-font-family), Sans-serif;
            font-weight: var(--e-global-typography-text-font-weight);
        }

        .elementor-widget-wts-postlist .eae-pl-readmore a {
            color: var(--e-global-color-text);
            font-family: var(--e-global-typography-text-font-family), Sans-serif;
            font-weight: var(--e-global-typography-text-font-weight);
            background-color: var(--e-global-color-primary);
        }

        .elementor-widget-wts-shape-separator svg {
            fill: var(--e-global-color-primary);
        }

        .elementor-widget-eae-filterableGallery .eae-overlay-caption {
            font-family: var(--e-global-typography-secondary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-secondary-font-weight);
            color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-filterableGallery.eae-icon-view-stacked .eae-overlay-icon {
            background-color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-filterableGallery.eae-icon-view-framed .eae-overlay-icon,
        .elementor-widget-eae-filterableGallery.eae-icon-view-default .eae-overlay-icon {
            color: var(--e-global-color-primary);
            border-color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-filterableGallery.eae-icon-view-framed .eae-overlay-icon svg,
        .elementor-widget-eae-filterableGallery.eae-icon-view-default .eae-overlay-icon svg {
            fill: var(--e-global-color-primary);
        }

        .elementor-widget-eae-filterableGallery .eae-filter-label {
            font-family: var(--e-global-typography-accent-font-family), Sans-serif;
            font-weight: var(--e-global-typography-accent-font-weight);
            background-color: var(--e-global-color-accent);
        }

        .elementor-widget-eae-filterableGallery .eae-filter-label.current {
            background-color: var(--e-global-color-primary);
        }

        .elementor-widget-eae-content-switcher .eae-cs-label-wrapper .eae-content-switch-button .eae-content-switch-label,
        .elementor-widget-eae-content-switcher .eae-content-switcher-wrapper .eae-cs-switch-wrapper .eae-content-switch-label .eae-cs-label {
            font-family: var(--e-global-typography-accent-font-family), Sans-serif;
            font-weight: var(--e-global-typography-accent-font-weight);
        }

        .elementor-widget-eae-content-switcher .eae-content-switcher-wrapper .eae-cs-switch-wrapper .eae-content-toggle-switcher:before {
            background-color: var(--e-global-color-accent);
            border-color: var(--e-global-color-accent);
        }

        .elementor-widget-eae-content-switcher .eae-content-switcher-wrapper .eae-cs-switch-wrapper .eae-content-toggle-switcher {
            background-color: var(--e-global-color-secondary);
            border-color: var(--e-global-color-secondary);
        }

        .elementor-widget-eae-content-switcher .eae-content-switcher-wrapper .eae-cs-content-section {
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-eae-thumbgallery .eae-slide-heading {
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-eae-thumbgallery .eae-slide-text {
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-eae-thumbgallery .eae-slide-button .eae-slide-btn {
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-eae-data-table .eae-table__column-text {
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-eae-data-table .eae-table-body__text {
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-eae-data-table .eae-table-search-field {
            font-family: var(--e-global-typography-secondary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-secondary-font-weight);
        }

        .elementor-widget-retina .hfe-retina-image-container .hfe-retina-img {
            border-color: var(--e-global-color-primary);
        }

        .elementor-widget-retina .widget-image-caption {
            color: var(--e-global-color-text);
            font-family: var(--e-global-typography-text-font-family), Sans-serif;
            font-weight: var(--e-global-typography-text-font-weight);
        }

        .elementor-widget-copyright .hfe-copyright-wrapper a,
        .elementor-widget-copyright .hfe-copyright-wrapper {
            color: var(--e-global-color-text);
        }

        .elementor-widget-copyright .hfe-copyright-wrapper,
        .elementor-widget-copyright .hfe-copyright-wrapper a {
            font-family: var(--e-global-typography-text-font-family), Sans-serif;
            font-weight: var(--e-global-typography-text-font-weight);
        }

        .elementor-widget-navigation-menu a.hfe-menu-item,
        .elementor-widget-navigation-menu a.hfe-sub-menu-item {
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-navigation-menu .menu-item a.hfe-menu-item,
        .elementor-widget-navigation-menu .sub-menu a.hfe-sub-menu-item {
            color: var(--e-global-color-text);
        }

        .elementor-widget-navigation-menu .menu-item a.hfe-menu-item:hover,
        .elementor-widget-navigation-menu .sub-menu a.hfe-sub-menu-item:hover,
        .elementor-widget-navigation-menu .menu-item.current-menu-item a.hfe-menu-item,
        .elementor-widget-navigation-menu .menu-item a.hfe-menu-item.highlighted,
        .elementor-widget-navigation-menu .menu-item a.hfe-menu-item:focus {
            color: var(--e-global-color-accent);
        }

        .elementor-widget-navigation-menu .hfe-nav-menu-layout:not(.hfe-pointer__framed) .menu-item.parent a.hfe-menu-item:before,
        .elementor-widget-navigation-menu .hfe-nav-menu-layout:not(.hfe-pointer__framed) .menu-item.parent a.hfe-menu-item:after {
            background-color: var(--e-global-color-accent);
        }

        .elementor-widget-navigation-menu .hfe-nav-menu-layout:not(.hfe-pointer__framed) .menu-item.parent .sub-menu .hfe-has-submenu-container a:after {
            background-color: var(--e-global-color-accent);
        }

        .elementor-widget-navigation-menu .hfe-pointer__framed .menu-item.parent a.hfe-menu-item:before,
        .elementor-widget-navigation-menu .hfe-pointer__framed .menu-item.parent a.hfe-menu-item:after {
            border-color: var(--e-global-color-accent);
        }

        .elementor-widget-navigation-menu .sub-menu li a.hfe-sub-menu-item,
        .elementor-widget-navigation-menu nav.hfe-dropdown li a.hfe-sub-menu-item,
        .elementor-widget-navigation-menu nav.hfe-dropdown li a.hfe-menu-item,
        .elementor-widget-navigation-menu nav.hfe-dropdown-expandible li a.hfe-menu-item,
        .elementor-widget-navigation-menu nav.hfe-dropdown-expandible li a.hfe-sub-menu-item {
            font-family: var(--e-global-typography-accent-font-family), Sans-serif;
            font-weight: var(--e-global-typography-accent-font-weight);
        }

        .elementor-widget-navigation-menu .menu-item a.hfe-menu-item.elementor-button {
            font-family: var(--e-global-typography-accent-font-family), Sans-serif;
            font-weight: var(--e-global-typography-accent-font-weight);
            background-color: var(--e-global-color-accent);
        }

        .elementor-widget-navigation-menu .menu-item a.hfe-menu-item.elementor-button:hover {
            background-color: var(--e-global-color-accent);
        }

        .elementor-widget-page-title .elementor-heading-title,
        .elementor-widget-page-title .hfe-page-title a {
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
            color: var(--e-global-color-primary);
        }

        .elementor-widget-page-title .hfe-page-title-icon i {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-page-title .hfe-page-title-icon svg {
            fill: var(--e-global-color-primary);
        }

        .elementor-widget-hfe-site-title .elementor-heading-title,
        .elementor-widget-hfe-site-title .hfe-heading a {
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-hfe-site-title .hfe-heading-text {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-hfe-site-title .hfe-icon i {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-hfe-site-title .hfe-icon svg {
            fill: var(--e-global-color-primary);
        }

        .elementor-widget-hfe-site-tagline .hfe-site-tagline {
            font-family: var(--e-global-typography-secondary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-secondary-font-weight);
            color: var(--e-global-color-secondary);
        }

        .elementor-widget-hfe-site-tagline .hfe-icon i {
            color: var(--e-global-color-primary);
        }

        .elementor-widget-hfe-site-tagline .hfe-icon svg {
            fill: var(--e-global-color-primary);
        }

        .elementor-widget-site-logo .hfe-site-logo-container .hfe-site-logo-img {
            border-color: var(--e-global-color-primary);
        }

        .elementor-widget-site-logo .widget-image-caption {
            color: var(--e-global-color-text);
            font-family: var(--e-global-typography-text-font-family), Sans-serif;
            font-weight: var(--e-global-typography-text-font-weight);
        }

        .elementor-widget-hfe-search-button input[type="search"].hfe-search-form__input,
        .elementor-widget-hfe-search-button .hfe-search-icon-toggle {
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-hfe-search-button .hfe-search-form__input {
            color: var(--e-global-color-text);
        }

        .elementor-widget-hfe-search-button .hfe-search-form__input::placeholder {
            color: var(--e-global-color-text);
        }

        .elementor-widget-hfe-search-button .hfe-search-form__container,
        .elementor-widget-hfe-search-button .hfe-search-icon-toggle .hfe-search-form__input,
        .elementor-widget-hfe-search-button .hfe-input-focus .hfe-search-icon-toggle .hfe-search-form__input {
            border-color: var(--e-global-color-primary);
        }

        .elementor-widget-hfe-search-button .hfe-search-form__input:focus::placeholder {
            color: var(--e-global-color-text);
        }

        .elementor-widget-hfe-search-button .hfe-search-form__container button#clear-with-button,
        .elementor-widget-hfe-search-button .hfe-search-form__container button#clear,
        .elementor-widget-hfe-search-button .hfe-search-icon-toggle button#clear {
            color: var(--e-global-color-text);
        }

        .elementor-widget-hfe-cart .hfe-menu-cart__toggle .elementor-button {
            font-family: var(--e-global-typography-primary-font-family), Sans-serif;
            font-weight: var(--e-global-typography-primary-font-weight);
        }

        .elementor-widget-text-path {
            font-family: var(--e-global-typography-text-font-family), Sans-serif;
            font-weight: var(--e-global-typography-text-font-weight);
        }

        .elementor-241221 .elementor-element.elementor-element-df78951 .elementor-heading-title {
            color: #000000;
            font-family: "Kanit", Sans-serif;
            font-weight: 600;
        }

        .elementor-241221 .elementor-element.elementor-element-f5518f1 .eae-table-container .eae-table-wrapper {
            margin-right: auto;
        }

        .elementor-241221 .elementor-element.elementor-element-f5518f1 .eae-table__column-text {
            color: #fff;
        }

        .elementor-241221 .elementor-element.elementor-element-f5518f1 .eae-table__head-column-wrapper .eae-head-icon {
            color: #fff;
        }

        .elementor-241221 .elementor-element.elementor-element-f5518f1 .eae-table__head_column {
            background-color: #FF6900;
            padding: 12px 12px 12px 12px;
        }

        .elementor-241221 .elementor-element.elementor-element-f5518f1 .eae-table .eae-table-head .eae-table__head_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-241221 .elementor-element.elementor-element-f5518f1 .eae-table__head-column-wrapper {
            text-align: center;
        }

        .elementor-241221 .elementor-element.elementor-element-f5518f1 .eae-table .eae-table__body .eae-table__body-row:nth-child(even) {
            background-color: #F0F0F0;
        }

        .elementor-241221 .elementor-element.elementor-element-f5518f1 .eae-table .eae-table__body .eae-table__body_row_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-241221 .elementor-element.elementor-element-f5518f1 .eae-table__body_row_column {
            padding: 12px 12px 12px 12px;
        }

        .elementor-241221 .elementor-element.elementor-element-f5518f1 .eae-table__col-inner {
            text-align: center;
        }

        .elementor-241221 .elementor-element.elementor-element-3985916 iframe {
            height: 360px;
        }

        .elementor-241163 .elementor-element.elementor-element-305e9eae .elementor-heading-title {
            color: #000000;
            font-family: "Kanit", Sans-serif;
            font-weight: 600;
        }

        .elementor-241163 .elementor-element.elementor-element-09e74b2 .eae-table-container .eae-table-wrapper {
            margin-right: auto;
        }

        .elementor-241163 .elementor-element.elementor-element-09e74b2 .eae-table__column-text {
            color: #fff;
        }

        .elementor-241163 .elementor-element.elementor-element-09e74b2 .eae-table__head-column-wrapper .eae-head-icon {
            color: #fff;
        }

        .elementor-241163 .elementor-element.elementor-element-09e74b2 .eae-table__head_column {
            background-color: #FF6900;
            padding: 12px 12px 12px 12px;
        }

        .elementor-241163 .elementor-element.elementor-element-09e74b2 .eae-table .eae-table-head .eae-table__head_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-241163 .elementor-element.elementor-element-09e74b2 .eae-table__head-column-wrapper {
            text-align: center;
        }

        .elementor-241163 .elementor-element.elementor-element-09e74b2 .eae-table .eae-table__body .eae-table__body-row:nth-child(even) {
            background-color: #F0F0F0;
        }

        .elementor-241163 .elementor-element.elementor-element-09e74b2 .eae-table .eae-table__body .eae-table__body_row_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-241163 .elementor-element.elementor-element-09e74b2 .eae-table__body_row_column {
            padding: 12px 12px 12px 12px;
        }

        .elementor-241163 .elementor-element.elementor-element-09e74b2 .eae-table__col-inner {
            text-align: center;
        }

        .elementor-241163 .elementor-element.elementor-element-3f3b767e iframe {
            height: 360px;
        }

        .elementor-236208 .elementor-element.elementor-element-305e9eae .elementor-heading-title {
            color: #000000;
            font-family: "Kanit", Sans-serif;
            font-weight: 600;
        }

        .elementor-236208 .elementor-element.elementor-element-08285a2 .eae-table-container .eae-table-wrapper {
            margin-right: auto;
        }

        .elementor-236208 .elementor-element.elementor-element-08285a2 .eae-table__column-text {
            color: #fff;
        }

        .elementor-236208 .elementor-element.elementor-element-08285a2 .eae-table__head-column-wrapper .eae-head-icon {
            color: #fff;
        }

        .elementor-236208 .elementor-element.elementor-element-08285a2 .eae-table__head_column {
            background-color: #FF6900;
            padding: 12px 12px 12px 12px;
        }

        .elementor-236208 .elementor-element.elementor-element-08285a2 .eae-table .eae-table-head .eae-table__head_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-236208 .elementor-element.elementor-element-08285a2 .eae-table__head-column-wrapper {
            text-align: center;
        }

        .elementor-236208 .elementor-element.elementor-element-08285a2 .eae-table .eae-table__body .eae-table__body-row:nth-child(even) {
            background-color: #F0F0F0;
        }

        .elementor-236208 .elementor-element.elementor-element-08285a2 .eae-table .eae-table__body .eae-table__body_row_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-236208 .elementor-element.elementor-element-08285a2 .eae-table__body_row_column {
            padding: 12px 12px 12px 12px;
        }

        .elementor-236208 .elementor-element.elementor-element-08285a2 .eae-table__col-inner {
            text-align: center;
        }

        .elementor-236208 .elementor-element.elementor-element-3f3b767e iframe {
            height: 360px;
        }

        .elementor-235960 .elementor-element.elementor-element-6d9427e6 .elementor-heading-title {
            color: #000000;
            font-family: "Kanit", Sans-serif;
            font-weight: 600;
        }

        .elementor-235960 .elementor-element.elementor-element-745fcee .eae-table-container .eae-table-wrapper {
            margin-right: auto;
        }

        .elementor-235960 .elementor-element.elementor-element-745fcee .eae-table__column-text {
            color: #fff;
        }

        .elementor-235960 .elementor-element.elementor-element-745fcee .eae-table__head-column-wrapper .eae-head-icon {
            color: #fff;
        }

        .elementor-235960 .elementor-element.elementor-element-745fcee .eae-table__head_column {
            background-color: #FF6900;
            padding: 12px 12px 12px 12px;
        }

        .elementor-235960 .elementor-element.elementor-element-745fcee .eae-table .eae-table-head .eae-table__head_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-235960 .elementor-element.elementor-element-745fcee .eae-table__head-column-wrapper {
            text-align: center;
        }

        .elementor-235960 .elementor-element.elementor-element-745fcee .eae-table .eae-table__body .eae-table__body-row:nth-child(even) {
            background-color: #F0F0F0;
        }

        .elementor-235960 .elementor-element.elementor-element-745fcee .eae-table .eae-table__body .eae-table__body_row_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-235960 .elementor-element.elementor-element-745fcee .eae-table__body_row_column {
            padding: 12px 12px 12px 12px;
        }

        .elementor-235960 .elementor-element.elementor-element-745fcee .eae-table__col-inner {
            text-align: center;
        }

        .elementor-235960 .elementor-element.elementor-element-62c1ea73 iframe {
            height: 360px;
        }

        .elementor-239281 .elementor-element.elementor-element-305e9eae .elementor-heading-title {
            color: #000000;
            font-family: "Kanit", Sans-serif;
            font-weight: 600;
        }

        .elementor-239281 .elementor-element.elementor-element-237c242 .eae-table-container .eae-table-wrapper {
            margin-right: auto;
        }

        .elementor-239281 .elementor-element.elementor-element-237c242 .eae-table__column-text {
            color: #fff;
        }

        .elementor-239281 .elementor-element.elementor-element-237c242 .eae-table__head-column-wrapper .eae-head-icon {
            color: #fff;
        }

        .elementor-239281 .elementor-element.elementor-element-237c242 .eae-table__head_column {
            background-color: #FF6900;
            padding: 12px 12px 12px 12px;
        }

        .elementor-239281 .elementor-element.elementor-element-237c242 .eae-table .eae-table-head .eae-table__head_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-239281 .elementor-element.elementor-element-237c242 .eae-table__head-column-wrapper {
            text-align: center;
        }

        .elementor-239281 .elementor-element.elementor-element-237c242 .eae-table .eae-table__body .eae-table__body-row:nth-child(even) {
            background-color: #F0F0F0;
        }

        .elementor-239281 .elementor-element.elementor-element-237c242 .eae-table .eae-table__body .eae-table__body_row_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-239281 .elementor-element.elementor-element-237c242 .eae-table__body_row_column {
            padding: 12px 12px 12px 12px;
        }

        .elementor-239281 .elementor-element.elementor-element-237c242 .eae-table__col-inner {
            text-align: center;
        }

        .elementor-239281 .elementor-element.elementor-element-3f3b767e iframe {
            height: 360px;
        }

        .elementor-238612 .elementor-element.elementor-element-305e9eae .elementor-heading-title {
            color: #000000;
            font-family: "Kanit", Sans-serif;
            font-weight: 600;
        }

        .elementor-238612 .elementor-element.elementor-element-2fbb698 .eae-table-container .eae-table-wrapper {
            margin-right: auto;
        }

        .elementor-238612 .elementor-element.elementor-element-2fbb698 .eae-table__column-text {
            color: #fff;
        }

        .elementor-238612 .elementor-element.elementor-element-2fbb698 .eae-table__head-column-wrapper .eae-head-icon {
            color: #fff;
        }

        .elementor-238612 .elementor-element.elementor-element-2fbb698 .eae-table__head_column {
            background-color: #FF6900;
            padding: 12px 12px 12px 12px;
        }

        .elementor-238612 .elementor-element.elementor-element-2fbb698 .eae-table .eae-table-head .eae-table__head_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-238612 .elementor-element.elementor-element-2fbb698 .eae-table__head-column-wrapper {
            text-align: center;
        }

        .elementor-238612 .elementor-element.elementor-element-2fbb698 .eae-table .eae-table__body .eae-table__body-row:nth-child(even) {
            background-color: #F0F0F0;
        }

        .elementor-238612 .elementor-element.elementor-element-2fbb698 .eae-table .eae-table__body .eae-table__body_row_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-238612 .elementor-element.elementor-element-2fbb698 .eae-table__body_row_column {
            padding: 12px 12px 12px 12px;
        }

        .elementor-238612 .elementor-element.elementor-element-2fbb698 .eae-table__col-inner {
            text-align: center;
        }

        .elementor-238612 .elementor-element.elementor-element-3f3b767e iframe {
            height: 360px;
        }

        .elementor-238773 .elementor-element.elementor-element-6d9427e6 .elementor-heading-title {
            color: #000000;
            font-family: "Kanit", Sans-serif;
            font-weight: 600;
        }

        .elementor-238773 .elementor-element.elementor-element-d7515f6 .eae-table-container .eae-table-wrapper {
            margin-right: auto;
        }

        .elementor-238773 .elementor-element.elementor-element-d7515f6 .eae-table__column-text {
            color: #fff;
        }

        .elementor-238773 .elementor-element.elementor-element-d7515f6 .eae-table__head-column-wrapper .eae-head-icon {
            color: #fff;
        }

        .elementor-238773 .elementor-element.elementor-element-d7515f6 .eae-table__head_column {
            background-color: #FF6900;
            padding: 12px 12px 12px 12px;
        }

        .elementor-238773 .elementor-element.elementor-element-d7515f6 .eae-table .eae-table-head .eae-table__head_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-238773 .elementor-element.elementor-element-d7515f6 .eae-table__head-column-wrapper {
            text-align: center;
        }

        .elementor-238773 .elementor-element.elementor-element-d7515f6 .eae-table .eae-table__body .eae-table__body-row:nth-child(even) {
            background-color: #F0F0F0;
        }

        .elementor-238773 .elementor-element.elementor-element-d7515f6 .eae-table .eae-table__body .eae-table__body_row_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-238773 .elementor-element.elementor-element-d7515f6 .eae-table__body_row_column {
            padding: 12px 12px 12px 12px;
        }

        .elementor-238773 .elementor-element.elementor-element-d7515f6 .eae-table__col-inner {
            text-align: center;
        }

        .elementor-238773 .elementor-element.elementor-element-62c1ea73 iframe {
            height: 360px;
        }

        .elementor-236193 .elementor-element.elementor-element-305e9eae .elementor-heading-title {
            color: #000000;
            font-family: "Kanit", Sans-serif;
            font-weight: 600;
        }

        .elementor-236193 .elementor-element.elementor-element-e622212 .eae-table-container .eae-table-wrapper {
            margin-right: auto;
        }

        .elementor-236193 .elementor-element.elementor-element-e622212 .eae-table__column-text {
            color: #fff;
        }

        .elementor-236193 .elementor-element.elementor-element-e622212 .eae-table__head-column-wrapper .eae-head-icon {
            color: #fff;
        }

        .elementor-236193 .elementor-element.elementor-element-e622212 .eae-table__head_column {
            background-color: #FF6900;
            padding: 12px 12px 12px 12px;
        }

        .elementor-236193 .elementor-element.elementor-element-e622212 .eae-table .eae-table-head .eae-table__head_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-236193 .elementor-element.elementor-element-e622212 .eae-table__head-column-wrapper {
            text-align: center;
        }

        .elementor-236193 .elementor-element.elementor-element-e622212 .eae-table .eae-table__body .eae-table__body-row:nth-child(even) {
            background-color: #F0F0F0;
        }

        .elementor-236193 .elementor-element.elementor-element-e622212 .eae-table .eae-table__body .eae-table__body_row_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-236193 .elementor-element.elementor-element-e622212 .eae-table__body_row_column {
            padding: 12px 12px 12px 12px;
        }

        .elementor-236193 .elementor-element.elementor-element-e622212 .eae-table__col-inner {
            text-align: center;
        }

        .elementor-236193 .elementor-element.elementor-element-3f3b767e iframe {
            height: 360px;
        }

        .elementor-236148 .elementor-element.elementor-element-305e9eae .elementor-heading-title {
            color: #000000;
            font-family: "Kanit", Sans-serif;
            font-weight: 600;
        }

        .elementor-236148 .elementor-element.elementor-element-f5e1ccb .eae-table-container .eae-table-wrapper {
            margin-right: auto;
        }

        .elementor-236148 .elementor-element.elementor-element-f5e1ccb .eae-table__column-text {
            color: #fff;
        }

        .elementor-236148 .elementor-element.elementor-element-f5e1ccb .eae-table__head-column-wrapper .eae-head-icon {
            color: #fff;
        }

        .elementor-236148 .elementor-element.elementor-element-f5e1ccb .eae-table__head_column {
            background-color: #FF6900;
            padding: 12px 12px 12px 12px;
        }

        .elementor-236148 .elementor-element.elementor-element-f5e1ccb .eae-table .eae-table-head .eae-table__head_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-236148 .elementor-element.elementor-element-f5e1ccb .eae-table__head-column-wrapper {
            text-align: center;
        }

        .elementor-236148 .elementor-element.elementor-element-f5e1ccb .eae-table .eae-table__body .eae-table__body-row:nth-child(even) {
            background-color: #F0F0F0;
        }

        .elementor-236148 .elementor-element.elementor-element-f5e1ccb .eae-table .eae-table__body .eae-table__body_row_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-236148 .elementor-element.elementor-element-f5e1ccb .eae-table__body_row_column {
            padding: 12px 12px 12px 12px;
        }

        .elementor-236148 .elementor-element.elementor-element-f5e1ccb .eae-table__col-inner {
            text-align: center;
        }

        .elementor-236148 .elementor-element.elementor-element-3f3b767e iframe {
            height: 360px;
        }

        .elementor-236138 .elementor-element.elementor-element-305e9eae .elementor-heading-title {
            color: #000000;
            font-family: "Kanit", Sans-serif;
            font-weight: 600;
        }

        .elementor-236138 .elementor-element.elementor-element-3f3b767e iframe {
            height: 360px;
        }

        .elementor-236124 .elementor-element.elementor-element-305e9eae .elementor-heading-title {
            color: #000000;
            font-family: "Kanit", Sans-serif;
            font-weight: 600;
        }

        .elementor-236124 .elementor-element.elementor-element-3f3b767e iframe {
            height: 360px;
        }

        .elementor-236114 .elementor-element.elementor-element-305e9eae .elementor-heading-title {
            color: #000000;
            font-family: "Kanit", Sans-serif;
            font-weight: 600;
        }

        .elementor-236114 .elementor-element.elementor-element-62a831b .eae-table-container .eae-table-wrapper {
            margin-right: auto;
        }

        .elementor-236114 .elementor-element.elementor-element-62a831b .eae-table__column-text {
            color: #fff;
        }

        .elementor-236114 .elementor-element.elementor-element-62a831b .eae-table__head-column-wrapper .eae-head-icon {
            color: #fff;
        }

        .elementor-236114 .elementor-element.elementor-element-62a831b .eae-table__head_column {
            background-color: #FF6900;
            padding: 12px 12px 12px 12px;
        }

        .elementor-236114 .elementor-element.elementor-element-62a831b .eae-table .eae-table-head .eae-table__head_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-236114 .elementor-element.elementor-element-62a831b .eae-table__head-column-wrapper {
            text-align: center;
        }

        .elementor-236114 .elementor-element.elementor-element-62a831b .eae-table .eae-table__body .eae-table__body-row:nth-child(even) {
            background-color: #F0F0F0;
        }

        .elementor-236114 .elementor-element.elementor-element-62a831b .eae-table .eae-table__body .eae-table__body_row_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-236114 .elementor-element.elementor-element-62a831b .eae-table__body_row_column {
            padding: 12px 12px 12px 12px;
        }

        .elementor-236114 .elementor-element.elementor-element-62a831b .eae-table__col-inner {
            text-align: center;
        }

        .elementor-236114 .elementor-element.elementor-element-3f3b767e iframe {
            height: 360px;
        }

        .elementor-236103 .elementor-element.elementor-element-305e9eae .elementor-heading-title {
            color: #000000;
            font-family: "Kanit", Sans-serif;
            font-weight: 600;
        }

        .elementor-236103 .elementor-element.elementor-element-076a85e .eae-table-container .eae-table-wrapper {
            margin-right: auto;
        }

        .elementor-236103 .elementor-element.elementor-element-076a85e .eae-table__column-text {
            color: #fff;
        }

        .elementor-236103 .elementor-element.elementor-element-076a85e .eae-table__head-column-wrapper .eae-head-icon {
            color: #fff;
        }

        .elementor-236103 .elementor-element.elementor-element-076a85e .eae-table__head_column {
            background-color: #FF6900;
            padding: 12px 12px 12px 12px;
        }

        .elementor-236103 .elementor-element.elementor-element-076a85e .eae-table .eae-table-head .eae-table__head_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-236103 .elementor-element.elementor-element-076a85e .eae-table__head-column-wrapper {
            text-align: center;
        }

        .elementor-236103 .elementor-element.elementor-element-076a85e .eae-table .eae-table__body .eae-table__body-row:nth-child(even) {
            background-color: #F0F0F0;
        }

        .elementor-236103 .elementor-element.elementor-element-076a85e .eae-table .eae-table__body .eae-table__body_row_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-236103 .elementor-element.elementor-element-076a85e .eae-table__body_row_column {
            padding: 12px 12px 12px 12px;
        }

        .elementor-236103 .elementor-element.elementor-element-076a85e .eae-table__col-inner {
            text-align: center;
        }

        .elementor-236103 .elementor-element.elementor-element-3f3b767e iframe {
            height: 360px;
        }

        .elementor-236090 .elementor-element.elementor-element-305e9eae .elementor-heading-title {
            color: #000000;
            font-family: "Kanit", Sans-serif;
            font-weight: 600;
        }

        .elementor-236090 .elementor-element.elementor-element-e0b4df2 .eae-table-container .eae-table-wrapper {
            margin-right: auto;
        }

        .elementor-236090 .elementor-element.elementor-element-e0b4df2 .eae-table__column-text {
            color: #fff;
        }

        .elementor-236090 .elementor-element.elementor-element-e0b4df2 .eae-table__head-column-wrapper .eae-head-icon {
            color: #fff;
        }

        .elementor-236090 .elementor-element.elementor-element-e0b4df2 .eae-table__head_column {
            background-color: #FF6900;
            padding: 12px 12px 12px 12px;
        }

        .elementor-236090 .elementor-element.elementor-element-e0b4df2 .eae-table .eae-table-head .eae-table__head_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-236090 .elementor-element.elementor-element-e0b4df2 .eae-table__head-column-wrapper {
            text-align: center;
        }

        .elementor-236090 .elementor-element.elementor-element-e0b4df2 .eae-table .eae-table__body .eae-table__body-row:nth-child(even) {
            background-color: #F0F0F0;
        }

        .elementor-236090 .elementor-element.elementor-element-e0b4df2 .eae-table .eae-table__body .eae-table__body_row_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-236090 .elementor-element.elementor-element-e0b4df2 .eae-table__body_row_column {
            padding: 12px 12px 12px 12px;
        }

        .elementor-236090 .elementor-element.elementor-element-e0b4df2 .eae-table__col-inner {
            text-align: center;
        }

        .elementor-236090 .elementor-element.elementor-element-3f3b767e iframe {
            height: 360px;
        }

        .elementor-236082 .elementor-element.elementor-element-305e9eae .elementor-heading-title {
            color: #000000;
            font-family: "Kanit", Sans-serif;
            font-weight: 600;
        }

        .elementor-236082 .elementor-element.elementor-element-3f3b767e iframe {
            height: 360px;
        }

        .elementor-236063 .elementor-element.elementor-element-305e9eae .elementor-heading-title {
            color: #000000;
            font-family: "Kanit", Sans-serif;
            font-weight: 600;
        }

        .elementor-236063 .elementor-element.elementor-element-435de8c .eae-table-container .eae-table-wrapper {
            margin-right: auto;
        }

        .elementor-236063 .elementor-element.elementor-element-435de8c .eae-table__column-text {
            color: #fff;
        }

        .elementor-236063 .elementor-element.elementor-element-435de8c .eae-table__head-column-wrapper .eae-head-icon {
            color: #fff;
        }

        .elementor-236063 .elementor-element.elementor-element-435de8c .eae-table__head_column {
            background-color: #FF6900;
            padding: 12px 12px 12px 12px;
        }

        .elementor-236063 .elementor-element.elementor-element-435de8c .eae-table .eae-table-head .eae-table__head_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-236063 .elementor-element.elementor-element-435de8c .eae-table__head-column-wrapper {
            text-align: center;
        }

        .elementor-236063 .elementor-element.elementor-element-435de8c .eae-table .eae-table__body .eae-table__body-row:nth-child(even) {
            background-color: #F0F0F0;
        }

        .elementor-236063 .elementor-element.elementor-element-435de8c .eae-table .eae-table__body .eae-table__body_row_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-236063 .elementor-element.elementor-element-435de8c .eae-table__body_row_column {
            padding: 12px 12px 12px 12px;
        }

        .elementor-236063 .elementor-element.elementor-element-435de8c .eae-table__col-inner {
            text-align: center;
        }

        .elementor-236063 .elementor-element.elementor-element-3f3b767e iframe {
            height: 360px;
        }

        .elementor-236049 .elementor-element.elementor-element-305e9eae .elementor-heading-title {
            color: #000000;
            font-family: "Kanit", Sans-serif;
            font-weight: 600;
        }

        .elementor-236049 .elementor-element.elementor-element-dd9406f .eae-table-container .eae-table-wrapper {
            margin-right: auto;
        }

        .elementor-236049 .elementor-element.elementor-element-dd9406f .eae-table__column-text {
            color: #fff;
        }

        .elementor-236049 .elementor-element.elementor-element-dd9406f .eae-table__head-column-wrapper .eae-head-icon {
            color: #fff;
        }

        .elementor-236049 .elementor-element.elementor-element-dd9406f .eae-table__head_column {
            background-color: #FF6900;
            padding: 12px 12px 12px 12px;
        }

        .elementor-236049 .elementor-element.elementor-element-dd9406f .eae-table .eae-table-head .eae-table__head_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-236049 .elementor-element.elementor-element-dd9406f .eae-table__head-column-wrapper {
            text-align: center;
        }

        .elementor-236049 .elementor-element.elementor-element-dd9406f .eae-table .eae-table__body .eae-table__body-row:nth-child(even) {
            background-color: #F0F0F0;
        }

        .elementor-236049 .elementor-element.elementor-element-dd9406f .eae-table .eae-table__body .eae-table__body_row_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-236049 .elementor-element.elementor-element-dd9406f .eae-table__body_row_column {
            padding: 12px 12px 12px 12px;
        }

        .elementor-236049 .elementor-element.elementor-element-dd9406f .eae-table__col-inner {
            text-align: center;
        }

        .elementor-236049 .elementor-element.elementor-element-3f3b767e iframe {
            height: 360px;
        }

        .elementor-236038 .elementor-element.elementor-element-305e9eae .elementor-heading-title {
            color: #000000;
            font-family: "Kanit", Sans-serif;
            font-weight: 600;
        }

        .elementor-236038 .elementor-element.elementor-element-0808bf9 .eae-table-container .eae-table-wrapper {
            margin-right: auto;
        }

        .elementor-236038 .elementor-element.elementor-element-0808bf9 .eae-table__column-text {
            color: #fff;
        }

        .elementor-236038 .elementor-element.elementor-element-0808bf9 .eae-table__head-column-wrapper .eae-head-icon {
            color: #fff;
        }

        .elementor-236038 .elementor-element.elementor-element-0808bf9 .eae-table__head_column {
            background-color: #FF6900;
            padding: 12px 12px 12px 12px;
        }

        .elementor-236038 .elementor-element.elementor-element-0808bf9 .eae-table .eae-table-head .eae-table__head_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-236038 .elementor-element.elementor-element-0808bf9 .eae-table__head-column-wrapper {
            text-align: center;
        }

        .elementor-236038 .elementor-element.elementor-element-0808bf9 .eae-table .eae-table__body .eae-table__body-row:nth-child(even) {
            background-color: #F0F0F0;
        }

        .elementor-236038 .elementor-element.elementor-element-0808bf9 .eae-table .eae-table__body .eae-table__body_row_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-236038 .elementor-element.elementor-element-0808bf9 .eae-table__body_row_column {
            padding: 12px 12px 12px 12px;
        }

        .elementor-236038 .elementor-element.elementor-element-0808bf9 .eae-table__col-inner {
            text-align: center;
        }

        .elementor-236038 .elementor-element.elementor-element-3f3b767e iframe {
            height: 360px;
        }

        .elementor-236020 .elementor-element.elementor-element-305e9eae .elementor-heading-title {
            color: #000000;
            font-family: "Kanit", Sans-serif;
            font-weight: 600;
        }

        .elementor-236020 .elementor-element.elementor-element-b292000 .eae-table-container .eae-table-wrapper {
            margin-right: auto;
        }

        .elementor-236020 .elementor-element.elementor-element-b292000 .eae-table__column-text {
            color: #fff;
        }

        .elementor-236020 .elementor-element.elementor-element-b292000 .eae-table__head-column-wrapper .eae-head-icon {
            color: #fff;
        }

        .elementor-236020 .elementor-element.elementor-element-b292000 .eae-table__head_column {
            background-color: #FF6900;
            padding: 12px 12px 12px 12px;
        }

        .elementor-236020 .elementor-element.elementor-element-b292000 .eae-table .eae-table-head .eae-table__head_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-236020 .elementor-element.elementor-element-b292000 .eae-table__head-column-wrapper {
            text-align: center;
        }

        .elementor-236020 .elementor-element.elementor-element-b292000 .eae-table .eae-table__body .eae-table__body-row:nth-child(even) {
            background-color: #F0F0F0;
        }

        .elementor-236020 .elementor-element.elementor-element-b292000 .eae-table .eae-table__body .eae-table__body_row_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-236020 .elementor-element.elementor-element-b292000 .eae-table__body_row_column {
            padding: 12px 12px 12px 12px;
        }

        .elementor-236020 .elementor-element.elementor-element-b292000 .eae-table__col-inner {
            text-align: center;
        }

        .elementor-236020 .elementor-element.elementor-element-3f3b767e iframe {
            height: 360px;
        }

        .elementor-236008 .elementor-element.elementor-element-305e9eae .elementor-heading-title {
            color: #000000;
            font-family: "Kanit", Sans-serif;
            font-weight: 600;
        }

        .elementor-236008 .elementor-element.elementor-element-d98500c .eae-table-container .eae-table-wrapper {
            margin-right: auto;
        }

        .elementor-236008 .elementor-element.elementor-element-d98500c .eae-table__column-text {
            color: #fff;
        }

        .elementor-236008 .elementor-element.elementor-element-d98500c .eae-table__head-column-wrapper .eae-head-icon {
            color: #fff;
        }

        .elementor-236008 .elementor-element.elementor-element-d98500c .eae-table__head_column {
            background-color: #FF6900;
            padding: 12px 12px 12px 12px;
        }

        .elementor-236008 .elementor-element.elementor-element-d98500c .eae-table .eae-table-head .eae-table__head_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-236008 .elementor-element.elementor-element-d98500c .eae-table__head-column-wrapper {
            text-align: center;
        }

        .elementor-236008 .elementor-element.elementor-element-d98500c .eae-table .eae-table__body .eae-table__body-row:nth-child(even) {
            background-color: #F0F0F0;
        }

        .elementor-236008 .elementor-element.elementor-element-d98500c .eae-table .eae-table__body .eae-table__body_row_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-236008 .elementor-element.elementor-element-d98500c .eae-table__body_row_column {
            padding: 12px 12px 12px 12px;
        }

        .elementor-236008 .elementor-element.elementor-element-d98500c .eae-table__col-inner {
            text-align: center;
        }

        .elementor-236008 .elementor-element.elementor-element-3f3b767e iframe {
            height: 360px;
        }

        .elementor-235992 .elementor-element.elementor-element-3326ce91 .elementor-heading-title {
            color: #000000;
            font-family: "Kanit", Sans-serif;
            font-weight: 600;
        }

        .elementor-235992 .elementor-element.elementor-element-ca38c00 .eae-table-container .eae-table-wrapper {
            margin-right: auto;
        }

        .elementor-235992 .elementor-element.elementor-element-ca38c00 .eae-table__column-text {
            color: #fff;
        }

        .elementor-235992 .elementor-element.elementor-element-ca38c00 .eae-table__head-column-wrapper .eae-head-icon {
            color: #fff;
        }

        .elementor-235992 .elementor-element.elementor-element-ca38c00 .eae-table__head_column {
            background-color: #FF6900;
            padding: 12px 12px 12px 12px;
        }

        .elementor-235992 .elementor-element.elementor-element-ca38c00 .eae-table .eae-table-head .eae-table__head_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-235992 .elementor-element.elementor-element-ca38c00 .eae-table__head-column-wrapper {
            text-align: center;
        }

        .elementor-235992 .elementor-element.elementor-element-ca38c00 .eae-table .eae-table__body .eae-table__body-row:nth-child(even) {
            background-color: #F0F0F0;
        }

        .elementor-235992 .elementor-element.elementor-element-ca38c00 .eae-table .eae-table__body .eae-table__body_row_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-235992 .elementor-element.elementor-element-ca38c00 .eae-table__body_row_column {
            padding: 12px 12px 12px 12px;
        }

        .elementor-235992 .elementor-element.elementor-element-ca38c00 .eae-table__col-inner {
            text-align: center;
        }

        .elementor-235992 .elementor-element.elementor-element-7e728684 iframe {
            height: 360px;
        }

        .elementor-235971 .elementor-element.elementor-element-7713b11f .elementor-heading-title {
            color: #000000;
            font-family: "Kanit", Sans-serif;
            font-weight: 600;
        }

        .elementor-235971 .elementor-element.elementor-element-676a3c6 .eae-table-container .eae-table-wrapper {
            margin-right: auto;
        }

        .elementor-235971 .elementor-element.elementor-element-676a3c6 .eae-table__column-text {
            color: #fff;
        }

        .elementor-235971 .elementor-element.elementor-element-676a3c6 .eae-table__head-column-wrapper .eae-head-icon {
            color: #fff;
        }

        .elementor-235971 .elementor-element.elementor-element-676a3c6 .eae-table__head_column {
            background-color: #FF6900;
            padding: 12px 12px 12px 12px;
        }

        .elementor-235971 .elementor-element.elementor-element-676a3c6 .eae-table .eae-table-head .eae-table__head_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-235971 .elementor-element.elementor-element-676a3c6 .eae-table__head-column-wrapper {
            text-align: center;
        }

        .elementor-235971 .elementor-element.elementor-element-676a3c6 .eae-table .eae-table__body .eae-table__body-row:nth-child(even) {
            background-color: #F0F0F0;
        }

        .elementor-235971 .elementor-element.elementor-element-676a3c6 .eae-table .eae-table__body .eae-table__body_row_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-235971 .elementor-element.elementor-element-676a3c6 .eae-table__body_row_column {
            padding: 12px 12px 12px 12px;
        }

        .elementor-235971 .elementor-element.elementor-element-676a3c6 .eae-table__col-inner {
            text-align: center;
        }

        .elementor-235971 .elementor-element.elementor-element-30bb4df6 iframe {
            height: 360px;
        }

        .elementor-235946 .elementor-element.elementor-element-562842fe .elementor-heading-title {
            color: #000000;
            font-family: "Kanit", Sans-serif;
            font-weight: 600;
        }

        .elementor-235946 .elementor-element.elementor-element-20e203f .eae-table-container .eae-table-wrapper {
            margin-right: auto;
        }

        .elementor-235946 .elementor-element.elementor-element-20e203f .eae-table__column-text {
            color: #fff;
        }

        .elementor-235946 .elementor-element.elementor-element-20e203f .eae-table__head-column-wrapper .eae-head-icon {
            color: #fff;
        }

        .elementor-235946 .elementor-element.elementor-element-20e203f .eae-table__head_column {
            background-color: #FF6900;
            padding: 12px 12px 12px 12px;
        }

        .elementor-235946 .elementor-element.elementor-element-20e203f .eae-table .eae-table-head .eae-table__head_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-235946 .elementor-element.elementor-element-20e203f .eae-table__head-column-wrapper {
            text-align: center;
        }

        .elementor-235946 .elementor-element.elementor-element-20e203f .eae-table .eae-table__body .eae-table__body-row:nth-child(even) {
            background-color: #F0F0F0;
        }

        .elementor-235946 .elementor-element.elementor-element-20e203f .eae-table .eae-table__body .eae-table__body_row_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-235946 .elementor-element.elementor-element-20e203f .eae-table__body_row_column {
            padding: 12px 12px 12px 12px;
        }

        .elementor-235946 .elementor-element.elementor-element-20e203f .eae-table__col-inner {
            text-align: center;
        }

        .elementor-235946 .elementor-element.elementor-element-2925b3dc iframe {
            height: 360px;
        }

        .elementor-235855 .elementor-element.elementor-element-37b710d .elementor-heading-title {
            color: #000000;
            font-family: "Kanit", Sans-serif;
            font-weight: 600;
        }

        .elementor-235855 .elementor-element.elementor-element-13a67d7 .eae-table-container .eae-table-wrapper {
            margin-right: auto;
        }

        .elementor-235855 .elementor-element.elementor-element-13a67d7 .eae-table__column-text {
            color: #fff;
        }

        .elementor-235855 .elementor-element.elementor-element-13a67d7 .eae-table__head-column-wrapper .eae-head-icon {
            color: #fff;
        }

        .elementor-235855 .elementor-element.elementor-element-13a67d7 .eae-table__head_column {
            background-color: #FF6900;
            padding: 12px 12px 12px 12px;
        }

        .elementor-235855 .elementor-element.elementor-element-13a67d7 .eae-table .eae-table-head .eae-table__head_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-235855 .elementor-element.elementor-element-13a67d7 .eae-table__head-column-wrapper {
            text-align: center;
        }

        .elementor-235855 .elementor-element.elementor-element-13a67d7 .eae-table .eae-table__body .eae-table__body-row:nth-child(even) {
            background-color: #F0F0F0;
        }

        .elementor-235855 .elementor-element.elementor-element-13a67d7 .eae-table .eae-table__body .eae-table__body_row_column {
            border-style: solid;
            border-width: 1px 1px 1px 1px;
        }

        .elementor-235855 .elementor-element.elementor-element-13a67d7 .eae-table__body_row_column {
            padding: 12px 12px 12px 12px;
        }

        .elementor-235855 .elementor-element.elementor-element-13a67d7 .eae-table__col-inner {
            text-align: center;
        }

        .elementor-235855 .elementor-element.elementor-element-cbceb21 iframe {
            height: 360px;
        }
    </style>
    <link rel='stylesheet' id='et-builder-googlefonts-cached-css'
        href='https://fonts.googleapis.com/css?family=Fredoka+One:regular|Kanit:100,200,300,regular,500,600,700,800,900,100italic,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic|Nunito:200,300,regular,500,600,700,800,900,200italic,300italic,italic,500italic,600italic,700italic,800italic,900italic&amp;subset=latin,latin-ext&amp;display=swap'
        type='text/css' media='all' />
    <link rel='stylesheet' id='hfe-widgets-style-css'
        href='https://www.eimaths-th.com/wp-content/cache/asset-cleanup/css/item/hfe-widgets-style-v4dba0137fb277de752ffd1f99acf1f2d015dc792.css'
        type='text/css' media='all' />
    <link rel='stylesheet' id='popup-maker-site-css'
        href='https://www.eimaths-th.com/wp-content/uploads/pum/pum-site-styles1a56.css?generated=1671691530&amp;ver=1.17.1'
        type='text/css' media='all' />
    <link rel='stylesheet' id='divi-style-pum-css'
        href='https://www.eimaths-th.com/wp-content/themes/Divi/style-static.min0349.css?ver=4.19.1' type='text/css'
        media='all' />
    <link rel='stylesheet' id='google-fonts-1-css'
        href='https://fonts.googleapis.com/css?family=Roboto%3A100%2C100italic%2C200%2C200italic%2C300%2C300italic%2C400%2C400italic%2C500%2C500italic%2C600%2C600italic%2C700%2C700italic%2C800%2C800italic%2C900%2C900italic%7CRoboto+Slab%3A100%2C100italic%2C200%2C200italic%2C300%2C300italic%2C400%2C400italic%2C500%2C500italic%2C600%2C600italic%2C700%2C700italic%2C800%2C800italic%2C900%2C900italic%7CFredoka+One%3A100%2C100italic%2C200%2C200italic%2C300%2C300italic%2C400%2C400italic%2C500%2C500italic%2C600%2C600italic%2C700%2C700italic%2C800%2C800italic%2C900%2C900italic&amp;display=auto&amp;ver=6.0.5'
        type='text/css' media='all' />
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <script type='text/javascript' src='https://www.eimaths-th.com/wp-includes/js/jquery/jquery.minaf6c.js?ver=3.6.0'
        id='jquery-core-js'></script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-includes/js/jquery/jquery-migrate.mind617.js?ver=3.3.2' id='jquery-migrate-js'>
    </script>
    <script type='text/javascript' id='jquery-js-after'>
        jqueryParams.length && $.each(jqueryParams, function(e, r) {
            if ("function" == typeof r) {
                var n = String(r);
                n.replace("$", "jQuery");
                var a = new Function("return " + n)();
                $(document).ready(a)
            }
        });
    </script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/plugins/mailchimp-for-divi-contact-form-pro/public/js/divi-contact-form-mailchimp-extension-publicd315.js?ver=2.6.0'
        id='divi-contact-form-mailchimp-extension-js'></script>
    <script type='text/javascript' id='divi-contact-form-mailchimp-extension-js-after'>
        /* <!\[CDATA\[ */
        var dcfme = {
            "theme_url": "https://eimaths.com/wp-content/themes/Divi",
            "members_url": "https://eimaths.com/members",
            "home_url": "https://eimaths.com",
            "ajax_url": "https://eimaths.com/wp-admin/admin-ajax.php",
            "nonce": "22fef77d9b",
            "post_id": 260
        };
        /* \]\]> */
    </script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/plugins/revslider/public/assets/js/rbtools.min9b30.js?ver=6.3.1'
        id='tp-tools-js'></script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/plugins/revslider/public/assets/js/rs6.min9b30.js?ver=6.3.1'
        id='revmin-js'></script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/plugins/supercarousel/public/js/supercarouselmerged9e95.js?ver=3.8.0'
        id='sc_merged-js'></script>
    <link rel="https://api.w.org/" href="https://www.eimaths-th.com/wp-json/index.html" />
    <link rel="alternate" type="application/json" href="https://www.eimaths-th.com/wp-json/wp/v2/pages/260.json" />
    <link rel="EditURI" type="application/rsd+xml" title="RSD" href="../xmlrpc0db0.php?rsd" />
    <link rel="wlwmanifest" type="application/wlwmanifest+xml"
        href="https://www.eimaths-th.com/wp-includes/wlwmanifest.xml" />
    <meta name="generator" content="WordPress 6.0.5" />
    <meta name="generator" content="WooCommerce 7.2.2" />
    <link rel='shortlink' href='../index8e1b.html?p=260' />
    <link rel="alternate" type="application/json+oembed"
        href="https://www.eimaths-th.com/wp-json/oembed/1.0/embedb970.json?url=https%3A%2F%2Feimaths.com%2Fcontact-us%2F" />
    <link rel="alternate" type="text/xml+oembed"
        href="https://www.eimaths-th.com/wp-json/oembed/1.0/embed38dd?url=https%3A%2F%2Feimaths.com%2Fcontact-us%2F&amp;format=xml" />
    <!-- start Simple Custom CSS and JS -->
    <style type="text/css">
        .woocommerce a.button,
        .woocommerce a.button.alt,
        .woocommerce a.button.alt:hover,
        .woocommerce a.button:hover,
        .woocommerce button.button,
        .woocommerce button.button.alt,
        .woocommerce button.button.alt.disabled,
        .woocommerce button.button.alt.disabled:hover,
        .woocommerce button.button.alt:hover,
        .woocommerce div.product p.price,
        .woocommerce div.product span.price,
        .woocommerce input.button,
        .woocommerce input.button.alt,
        .woocommerce input.button.alt:hover,
        .woocommerce input.button:hover,
        .wp-pagenavi a:hover,
        .wp-pagenavi span.current {
            color: #ffffff;
        }
    </style>
    <!-- end Simple Custom CSS and JS -->

    <!-- GA Google Analytics @ https://m0n.co/ga -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-ZV46434GKP"></script>

    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
        gtag('config', 'G-ZV46434GKP');
    </script>

    {{-- <script>
        jQuery(document).ready(function() {
            var x = 0;
            jQuery('.et_pb_contact_form').each(function() {
                jQuery(this).append(
                    '<p class="et_pb_contact_field"><span class="subscribe_to_mailing_list et_pb_contact_field_checkbox" style="display: none;"><input id="mailing_list_' +
                    x +
                    '" class="input" type="checkbox" name="add_to_mailing_list" checked><label for="mailing_list_' +
                    x + '">							</label></span></p>');

                x++;
            });
        });
    </script> --}}
    <style type="text/css">
        .shipping-pickup-store td .title {
            float: left;
            line-height: 30px;
        }

        .shipping-pickup-store td span.text {
            float: right;
        }

        .shipping-pickup-store td span.description {
            clear: both;
        }

        .shipping-pickup-store td>span:not([class*="select"]) {
            display: block;
            font-size: 14px;
            font-weight: normal;
            line-height: 1.4;
            margin-bottom: 0;
            padding: 6px 0;
            text-align: justify;
        }

        .shipping-pickup-store td #shipping-pickup-store-select {
            width: 100%;
        }

        .wps-store-details iframe {
            width: 100%;
        }
    </style>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <noscript>
        <style>
            .woocommerce-product-gallery {
                opacity: 1 !important;
            }
        </style>
    </noscript>

    <!-- Meta Pixel Code -->
    <script type='text/javascript'>
        ! function(f, b, e, v, n, t, s) {
            if (f.fbq) return;
            n = f.fbq = function() {
                n.callMethod ?
                    n.callMethod.apply(n, arguments) : n.queue.push(arguments)
            };
            if (!f._fbq) f._fbq = n;
            n.push = n;
            n.loaded = !0;
            n.version = '2.0';
            n.queue = [];
            t = b.createElement(e);
            t.async = !0;
            t.src = v;
            s = b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t, s)
        }(window,
            document, 'script', '../../connect.facebook.net/en_US/fbevents.js');
    </script>
    <!-- End Meta Pixel Code -->
    <script type='text/javascript'>
        fbq('init', '1629094657149098', {}, {
            "agent": "wordpress-6.0.5-3.0.8"
        });
    </script>
    <script type='text/javascript'>
        fbq('track', 'PageView', []);
    </script>
    <!-- Meta Pixel Code -->
    <noscript>
        <img height="1" width="1" style="display:none" alt="fbpx"
            src="https://www.facebook.com/tr?id=1629094657149098&amp;ev=PageView&amp;noscript=1" />
    </noscript>
    <!-- End Meta Pixel Code -->
    <meta name="generator"
        content="Powered by Slider Revolution 6.3.1 - responsive, Mobile-Friendly Slider Plugin for WordPress with comfortable drag and drop interface." />
    <meta name="facebook-domain-verification" content="6rtbfqcxrlmfeiqb2u9xylpsjxownt">

    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-0CDRZY1XT1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-0CDRZY1XT1');
    </script>

    <script>
        jQuery(function($) {
            $(document).ready(function() {
                $("body ul.et_mobile_menu li.menu-item-has-children, body ul.et_mobile_menu  li.page_item_has_children")
                    .append('<a href="#" class="mobile-toggle"></a>');
                $('ul.et_mobile_menu li.menu-item-has-children .mobile-toggle, ul.et_mobile_menu li.page_item_has_children .mobile-toggle')
                    .click(function(event) {
                        event.preventDefault();
                        $(this).parent('li').toggleClass('dt-open');
                        $(this).parent('li').find('ul.children').first().toggleClass('visible');
                        $(this).parent('li').find('ul.sub-menu').first().toggleClass('visible');
                    });
                iconFINAL = 'P';
                $('body ul.et_mobile_menu li.menu-item-has-children, body ul.et_mobile_menu li.page_item_has_children')
                    .attr('data-icon', iconFINAL);
                $('.mobile-toggle').on('mouseover', function() {
                    $(this).parent().addClass('is-hover');
                }).on('mouseout', function() {
                    $(this).parent().removeClass('is-hover');
                })
            });
        });
    </script>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-926876969"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'AW-926876969');
    </script>
    <link rel="icon" href="https://www.eimaths-th.com/wp-content/uploads/2019/08/cropped-footer-logo-32x32.png"
        sizes="32x32" />
    <link rel="icon" href="https://www.eimaths-th.com/wp-content/uploads/2019/08/cropped-footer-logo-192x192.png"
        sizes="192x192" />
    <link rel="apple-touch-icon"
        href="https://www.eimaths-th.com/wp-content/uploads/2019/08/cropped-footer-logo-180x180.png" />
    <meta name="msapplication-TileImage"
        content="https://eimaths.com/wp-content/uploads/2019/08/cropped-footer-logo-270x270.png" />
    <script type="text/javascript">
        function setREVStartSize(e) {
            //window.requestAnimationFrame(function() {				 
            window.RSIW = window.RSIW === undefined ? window.innerWidth : window.RSIW;
            window.RSIH = window.RSIH === undefined ? window.innerHeight : window.RSIH;
            try {
                var pw = document.getElementById(e.c).parentNode.offsetWidth,
                    newh;
                pw = pw === 0 || isNaN(pw) ? window.RSIW : pw;
                e.tabw = e.tabw === undefined ? 0 : parseInt(e.tabw);
                e.thumbw = e.thumbw === undefined ? 0 : parseInt(e.thumbw);
                e.tabh = e.tabh === undefined ? 0 : parseInt(e.tabh);
                e.thumbh = e.thumbh === undefined ? 0 : parseInt(e.thumbh);
                e.tabhide = e.tabhide === undefined ? 0 : parseInt(e.tabhide);
                e.thumbhide = e.thumbhide === undefined ? 0 : parseInt(e.thumbhide);
                e.mh = e.mh === undefined || e.mh == "" || e.mh === "auto" ? 0 : parseInt(e.mh, 0);
                if (e.layout === "fullscreen" || e.l === "fullscreen")
                    newh = Math.max(e.mh, window.RSIH);
                else {
                    e.gw = Array.isArray(e.gw) ? e.gw : [e.gw];
                    for (var i in e.rl)
                        if (e.gw[i] === undefined || e.gw[i] === 0) e.gw[i] = e.gw[i - 1];
                    e.gh = e.el === undefined || e.el === "" || (Array.isArray(e.el) && e.el.length == 0) ? e.gh : e.el;
                    e.gh = Array.isArray(e.gh) ? e.gh : [e.gh];
                    for (var i in e.rl)
                        if (e.gh[i] === undefined || e.gh[i] === 0) e.gh[i] = e.gh[i - 1];

                    var nl = new Array(e.rl.length),
                        ix = 0,
                        sl;
                    e.tabw = e.tabhide >= pw ? 0 : e.tabw;
                    e.thumbw = e.thumbhide >= pw ? 0 : e.thumbw;
                    e.tabh = e.tabhide >= pw ? 0 : e.tabh;
                    e.thumbh = e.thumbhide >= pw ? 0 : e.thumbh;
                    for (var i in e.rl) nl[i] = e.rl[i] < window.RSIW ? 0 : e.rl[i];
                    sl = nl[0];
                    for (var i in nl)
                        if (sl > nl[i] && nl[i] > 0) {
                            sl = nl[i];
                            ix = i;
                        }
                    var m = pw > (e.gw[ix] + e.tabw + e.thumbw) ? 1 : (pw - (e.tabw + e.thumbw)) / (e.gw[ix]);
                    newh = (e.gh[ix] * m) + (e.tabh + e.thumbh);
                }
                if (window.rs_init_css === undefined) window.rs_init_css = document.head.appendChild(document.createElement(
                    "style"));
                document.getElementById(e.c).height = newh + "px";
                window.rs_init_css.innerHTML += "#" + e.c + "_wrapper { height: " + newh + "px }";
            } catch (e) {
                console.log("Failure at Presize of Slider:" + e)
            }
            //});
        };
    </script>
    <link rel="stylesheet" id="et-core-unified-tb-241508-260-cached-inline-styles"
        href="https://www.eimaths-th.com/wp-content/et-cache/260/et-core-unified-tb-241508-260.min21c3.css?ver=1686570216" />
    <link rel="stylesheet" id="et-core-unified-260-cached-inline-styles"
        href="https://www.eimaths-th.com/wp-content/et-cache/260/et-core-unified-260.min8ea9.css?ver=1686570215" />
    <link rel="stylesheet" id="et-core-unified-tb-241508-deferred-260-cached-inline-styles"
        href="https://www.eimaths-th.com/wp-content/et-cache/260/et-core-unified-tb-241508-deferred-260.min21c3.css?ver=1686570216" />


    {{-- @include('includes.head') --}}
</head>

<body
    class="page-template-default page page-id-260 theme-Divi et-tb-has-template et-tb-has-footer woocommerce-no-js ehf-template-Divi ehf-stylesheet-Divi et_pb_button_helper_class et_fullwidth_nav et_fullwidth_secondary_nav et_non_fixed_nav et_show_nav et_secondary_nav_enabled et_secondary_nav_two_panels et_primary_nav_dropdown_animation_fade et_secondary_nav_dropdown_animation_fade et_header_style_left et_cover_background et_pb_gutter windows et_pb_gutters3 et_pb_pagebuilder_layout et_no_sidebar et_divi_theme et-db elementor-default elementor-kit-977">

    {{-- @include('includes.page-js') --}}


    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 0 0" width="0" height="0" focusable="false"
        role="none" style="visibility: hidden; position: absolute; left: -9999px; overflow: hidden;">
        <defs>
            <filter id="wp-duotone-dark-grayscale">
                <feColorMatrix color-interpolation-filters="sRGB" type="matrix"
                    values=" .299 .587 .114 0 0 .299 .587 .114 0 0 .299 .587 .114 0 0 .299 .587 .114 0 0 " />
                <feComponentTransfer color-interpolation-filters="sRGB">
                    <feFuncR type="table" tableValues="0 0.49803921568627" />
                    <feFuncG type="table" tableValues="0 0.49803921568627" />
                    <feFuncB type="table" tableValues="0 0.49803921568627" />
                    <feFuncA type="table" tableValues="1 1" />
                </feComponentTransfer>
                <feComposite in2="SourceGraphic" operator="in" />
            </filter>
        </defs>
    </svg><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 0 0" width="0" height="0" focusable="false"
        role="none" style="visibility: hidden; position: absolute; left: -9999px; overflow: hidden;">
        <defs>
            <filter id="wp-duotone-grayscale">
                <feColorMatrix color-interpolation-filters="sRGB" type="matrix"
                    values=" .299 .587 .114 0 0 .299 .587 .114 0 0 .299 .587 .114 0 0 .299 .587 .114 0 0 " />
                <feComponentTransfer color-interpolation-filters="sRGB">
                    <feFuncR type="table" tableValues="0 1" />
                    <feFuncG type="table" tableValues="0 1" />
                    <feFuncB type="table" tableValues="0 1" />
                    <feFuncA type="table" tableValues="1 1" />
                </feComponentTransfer>
                <feComposite in2="SourceGraphic" operator="in" />
            </filter>
        </defs>
    </svg><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 0 0" width="0" height="0" focusable="false"
        role="none" style="visibility: hidden; position: absolute; left: -9999px; overflow: hidden;">
        <defs>
            <filter id="wp-duotone-purple-yellow">
                <feColorMatrix color-interpolation-filters="sRGB" type="matrix"
                    values=" .299 .587 .114 0 0 .299 .587 .114 0 0 .299 .587 .114 0 0 .299 .587 .114 0 0 " />
                <feComponentTransfer color-interpolation-filters="sRGB">
                    <feFuncR type="table" tableValues="0.54901960784314 0.98823529411765" />
                    <feFuncG type="table" tableValues="0 1" />
                    <feFuncB type="table" tableValues="0.71764705882353 0.25490196078431" />
                    <feFuncA type="table" tableValues="1 1" />
                </feComponentTransfer>
                <feComposite in2="SourceGraphic" operator="in" />
            </filter>
        </defs>
    </svg><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 0 0" width="0" height="0" focusable="false"
        role="none" style="visibility: hidden; position: absolute; left: -9999px; overflow: hidden;">
        <defs>
            <filter id="wp-duotone-blue-red">
                <feColorMatrix color-interpolation-filters="sRGB" type="matrix"
                    values=" .299 .587 .114 0 0 .299 .587 .114 0 0 .299 .587 .114 0 0 .299 .587 .114 0 0 " />
                <feComponentTransfer color-interpolation-filters="sRGB">
                    <feFuncR type="table" tableValues="0 1" />
                    <feFuncG type="table" tableValues="0 0.27843137254902" />
                    <feFuncB type="table" tableValues="0.5921568627451 0.27843137254902" />
                    <feFuncA type="table" tableValues="1 1" />
                </feComponentTransfer>
                <feComposite in2="SourceGraphic" operator="in" />
            </filter>
        </defs>
    </svg><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 0 0" width="0" height="0" focusable="false"
        role="none" style="visibility: hidden; position: absolute; left: -9999px; overflow: hidden;">
        <defs>
            <filter id="wp-duotone-midnight">
                <feColorMatrix color-interpolation-filters="sRGB" type="matrix"
                    values=" .299 .587 .114 0 0 .299 .587 .114 0 0 .299 .587 .114 0 0 .299 .587 .114 0 0 " />
                <feComponentTransfer color-interpolation-filters="sRGB">
                    <feFuncR type="table" tableValues="0 0" />
                    <feFuncG type="table" tableValues="0 0.64705882352941" />
                    <feFuncB type="table" tableValues="0 1" />
                    <feFuncA type="table" tableValues="1 1" />
                </feComponentTransfer>
                <feComposite in2="SourceGraphic" operator="in" />
            </filter>
        </defs>
    </svg><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 0 0" width="0" height="0" focusable="false"
        role="none" style="visibility: hidden; position: absolute; left: -9999px; overflow: hidden;">
        <defs>
            <filter id="wp-duotone-magenta-yellow">
                <feColorMatrix color-interpolation-filters="sRGB" type="matrix"
                    values=" .299 .587 .114 0 0 .299 .587 .114 0 0 .299 .587 .114 0 0 .299 .587 .114 0 0 " />
                <feComponentTransfer color-interpolation-filters="sRGB">
                    <feFuncR type="table" tableValues="0.78039215686275 1" />
                    <feFuncG type="table" tableValues="0 0.94901960784314" />
                    <feFuncB type="table" tableValues="0.35294117647059 0.47058823529412" />
                    <feFuncA type="table" tableValues="1 1" />
                </feComponentTransfer>
                <feComposite in2="SourceGraphic" operator="in" />
            </filter>
        </defs>
    </svg><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 0 0" width="0" height="0" focusable="false"
        role="none" style="visibility: hidden; position: absolute; left: -9999px; overflow: hidden;">
        <defs>
            <filter id="wp-duotone-purple-green">
                <feColorMatrix color-interpolation-filters="sRGB" type="matrix"
                    values=" .299 .587 .114 0 0 .299 .587 .114 0 0 .299 .587 .114 0 0 .299 .587 .114 0 0 " />
                <feComponentTransfer color-interpolation-filters="sRGB">
                    <feFuncR type="table" tableValues="0.65098039215686 0.40392156862745" />
                    <feFuncG type="table" tableValues="0 1" />
                    <feFuncB type="table" tableValues="0.44705882352941 0.4" />
                    <feFuncA type="table" tableValues="1 1" />
                </feComponentTransfer>
                <feComposite in2="SourceGraphic" operator="in" />
            </filter>
        </defs>
    </svg><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 0 0" width="0" height="0" focusable="false"
        role="none" style="visibility: hidden; position: absolute; left: -9999px; overflow: hidden;">
        <defs>
            <filter id="wp-duotone-blue-orange">
                <feColorMatrix color-interpolation-filters="sRGB" type="matrix"
                    values=" .299 .587 .114 0 0 .299 .587 .114 0 0 .299 .587 .114 0 0 .299 .587 .114 0 0 " />
                <feComponentTransfer color-interpolation-filters="sRGB">
                    <feFuncR type="table" tableValues="0.098039215686275 1" />
                    <feFuncG type="table" tableValues="0 0.66274509803922" />
                    <feFuncB type="table" tableValues="0.84705882352941 0.41960784313725" />
                    <feFuncA type="table" tableValues="1 1" />
                </feComponentTransfer>
                <feComposite in2="SourceGraphic" operator="in" />
            </filter>
        </defs>
    </svg>
    <script type='text/javascript'>
        function updateConfig() {
            var eventsFilter = "Microdata,SubscribedButtonClick";
            var eventsFilterList = eventsFilter.split(',');
            fbq.instance.pluginConfig.set("1629094657149098", 'openbridge', {
                'endpoints': [{
                    'targetDomain': window.location.href,
                    'endpoint': window.location.href + '.open-bridge'
                }],
                'eventsFilter': {
                    'eventNames': eventsFilterList,
                    'filteringMode': 'blocklist'
                }
            });
            fbq.instance.configLoaded("1629094657149098");
        }

        window.onload = function() {
            var s = document.createElement('script');
            s.setAttribute('src',
                "https://www.eimaths-th.com/wp-content/plugins/official-facebook-pixel/js/openbridge_plugin.js");
            s.setAttribute('onload', 'updateConfig()');
            document.body.appendChild(s);
        }
    </script>
    <div id="page-container">
        <div id="et-boc" class="et-boc">
            {{-- @include('includes.header-ei') --}}

            <!-- #main-header -->
            <div id="et-main-area">
                <div id="main-content">
                    <article id="post-260" class="post-260 page type-page status-publish hentry">
                        <div class="entry-content">
                            <div class="et-l et-l--post">
                                <div class="et_builder_inner_content et_pb_gutters3">
                                    <div class="et_pb_section et_pb_section_0 et_section_regular">
                                        <div class="et_pb_row et_pb_row_0">
                                            <div
                                                class="et_pb_column et_pb_column_4_4 et_pb_column_0  et_pb_css_mix_blend_mode_passthrough et-last-child">

                                                <div class="et_pb_module et_pb_image et_pb_image_0">
                                                    <span class="et_pb_image_wrap "><img width="480"
                                                            height="480"
                                                            src="https://www.eimaths-th.com/wp-content/uploads/2021/12/Blank.jpg"
                                                            alt="" title="Blank"
                                                            class="wp-image-236608" /></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="et_pb_row et_pb_row_1">
                                            <div
                                                class="et_pb_column et_pb_column_4_4 et_pb_column_1  et_pb_css_mix_blend_mode_passthrough et-last-child">
                                                <div
                                                    class="et_pb_module et_pb_text et_pb_text_0  et_pb_text_align_center et_pb_bg_layout_light">
                                                    <div class="et_pb_text_inner">
                                                        <h1>
                                                            eiMaths

                                                        </h1>
                                                        <h1>
                                                            Form
                                                        </h1>
                                                        <h2>



                                                        </h2>
                                                    </div>
                                                </div>
                                                {{-- <div
                                                    class="et_pb_button_module_wrapper et_pb_button_0_wrapper et_pb_button_alignment_center et_pb_module ">
                                                    <a class="et_pb_button et_pb_button_0 et_pb_bg_layout_light"
                                                        href="#form-section" data-icon="&#x24;">JOIN US TODAY</a>
                                                </div> --}}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="et_pb_section et_pb_section_1 et_section_regular">

                                        <div class="et_pb_row et_pb_row_2">
                                            <div
                                                class="et_pb_column et_pb_column_4_4 et_pb_column_2  et_pb_css_mix_blend_mode_passthrough et-last-child">

                                                <div class="et_pb_module et_pb_image et_pb_image_1">

                                                    <span class="et_pb_image_wrap "><img loading="lazy"
                                                            width="1920" height="576"
                                                            src="https://www.eimaths-th.com/wp-content/uploads/2022/07/4.jpg"
                                                            alt="" title="4"
                                                            class="wp-image-239982" /></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="et_pb_row et_pb_row_3">
                                            {{-- <div class="et_pb_column et_pb_column_4_4 et_pb_column_3  et_pb_css_mix_blend_mode_passthrough et-last-child"> --}}

                                            <div
                                                class="et_pb_module et_pb_text et_pb_text_1  et_pb_text_align_left et_pb_bg_layout_light">

                                                <div class="et_pb_text_inner">
                                                    <h1>
                                                        eiMaths Form
                                                    </h1>
                                                    <h1>

                                                    </h1>
                                                    <h2>


                                                    </h2>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="et_pb_section et_pb_section_2 et_pb_with_background et_section_regular">

                                        <div class="et_pb_row et_pb_row_4">
                                            <div
                                                class="et_pb_column et_pb_column_3_5 et_pb_column_4  et_pb_css_mix_blend_mode_passthrough">

                                                <div id="et_pb_contact_form_0"
                                                    class="et_pb_module et_pb_contact_form_0 et_pb_contact_form_container clearfix"
                                                    data-form_unique_num="0"
                                                    data-form_unique_id="d2497ece-0381-41d0-baef-98a2b5aceb12">

                                                    <h1 class="et_pb_contact_main_title">

                                                        eiMaths Form

                                                    </h1>
                                                    <div id="error-container" class="et-pb-contact-message"
                                                        style="display: none;"></div>
                                                    @if ($errors->any())
                                                        <div class="alert alert-danger">
                                                            <ul>
                                                                @foreach ($errors->all() as $error)
                                                                    <li>{{ $error }}</li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                    <p id="response-container"> </p>
                                                    <div class="et_pb_contact">
                                                        <form class="et_pb_contact_form clearfix" method="POST"
                                                            id="form_contact" action="{{ route('ei-form.store') }}"
                                                            enctype="multipart/form-data">
                                                            @csrf

                                                            <p class="et_pb_contact_field et_pb_contact_field_2 et_pb_contact_field_half"
                                                                data-id="phone" data-type="input">

                                                                <label for="et_pb_contact_phone_0"
                                                                    class="et_pb_contact_form_label">First Name
                                                                    Student</label>
                                                                <input type="text" id="first_name_student"
                                                                    class="input" value=""
                                                                    name="first_name_student"
                                                                    data-required_mark="required"
                                                                    data-field_type="input" data-original_id="phone"
                                                                    placeholder="First Name Student" required>
                                                            </p>

                                                            <p class="et_pb_contact_field et_pb_contact_field_1 et_pb_contact_field_half et_pb_contact_field_last"
                                                                data-id="email" data-type="email">

                                                                <label for="et_pb_contact_email_0"
                                                                    class="et_pb_contact_form_label">Last Name
                                                                    Student</label>
                                                                <input type="text" id="last_name_student"
                                                                    class="input" value=""
                                                                    name="last_name_student"
                                                                    data-required_mark="required"
                                                                    data-field_type="input"
                                                                    data-original_id="last_name_student"
                                                                    placeholder="Last Name Student" required>
                                                            </p>
                                                            <p class="et_pb_contact_field et_pb_contact_field_2 et_pb_contact_field_half"
                                                                data-id="phone" data-type="input">

                                                                <label for="et_pb_contact_phone_0"
                                                                    class="et_pb_contact_form_label">Nick Name
                                                                    Student</label>
                                                                <input type="text" id="nick_name_student"
                                                                    class="input" value=""
                                                                    name="nick_name_student"
                                                                    data-required_mark="required"
                                                                    data-field_type="input"
                                                                    data-original_id="nick_name_student"
                                                                    placeholder="Nick Name Student" required>
                                                            </p>
                                                            <p class="et_pb_contact_field et_pb_contact_field_1 et_pb_contact_field_half et_pb_contact_field_last"
                                                                data-id="topic" data-type="select">

                                                                <label for="et_pb_contact_topic_0"
                                                                    class="et_pb_contact_form_label"> Category </label>
                                                                <select id="category"
                                                                    class="et_pb_contact_select input" name="category"
                                                                    data-required_mark="not_required"
                                                                    data-field_type="select"
                                                                    data-original_id="category">
                                                                    <option value="">Select Category</option>
                                                                    <option value="1">Category 1</option>
                                                                    <option value="2">Category 2</option>
                                                                    <option value="3">Category 3</option>
                                                                    <option value="4">Category 4</option>
                                                                </select>
                                                            </p>
                                                            <p class="et_pb_contact_field et_pb_contact_field_0 et_pb_contact_field_half"
                                                                data-id="fname" data-type="input">

                                                                <label for="et_pb_contact_fname_0"
                                                                    class="et_pb_contact_form_label">Firs Name
                                                                    Parent</label>
                                                                <input type="text" id="fname" class="input"
                                                                    value="" name="fname" required
                                                                    data-field_type="input" data-original_id="fname"
                                                                    placeholder="Firs Name Parent">


                                                            <p class="et_pb_contact_field et_pb_contact_field_1 et_pb_contact_field_half et_pb_contact_field_last"
                                                                data-id="lname" data-type="input">

                                                                <label for="et_pb_contact_lname_0"
                                                                    class="et_pb_contact_form_label">Last Name
                                                                    Parent</label>
                                                                <input type="text" id="lname" class="input"
                                                                    value="" name="lname"
                                                                    data-required_mark="required"
                                                                    data-field_type="input" data-original_id="lname"
                                                                    placeholder="Last Name Parent">
                                                            </p>
                                                            <p class="et_pb_contact_field et_pb_contact_field_2 et_pb_contact_field_half"
                                                                data-id="phone" data-type="input">

                                                                <label for="et_pb_contact_phone_0"
                                                                    class="et_pb_contact_form_label">Phone
                                                                    Number</label>
                                                                <input type="text" id="telp" class="input"
                                                                    value="" name="telp"
                                                                    data-required_mark="required"
                                                                    data-field_type="input" data-original_id="phone"
                                                                    placeholder="Phone Number">
                                                            </p>

                                                            <p class="et_pb_contact_field et_pb_contact_field_1 et_pb_contact_field_half et_pb_contact_field_last"
                                                                data-id="email" data-type="email">

                                                                <label for="et_pb_contact_email_0"
                                                                    class="et_pb_contact_form_label">Email
                                                                    Address</label>
                                                                <input type="text" id="email" class="input"
                                                                    value="" name="email"
                                                                    data-required_mark="required"
                                                                    data-field_type="email" data-original_id="email"
                                                                    placeholder="Email Address">
                                                            </p>



                                                            <p class="et_pb_contact_field et_pb_contact_field_6 et_pb_contact_field_last"
                                                                data-id="message" data-type="text">

                                                                <label for="et_pb_contact_message_0"
                                                                    class="et_pb_contact_form_label">Address</label>
                                                                <textarea name="message" id="message" class="et_pb_contact_message input" data-required_mark="not_required"
                                                                    data-field_type="text" data-original_id="message" placeholder="Address" required></textarea>
                                                            </p>
                                                            <div class="et_contact_bottom_container">

                                                                <button type="button" name="submit_button"
                                                                    id="SubmitCreateForm"
                                                                    class="et_pb_contact_submit et_pb_button"
                                                                    data-icon="&#xe076;">Submit enquiry</button>
                                                            </div>
                                                        </form>
                                                        <script>
                                                            // $('#SubmitCreateForm').click(function(e) {
                                                            //     e.preventDefault();
                                                            //     var fname = $('#fname').val();
                                                            //     var lname = $('#lname').val();
                                                            //     var telp = $('#telp').val();
                                                            //     var email = $('#email').val();
                                                            //     var category = $('#category').val();
                                                            //     var first_name_student = $('#first_name_student').val();
                                                            //     var last_name_student = $('#last_name_student').val();
                                                            //     var nick_name_student = $('#nick_name_student').val();
                                                            //     var message = $('#message').val();
                                                            //     var token = $('meta[name="csrf-token"]').attr('content');

                                                            //     // how to for each alert all input

                                                            //     if (telp == '' && email == '') {
                                                            //         $('#error-container').show();
                                                            //         $('#error-container').html('<li>Please enter telp or email </li>');
                                                            //         alert('Please enter telp or email');
                                                            //         return;
                                                            //     }
                                                            //     if (fname == '') {
                                                            //         $('#error-container').show();
                                                            //         $('#error-container').html('<li>Please enter first name parent </li>');
                                                            //         alert('Please enter first name parent');
                                                            //         return;
                                                            //     }
                                                            //     if (lname == '') {
                                                            //         $('#error-container').show();
                                                            //         $('#error-container').html('<li>Please enter last name parent </li>');
                                                            //         alert('Please enter last name parent');
                                                            //         return;
                                                            //     }
                                                            //     if (first_name_student == '') {
                                                            //         $('#error-container').show();
                                                            //         $('#error-container').html('<li>Please enter first name student </li>');
                                                            //         alert('Please enter first name student');
                                                            //         return;
                                                            //     }
                                                            //     if (last_name_student == '') {
                                                            //         $('#error-container').show();
                                                            //         $('#error-container').html('<li>Please enter last name student </li>');
                                                            //         alert('Please enter last name student');
                                                            //         return;
                                                            //     }
                                                            //     if (nick_name_student == '') {
                                                            //         $('#error-container').show();
                                                            //         $('#error-container').html('<li>Please enter nick name student </li>');
                                                            //         alert('Please enter nick name student');
                                                            //         return;
                                                            //     }
                                                            //     if (message == '') {
                                                            //         $('#error-container').show();
                                                            //         $('#error-container').html('<li>Please enter address </li>');
                                                            //         alert('Please enter address');
                                                            //         return;
                                                            //     }

                                                            //     $.ajax({
                                                            //             url: "{{ route('ei-form.store') }}",
                                                            //             type: "POST",
                                                            //             method: "POST",

                                                            //             data: {
                                                            //                 first_name_parent: fname,
                                                            //                 last_name_parent: lname,
                                                            //                 telp_parent: telp,
                                                            //                 email_parent: email,
                                                            //                 category: category,
                                                            //                 first_name_student: first_name_student,
                                                            //                 last_name_student: last_name_student,
                                                            //                 nick_name_student: nick_name_student,
                                                            //                 address: message,
                                                            //                 _token: token,
                                                            //             },
                                                            //             success: function(result) {

                                                            //                 var pdpa = $('#form_contact').hide();
                                                            //                 var pdpa = $('#error-container').hide();
                                                            //                 // $('#msg').text(result.msg);
                                                            //                 // $('#msg').text(result.msg);
                                                            //                 // $('#msg2').text(result.msg2);
                                                            //                 $('#response-container').html(result.html);


                                                            //             },
                                                            //             error: function(xhr) {
                                                            //                 // Handle errors here

                                                            //                 // Parse the JSON response (if the server returns JSON error messages)
                                                            //                 var errorResponse;
                                                            //                 try {
                                                            //                     errorResponse = JSON.parse(xhr.responseText);
                                                            //                 } catch (e) {
                                                            //                     // If the response is not JSON, handle the error appropriately (optional)
                                                            //                     console.error('Error parsing JSON response:', e);
                                                            //                     return; // Exit the error handling function
                                                            //                 }

                                                            //                 if (errorResponse && errorResponse.errors) {
                                                            //                     // Display the errors in the error container
                                                            //                     var errorContainer = $('#error-container');
                                                            //                     errorContainer.empty();
                                                            //                     errorContainer.show();

                                                            //                     // Append each error message to the container
                                                            //                     $.each(errorResponse.errors, function(field, messages) {
                                                            //                         $.each(messages, function(index, message) {
                                                            //                             errorContainer.append('<li>' + message + '</li>');
                                                            //                         });
                                                            //                     });
                                                            //                 } else {
                                                            //                     // Handle the case where there are no errors but other error responses (optional)
                                                            //                     console.error('Unexpected error response:', errorResponse);
                                                            //                 }
                                                            //             }

                                                            //         });


                                                            // });

                                                            $('#SubmitCreateForm').click(function(e) {
                                                                e.preventDefault();

                                                                var fields = [{
                                                                        id: 'fname',
                                                                        label: 'first name parent'
                                                                    },
                                                                    {
                                                                        id: 'lname',
                                                                        label: 'last name parent'
                                                                    },
                                                                    {
                                                                        id: 'first_name_student',
                                                                        label: 'first name student'
                                                                    },
                                                                    {
                                                                        id: 'last_name_student',
                                                                        label: 'last name student'
                                                                    },
                                                                    {
                                                                        id: 'nick_name_student',
                                                                        label: 'nick name student'
                                                                    },
                                                                    {
                                                                        id: 'message',
                                                                        label: 'address'
                                                                    }
                                                                ];

                                                                var telp = $('#telp').val();
                                                                var email = $('#email').val();
                                                                var token = $('meta[name="csrf-token"]').attr('content');
                                                                var category = $('#category').val();

                                                                var errorMessages = [];

                                                                // Check if either telp or email is provided
                                                                if (telp === '' && email === '') {
                                                                    errorMessages.push('Please enter telp or email');
                                                                }

                                                                // Loop through required fields
                                                                fields.forEach(function(field) {
                                                                    var value = $('#' + field.id).val();
                                                                    if (value === '') {
                                                                        errorMessages.push('Please enter ' + field.label);
                                                                    }
                                                                });

                                                                // If there are errors, show and stop
                                                                if (errorMessages.length > 0) {
                                                                    var errorContainer = $('#error-container');
                                                                    errorContainer.empty().show();
                                                                    errorMessages.forEach(function(msg) {
                                                                        errorContainer.append('<li>' + msg + '</li>');
                                                                    });
                                                                    alert(errorMessages.join('\n'));
                                                                    return;
                                                                }

                                                                // Prepare data
                                                                var formData = {
                                                                    first_name_parent: $('#fname').val(),
                                                                    last_name_parent: $('#lname').val(),
                                                                    telp_parent: telp,
                                                                    email_parent: email,
                                                                    category: category,
                                                                    first_name_student: $('#first_name_student').val(),
                                                                    last_name_student: $('#last_name_student').val(),
                                                                    nick_name_student: $('#nick_name_student').val(),
                                                                    address: $('#message').val(),
                                                                    _token: token,
                                                                };

                                                                // Submit form via AJAX
                                                                $.ajax({
                                                                    url: "{{ route('ei-form.store') }}",
                                                                    type: "POST",
                                                                    data: formData,
                                                                    success: function(result) {
                                                                        $('#form_contact').hide();
                                                                        $('#error-container').hide();
                                                                        $('#response-container').html(result.html);
                                                                    },
                                                                    error: function(xhr) {
                                                                        var errorResponse;
                                                                        try {
                                                                            errorResponse = JSON.parse(xhr.responseText);
                                                                        } catch (e) {
                                                                            console.error('Error parsing JSON response:', e);
                                                                            return;
                                                                        }

                                                                        if (errorResponse && errorResponse.errors) {
                                                                            var errorContainer = $('#error-container');
                                                                            errorContainer.empty().show();
                                                                            $.each(errorResponse.errors, function(field, messages) {
                                                                                $.each(messages, function(index, message) {
                                                                                    errorContainer.append('<li>' + message + '</li>');
                                                                                });
                                                                            });
                                                                        } else {
                                                                            console.error('Unexpected error response:', errorResponse);
                                                                        }
                                                                    }
                                                                });
                                                            });
                                                        </script>
                                                    </div>
                                                </div>

                                            </div>
                                            <div
                                                class="et_pb_column et_pb_column_2_5 et_pb_column_5  et_pb_css_mix_blend_mode_passthrough et-last-child">




                                                <div
                                                    class="et_pb_module et_pb_text et_pb_text_3  et_pb_text_align_left et_pb_bg_layout_light">




                                                    <div class="et_pb_text_inner">

                                                    </div>
                                                </div>
                                                <div
                                                    class="et_pb_module et_pb_blurb et_pb_blurb_0 et_animated et_clickable  et_pb_text_align_left  et_pb_blurb_position_left et_pb_bg_layout_light">

                                                </div>
                                                <div
                                                    class="et_pb_module et_pb_blurb et_pb_blurb_1 et_animated et_clickable  et_pb_text_align_left  et_pb_blurb_position_left et_pb_bg_layout_light">


                                                </div>
                                                <div
                                                    class="et_pb_module et_pb_blurb et_pb_blurb_2 et_animated et_clickable  et_pb_text_align_left  et_pb_blurb_position_left et_pb_bg_layout_light">

                                                    <div class="et_pb_blurb_content">

                                                    </div>
                                                </div>
                                                <div class="et_pb_module et_pb_image et_pb_image_2 contact-image">

                                                    <span class="et_pb_image_wrap "><img loading="lazy"
                                                            width="1027" height="731"
                                                            src="https://www.eimaths-th.com/wp-content/uploads/2022/04/contact-image.png"
                                                            alt="" title="contact-image"
                                                            class="wp-image-238856" /></span>
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                    <div
                                        class="et_pb_section et_pb_section_3 et_pb_with_background et_section_regular">
                                        <div class="et_pb_row et_pb_row_5">
                                            <div
                                                class="et_pb_column et_pb_column_4_4 et_pb_column_6  et_pb_css_mix_blend_mode_passthrough et-last-child">
                                                <div
                                                    class="et_pb_module et_pb_code et_pb_code_0 et_animated  et_pb_text_align_center">
                                                    <div class="et_pb_code_inner">

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>

                {{-- <footer class="et-l et-l--footer">
                    <div class="et_builder_inner_content et_pb_gutters3">
                        <div class="et_pb_section et_pb_section_0_tb_footer et_section_regular">

                            <div class="et_pb_row et_pb_row_0_tb_footer">
                                <div
                                    class="et_pb_column et_pb_column_4_4 et_pb_column_0_tb_footer  et_pb_css_mix_blend_mode_passthrough et-last-child">




                                    <div class="et_pb_module et_pb_image et_pb_image_0_tb_footer">




                                        <span class="et_pb_image_wrap "><img loading="lazy" width="480"
                                                height="480" src="https://www.eimaths-th.com/wp-content/uploads/2021/12/Blank.jpg"
                                                alt="" title="Blank" class="wp-image-236608" /></span>
                                    </div>
                                </div>




                            </div>
                            <div class="et_pb_row et_pb_row_1_tb_footer">
                                <div
                                    class="et_pb_column et_pb_column_4_4 et_pb_column_1_tb_footer  et_pb_css_mix_blend_mode_passthrough et-last-child">




                                    <div
                                        class="et_pb_module et_pb_text et_pb_text_0_tb_footer  et_pb_text_align_center et_pb_bg_layout_light">

                                        <div class="et_pb_text_inner">
                                            <h1>Want to Score In Maths?</h1>
                                            <h1>Get In Touch With Us</h1>
                                            <h2>We are always at the service of our parents and children &amp; that is
                                                why eiMaths is the preferred Math specialist today.</h2>
                                        </div>
                                    </div>
                                    <div
                                        class="et_pb_button_module_wrapper et_pb_button_0_tb_footer_wrapper et_pb_button_alignment_center et_pb_module ">
                                        <a class="et_pb_button et_pb_button_0_tb_footer et_pb_bg_layout_light"
                                            href="#form-section" data-icon="&#x24;">JOIN US TODAY</a>
                                    </div>
                                </div>




                            </div>


                        </div>
                        <div class="et_pb_section et_pb_section_3_tb_footer et_pb_with_background et_section_regular">
                            <div class="et_pb_row et_pb_row_2_tb_footer">
                                <div
                                    class="et_pb_column et_pb_column_4_4 et_pb_column_2_tb_footer  et_pb_css_mix_blend_mode_passthrough et-last-child">




                                    <div
                                        class="et_pb_module et_pb_code et_pb_code_0_tb_footer et_animated  et_pb_text_align_center">




                                        <div class="et_pb_code_inner">

                                            <iframe 
                                    
                                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2303.785858471225!2d100.44691053632694!3d13.809557139065054!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x30e29b74d06dfd95%3A0x558d6296221fa8d9!2z4LmA4LiU4Lit4LiwIOC4hOC4o-C4tOC4quC4leC4seC4pSDguYDguK3guKrguJrguLUg4Lij4Liy4LiK4Lie4Lik4LiB4Lip4LmM!5e0!3m2!1sth!2sth!4v1689652731211!5m2!1sth!2sth"
                                                width="100%" height="450" style="border:0;" allowfullscreen=""
                                                loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="et_pb_section et_pb_section_5_tb_footer et_pb_with_background et_section_regular">
                            <div class="et_pb_row et_pb_row_3_tb_footer">
                                <div
                                    class="et_pb_column et_pb_column_4_4 et_pb_column_3_tb_footer  et_pb_css_mix_blend_mode_passthrough et-last-child">




                                    <div
                                        class="et_pb_module et_pb_text et_pb_text_1_tb_footer  et_pb_text_align_left et_pb_bg_layout_light">




                                        <div class="et_pb_text_inner">
                                            <p><a href="https://ilovedemellows.com/eimathsnew/"
                                                    title="Best Mathematics Enrichment Centre in Singapore">Edison
                                                    Innovative Pte Ltd</a><span> © 2022 All Rights Reserved | Design and
                                                    Developed By <a href="https://demellows.com/">De Mellows</a> | <a
                                                        href="../sitemap/index.html" title="Sitemap">Sitemap</a> | <a
                                                        href="../webmaster/index.html">Powered By 3D Omni
                                                        Commerce</a></span><span></span></p>
                                        </div>
                                    </div>
                                </div>




                            </div>


                        </div> 
                    </div>
                </footer> --}}
            </div>
        </div>
    </div>

    <div id="pum-241233" class="pum pum-overlay pum-theme-235878 pum-theme-default-theme popmake-overlay click_open"
        data-popmake="{&quot;id&quot;:241233,&quot;slug&quot;:&quot;ang-mo-kio&quot;,&quot;theme_id&quot;:235878,&quot;cookies&quot;:[],&quot;triggers&quot;:[{&quot;type&quot;:&quot;click_open&quot;,&quot;settings&quot;:{&quot;extra_selectors&quot;:&quot;#angmokio-popup&quot;}}],&quot;mobile_disabled&quot;:null,&quot;tablet_disabled&quot;:null,&quot;meta&quot;:{&quot;display&quot;:{&quot;stackable&quot;:false,&quot;overlay_disabled&quot;:false,&quot;scrollable_content&quot;:false,&quot;disable_reposition&quot;:false,&quot;size&quot;:&quot;medium&quot;,&quot;responsive_min_width&quot;:&quot;0%&quot;,&quot;responsive_min_width_unit&quot;:false,&quot;responsive_max_width&quot;:&quot;100%&quot;,&quot;responsive_max_width_unit&quot;:false,&quot;custom_width&quot;:&quot;640px&quot;,&quot;custom_width_unit&quot;:false,&quot;custom_height&quot;:&quot;380px&quot;,&quot;custom_height_unit&quot;:false,&quot;custom_height_auto&quot;:false,&quot;location&quot;:&quot;center top&quot;,&quot;position_from_trigger&quot;:false,&quot;position_top&quot;:&quot;100&quot;,&quot;position_left&quot;:&quot;0&quot;,&quot;position_bottom&quot;:&quot;0&quot;,&quot;position_right&quot;:&quot;0&quot;,&quot;position_fixed&quot;:false,&quot;animation_type&quot;:&quot;fade&quot;,&quot;animation_speed&quot;:&quot;350&quot;,&quot;animation_origin&quot;:&quot;center top&quot;,&quot;overlay_zindex&quot;:false,&quot;zindex&quot;:&quot;1999999999&quot;},&quot;close&quot;:{&quot;text&quot;:&quot;&quot;,&quot;button_delay&quot;:&quot;0&quot;,&quot;overlay_click&quot;:false,&quot;esc_press&quot;:false,&quot;f4_press&quot;:false},&quot;click_open&quot;:[]}}"
        role="dialog" aria-hidden="true">

        <div id="popmake-241233"
            class="pum-container popmake theme-235878 pum-responsive pum-responsive-medium responsive size-medium">
            <div class="pum-content popmake-content" tabindex="0">
                <div data-elementor-type="wp-post" data-elementor-id="241221" class="elementor elementor-241221">
                    <div class="elementor-inner">
                        <div class="elementor-section-wrap">
                            <section
                                class="has_eae_slider elementor-section elementor-top-section elementor-element elementor-element-f6ce6e0 elementor-section-boxed elementor-section-height-default elementor-section-height-default"
                                data-id="f6ce6e0" data-element_type="section">
                                <div class="elementor-container elementor-column-gap-default">
                                    <div class="elementor-row">
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-a422848"
                                            data-id="a422848" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-df78951 elementor-widget elementor-widget-heading"
                                                        data-id="df78951" data-element_type="widget"
                                                        data-widget_type="heading.default">
                                                        <div class="elementor-widget-container">
                                                            <h2 class="elementor-heading-title elementor-size-default">
                                                                Opening Hours</h2>
                                                        </div>
                                                    </div>
                                                    <div class="elementor-element elementor-element-f5518f1 elementor-widget elementor-widget-eae-data-table"
                                                        data-id="f5518f1" data-element_type="widget"
                                                        data-widget_type="eae-data-table.default">
                                                        <div class="elementor-widget-container">

                                                            <div class="eae-table-container">
                                                                <div class="eae-table-wrapper">
                                                                    <div class="eae-table-wrap">

                                                                        <table class="eae-table"
                                                                            data-settings="{&quot;sort&quot;:false,&quot;search&quot;:false}">

                                                                            <thead class="eae-table-head">
                                                                                <tr class="eae-table-row">

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-253a221">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Day</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-c4ba098">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Time</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>
                                                                            </thead>
                                                                            <tbody class="eae-table__body">
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2b03c3e"
                                                                                        colspan="1" rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Mon</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-fb9d87a"
                                                                                        colspan="1" rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3pm
                                                                                                    - 7 pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f2f570b"
                                                                                        colspan="1" rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Sat</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2f6ca86"
                                                                                        colspan="1" rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">9am
                                                                                                    - 1pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-7d3880a"
                                            data-id="7d3880a" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-3985916 elementor-widget elementor-widget-google_maps"
                                                        data-id="3985916" data-element_type="widget"
                                                        data-widget_type="google_maps.default">
                                                        <div class="elementor-widget-container">
                                                            <div class="elementor-custom-embed">
                                                                <iframe frameborder="0" scrolling="no"
                                                                    marginheight="0" marginwidth="0"
                                                                    src="https://maps.google.com/maps?q=712%20Ang%20Mo%20Kio%20Ave%206%20%2303-4056%20Singapore%20560712&amp;t=m&amp;z=17&amp;output=embed&amp;iwloc=near"
                                                                    title="712 Ang Mo Kio Ave 6 #03-4056 Singapore 560712"
                                                                    aria-label="712 Ang Mo Kio Ave 6 #03-4056 Singapore 560712"></iframe>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

            </div>




            <button type="button" class="pum-close popmake-close" aria-label="Close">
                CLOSE </button>

        </div>

    </div>
    <div id="pum-241137" class="pum pum-overlay pum-theme-235878 pum-theme-default-theme popmake-overlay click_open"
        data-popmake="{&quot;id&quot;:241137,&quot;slug&quot;:&quot;chicago&quot;,&quot;theme_id&quot;:235878,&quot;cookies&quot;:[],&quot;triggers&quot;:[{&quot;type&quot;:&quot;click_open&quot;,&quot;settings&quot;:{&quot;extra_selectors&quot;:&quot;#chicago_popup&quot;}}],&quot;mobile_disabled&quot;:null,&quot;tablet_disabled&quot;:null,&quot;meta&quot;:{&quot;display&quot;:{&quot;stackable&quot;:false,&quot;overlay_disabled&quot;:false,&quot;scrollable_content&quot;:false,&quot;disable_reposition&quot;:false,&quot;size&quot;:&quot;medium&quot;,&quot;responsive_min_width&quot;:&quot;0%&quot;,&quot;responsive_min_width_unit&quot;:false,&quot;responsive_max_width&quot;:&quot;100%&quot;,&quot;responsive_max_width_unit&quot;:false,&quot;custom_width&quot;:&quot;640px&quot;,&quot;custom_width_unit&quot;:false,&quot;custom_height&quot;:&quot;380px&quot;,&quot;custom_height_unit&quot;:false,&quot;custom_height_auto&quot;:false,&quot;location&quot;:&quot;center top&quot;,&quot;position_from_trigger&quot;:false,&quot;position_top&quot;:&quot;100&quot;,&quot;position_left&quot;:&quot;0&quot;,&quot;position_bottom&quot;:&quot;0&quot;,&quot;position_right&quot;:&quot;0&quot;,&quot;position_fixed&quot;:false,&quot;animation_type&quot;:&quot;fade&quot;,&quot;animation_speed&quot;:&quot;350&quot;,&quot;animation_origin&quot;:&quot;center top&quot;,&quot;overlay_zindex&quot;:false,&quot;zindex&quot;:&quot;1999999999&quot;},&quot;close&quot;:{&quot;text&quot;:&quot;&quot;,&quot;button_delay&quot;:&quot;0&quot;,&quot;overlay_click&quot;:false,&quot;esc_press&quot;:false,&quot;f4_press&quot;:false},&quot;click_open&quot;:[]}}"
        role="dialog" aria-hidden="true">

        <div id="popmake-241137"
            class="pum-container popmake theme-235878 pum-responsive pum-responsive-medium responsive size-medium">







            <div class="pum-content popmake-content" tabindex="0">
                <div data-elementor-type="wp-post" data-elementor-id="241163" class="elementor elementor-241163">
                    <div class="elementor-inner">
                        <div class="elementor-section-wrap">
                            <section
                                class="has_eae_slider elementor-section elementor-top-section elementor-element elementor-element-3d0565c7 elementor-section-boxed elementor-section-height-default elementor-section-height-default"
                                data-id="3d0565c7" data-element_type="section">
                                <div class="elementor-container elementor-column-gap-default">
                                    <div class="elementor-row">
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-636c9b8b"
                                            data-id="636c9b8b" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-305e9eae elementor-widget elementor-widget-heading"
                                                        data-id="305e9eae" data-element_type="widget"
                                                        data-widget_type="heading.default">
                                                        <div class="elementor-widget-container">
                                                            <h2 class="elementor-heading-title elementor-size-default">
                                                                Opening Hours</h2>
                                                        </div>
                                                    </div>
                                                    <div class="elementor-element elementor-element-09e74b2 elementor-widget elementor-widget-eae-data-table"
                                                        data-id="09e74b2" data-element_type="widget"
                                                        data-widget_type="eae-data-table.default">
                                                        <div class="elementor-widget-container">

                                                            <div class="eae-table-container">
                                                                <div class="eae-table-wrapper">
                                                                    <div class="eae-table-wrap">

                                                                        <table class="eae-table"
                                                                            data-settings="{&quot;sort&quot;:false,&quot;search&quot;:false}">

                                                                            <thead class="eae-table-head">
                                                                                <tr class="eae-table-row">

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-253a221">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Day</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-c4ba098">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Time</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>
                                                                            </thead>
                                                                            <tbody class="eae-table__body">
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2b03c3e"
                                                                                        colspan="1" rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Mon</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-fb9d87a"
                                                                                        colspan="1" rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3pm
                                                                                                    - 6pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f2f570b"
                                                                                        colspan="1" rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Tue</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2f6ca86"
                                                                                        colspan="1" rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3pm
                                                                                                    - 6pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-d319990"
                                                                                        colspan="1" rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Wed</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-c6f9ac6"
                                                                                        colspan="1" rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3pm
                                                                                                    - 6pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-edab4c5"
                                                                                        colspan="1" rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Thu</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-712b8ec"
                                                                                        colspan="1" rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3pm
                                                                                                    - 6pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-42ed637"
                                                                                        colspan="1" rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Fri</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-b7746cf"
                                                                                        colspan="1" rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3pm
                                                                                                    - 6pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-766eee0"
                                                                                        colspan="1" rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Sat</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-0c8eef7"
                                                                                        colspan="1" rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">1pm
                                                                                                    - 5pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-50450dc5"
                                            data-id="50450dc5" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-3f3b767e elementor-widget elementor-widget-google_maps"
                                                        data-id="3f3b767e" data-element_type="widget"
                                                        data-widget_type="google_maps.default">
                                                        <div class="elementor-widget-container">
                                                            <div class="elementor-custom-embed">
                                                                <iframe frameborder="0" scrolling="no"
                                                                    marginheight="0" marginwidth="0"
                                                                    src="https://maps.google.com/maps?q=535%20W%2031st%20Street%2C%20Chicago%20IL%2060616%2C%20Chicago&amp;t=m&amp;z=17&amp;output=embed&amp;iwloc=near"
                                                                    title="535 W 31st Street, Chicago IL 60616, Chicago"
                                                                    aria-label="535 W 31st Street, Chicago IL 60616, Chicago"></iframe>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

            </div>




            <button type="button" class="pum-close popmake-close" aria-label="Close">
                CLOSE </button>

        </div>

    </div>
    <div id="pum-236210" class="pum pum-overlay pum-theme-235878 pum-theme-default-theme popmake-overlay click_open"
        data-popmake="{&quot;id&quot;:236210,&quot;slug&quot;:&quot;oklahoma&quot;,&quot;theme_id&quot;:235878,&quot;cookies&quot;:[],&quot;triggers&quot;:[{&quot;type&quot;:&quot;click_open&quot;,&quot;settings&quot;:{&quot;cookie_name&quot;:&quot;&quot;,&quot;extra_selectors&quot;:&quot;#oklahoma&quot;}}],&quot;mobile_disabled&quot;:null,&quot;tablet_disabled&quot;:null,&quot;meta&quot;:{&quot;display&quot;:{&quot;stackable&quot;:false,&quot;overlay_disabled&quot;:false,&quot;scrollable_content&quot;:false,&quot;disable_reposition&quot;:false,&quot;size&quot;:&quot;medium&quot;,&quot;responsive_min_width&quot;:&quot;0%&quot;,&quot;responsive_min_width_unit&quot;:false,&quot;responsive_max_width&quot;:&quot;100%&quot;,&quot;responsive_max_width_unit&quot;:false,&quot;custom_width&quot;:&quot;640px&quot;,&quot;custom_width_unit&quot;:false,&quot;custom_height&quot;:&quot;380px&quot;,&quot;custom_height_unit&quot;:false,&quot;custom_height_auto&quot;:false,&quot;location&quot;:&quot;center top&quot;,&quot;position_from_trigger&quot;:false,&quot;position_top&quot;:&quot;100&quot;,&quot;position_left&quot;:&quot;0&quot;,&quot;position_bottom&quot;:&quot;0&quot;,&quot;position_right&quot;:&quot;0&quot;,&quot;position_fixed&quot;:false,&quot;animation_type&quot;:&quot;fade&quot;,&quot;animation_speed&quot;:&quot;350&quot;,&quot;animation_origin&quot;:&quot;center top&quot;,&quot;overlay_zindex&quot;:false,&quot;zindex&quot;:&quot;1999999999&quot;},&quot;close&quot;:{&quot;text&quot;:&quot;&quot;,&quot;button_delay&quot;:&quot;0&quot;,&quot;overlay_click&quot;:false,&quot;esc_press&quot;:false,&quot;f4_press&quot;:false},&quot;click_open&quot;:[]}}"
        role="dialog" aria-hidden="true">

        <div id="popmake-236210"
            class="pum-container popmake theme-235878 pum-responsive pum-responsive-medium responsive size-medium">







            <div class="pum-content popmake-content" tabindex="0">
                <div data-elementor-type="wp-post" data-elementor-id="236208" class="elementor elementor-236208">
                    <div class="elementor-inner">
                        <div class="elementor-section-wrap">
                            <section
                                class="has_eae_slider elementor-section elementor-top-section elementor-element elementor-element-3d0565c7 elementor-section-boxed elementor-section-height-default elementor-section-height-default"
                                data-id="3d0565c7" data-element_type="section">
                                <div class="elementor-container elementor-column-gap-default">
                                    <div class="elementor-row">
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-636c9b8b"
                                            data-id="636c9b8b" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-305e9eae elementor-widget elementor-widget-heading"
                                                        data-id="305e9eae" data-element_type="widget"
                                                        data-widget_type="heading.default">
                                                        <div class="elementor-widget-container">
                                                            <h2 class="elementor-heading-title elementor-size-default">
                                                                Opening Hours</h2>
                                                        </div>
                                                    </div>
                                                    <div class="elementor-element elementor-element-08285a2 elementor-widget elementor-widget-eae-data-table"
                                                        data-id="08285a2" data-element_type="widget"
                                                        data-widget_type="eae-data-table.default">
                                                        <div class="elementor-widget-container">

                                                            <div class="eae-table-container">
                                                                <div class="eae-table-wrapper">
                                                                    <div class="eae-table-wrap">

                                                                        <table class="eae-table"
                                                                            data-settings="{&quot;sort&quot;:false,&quot;search&quot;:false}">

                                                                            <thead class="eae-table-head">
                                                                                <tr class="eae-table-row">

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-253a221">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Day</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-c4ba098">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Time</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>
                                                                            </thead>
                                                                            <tbody class="eae-table__body">
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2b03c3e"
                                                                                        colspan="1" rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Tue</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-fb9d87a"
                                                                                        colspan="1" rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3
                                                                                                    pm - 8 pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-a938f5a"
                                                                                        colspan="1" rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Wed</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-696b476"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3
                                                                                                    pm - 8 pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f2f570b"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Thu</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2f6ca86"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3
                                                                                                    pm - 8 pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f292d29"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Fri</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-83231a8"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3
                                                                                                    pm - 8 pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-50450dc5"
                                            data-id="50450dc5" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-3f3b767e elementor-widget elementor-widget-google_maps"
                                                        data-id="3f3b767e" data-element_type="widget"
                                                        data-widget_type="google_maps.default">
                                                        <div class="elementor-widget-container">
                                                            <div class="elementor-custom-embed">
                                                                <iframe frameborder="0" scrolling="no"
                                                                    marginheight="0" marginwidth="0"
                                                                    src="https://maps.google.com/maps?q=6120%20North%20Drexel%20Blvd%2C%20Oklahoma%20City%2C%20Oklahoma%2C%20Postal%2073112&amp;t=m&amp;z=17&amp;output=embed&amp;iwloc=near"
                                                                    title="6120 North Drexel Blvd, Oklahoma City, Oklahoma, Postal 73112"
                                                                    aria-label="6120 North Drexel Blvd, Oklahoma City, Oklahoma, Postal 73112"></iframe>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

                <p>&nbsp;</p>
            </div>




            <button type="button" class="pum-close popmake-close" aria-label="Close">
                CLOSE </button>

        </div>

    </div>
    <div id="pum-235966" class="pum pum-overlay pum-theme-235878 pum-theme-default-theme popmake-overlay click_open"
        data-popmake="{&quot;id&quot;:235966,&quot;slug&quot;:&quot;orchard&quot;,&quot;theme_id&quot;:235878,&quot;cookies&quot;:[],&quot;triggers&quot;:[{&quot;type&quot;:&quot;click_open&quot;,&quot;settings&quot;:{&quot;extra_selectors&quot;:&quot;#orchard&quot;}}],&quot;mobile_disabled&quot;:null,&quot;tablet_disabled&quot;:null,&quot;meta&quot;:{&quot;display&quot;:{&quot;stackable&quot;:false,&quot;overlay_disabled&quot;:false,&quot;scrollable_content&quot;:false,&quot;disable_reposition&quot;:false,&quot;size&quot;:&quot;medium&quot;,&quot;responsive_min_width&quot;:&quot;0%&quot;,&quot;responsive_min_width_unit&quot;:false,&quot;responsive_max_width&quot;:&quot;100%&quot;,&quot;responsive_max_width_unit&quot;:false,&quot;custom_width&quot;:&quot;640px&quot;,&quot;custom_width_unit&quot;:false,&quot;custom_height&quot;:&quot;380px&quot;,&quot;custom_height_unit&quot;:false,&quot;custom_height_auto&quot;:false,&quot;location&quot;:&quot;center top&quot;,&quot;position_from_trigger&quot;:false,&quot;position_top&quot;:&quot;100&quot;,&quot;position_left&quot;:&quot;0&quot;,&quot;position_bottom&quot;:&quot;0&quot;,&quot;position_right&quot;:&quot;0&quot;,&quot;position_fixed&quot;:false,&quot;animation_type&quot;:&quot;fade&quot;,&quot;animation_speed&quot;:&quot;350&quot;,&quot;animation_origin&quot;:&quot;center top&quot;,&quot;overlay_zindex&quot;:false,&quot;zindex&quot;:&quot;1999999999&quot;},&quot;close&quot;:{&quot;text&quot;:&quot;&quot;,&quot;button_delay&quot;:&quot;0&quot;,&quot;overlay_click&quot;:false,&quot;esc_press&quot;:false,&quot;f4_press&quot;:false},&quot;click_open&quot;:[]}}"
        role="dialog" aria-hidden="true">

        <div id="popmake-235966"
            class="pum-container popmake theme-235878 pum-responsive pum-responsive-medium responsive size-medium">







            <div class="pum-content popmake-content" tabindex="0">
                <div data-elementor-type="wp-post" data-elementor-id="235960" class="elementor elementor-235960">
                    <div class="elementor-inner">
                        <div class="elementor-section-wrap">
                            <section
                                class="has_eae_slider elementor-section elementor-top-section elementor-element elementor-element-3710e5c5 elementor-section-boxed elementor-section-height-default elementor-section-height-default"
                                data-id="3710e5c5" data-element_type="section">
                                <div class="elementor-container elementor-column-gap-default">
                                    <div class="elementor-row">
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-6037466e"
                                            data-id="6037466e" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-6d9427e6 elementor-widget elementor-widget-heading"
                                                        data-id="6d9427e6" data-element_type="widget"
                                                        data-widget_type="heading.default">
                                                        <div class="elementor-widget-container">
                                                            <h2
                                                                class="elementor-heading-title elementor-size-default">
                                                                Opening Hours</h2>
                                                        </div>
                                                    </div>
                                                    <div class="elementor-element elementor-element-745fcee elementor-widget elementor-widget-eae-data-table"
                                                        data-id="745fcee" data-element_type="widget"
                                                        data-widget_type="eae-data-table.default">
                                                        <div class="elementor-widget-container">

                                                            <div class="eae-table-container">
                                                                <div class="eae-table-wrapper">
                                                                    <div class="eae-table-wrap">

                                                                        <table class="eae-table"
                                                                            data-settings="{&quot;sort&quot;:false,&quot;search&quot;:false}">

                                                                            <thead class="eae-table-head">
                                                                                <tr class="eae-table-row">

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-253a221">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Day</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-c4ba098">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Time</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>
                                                                            </thead>
                                                                            <tbody class="eae-table__body">
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-48cc257"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Mon</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2f6ca86"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3.30pm-5.00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2b03c3e"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Tue</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-fb9d87a"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3.30pm
                                                                                                    - 5.00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f2f570b"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Thurs</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-00d4e19"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3.30pm
                                                                                                    - 5.00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f292d29"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Sat</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-83231a8"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">2.30pm
                                                                                                    - 4.00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-2e775122"
                                            data-id="2e775122" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-62c1ea73 elementor-widget elementor-widget-google_maps"
                                                        data-id="62c1ea73" data-element_type="widget"
                                                        data-widget_type="google_maps.default">
                                                        <div class="elementor-widget-container">
                                                            <div class="elementor-custom-embed">
                                                                <iframe frameborder="0" scrolling="no"
                                                                    marginheight="0" marginwidth="0"
                                                                    src="https://maps.google.com/maps?q=eiMaths%40Orchard&amp;t=m&amp;z=17&amp;output=embed&amp;iwloc=near"
                                                                    title="eiMaths@Orchard"
                                                                    aria-label="eiMaths@Orchard"></iframe>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

            </div>




            <button type="button" class="pum-close popmake-close" aria-label="Close">
                CLOSE </button>

        </div>

    </div>
    <div id="pum-239286" class="pum pum-overlay pum-theme-235878 pum-theme-default-theme popmake-overlay click_open"
        data-popmake="{&quot;id&quot;:239286,&quot;slug&quot;:&quot;sengkang&quot;,&quot;theme_id&quot;:235878,&quot;cookies&quot;:[],&quot;triggers&quot;:[{&quot;type&quot;:&quot;click_open&quot;,&quot;settings&quot;:{&quot;extra_selectors&quot;:&quot;#Sengkang&quot;}}],&quot;mobile_disabled&quot;:null,&quot;tablet_disabled&quot;:null,&quot;meta&quot;:{&quot;display&quot;:{&quot;stackable&quot;:false,&quot;overlay_disabled&quot;:false,&quot;scrollable_content&quot;:false,&quot;disable_reposition&quot;:false,&quot;size&quot;:&quot;medium&quot;,&quot;responsive_min_width&quot;:&quot;0%&quot;,&quot;responsive_min_width_unit&quot;:false,&quot;responsive_max_width&quot;:&quot;100%&quot;,&quot;responsive_max_width_unit&quot;:false,&quot;custom_width&quot;:&quot;640px&quot;,&quot;custom_width_unit&quot;:false,&quot;custom_height&quot;:&quot;380px&quot;,&quot;custom_height_unit&quot;:false,&quot;custom_height_auto&quot;:false,&quot;location&quot;:&quot;center top&quot;,&quot;position_from_trigger&quot;:false,&quot;position_top&quot;:&quot;100&quot;,&quot;position_left&quot;:&quot;0&quot;,&quot;position_bottom&quot;:&quot;0&quot;,&quot;position_right&quot;:&quot;0&quot;,&quot;position_fixed&quot;:false,&quot;animation_type&quot;:&quot;fade&quot;,&quot;animation_speed&quot;:&quot;350&quot;,&quot;animation_origin&quot;:&quot;center top&quot;,&quot;overlay_zindex&quot;:false,&quot;zindex&quot;:&quot;1999999999&quot;},&quot;close&quot;:{&quot;text&quot;:&quot;&quot;,&quot;button_delay&quot;:&quot;0&quot;,&quot;overlay_click&quot;:false,&quot;esc_press&quot;:false,&quot;f4_press&quot;:false},&quot;click_open&quot;:[]}}"
        role="dialog" aria-hidden="true">

        <div id="popmake-239286"
            class="pum-container popmake theme-235878 pum-responsive pum-responsive-medium responsive size-medium">







            <div class="pum-content popmake-content" tabindex="0">
                <div data-elementor-type="wp-post" data-elementor-id="239281" class="elementor elementor-239281">
                    <div class="elementor-inner">
                        <div class="elementor-section-wrap">
                            <section
                                class="has_eae_slider elementor-section elementor-top-section elementor-element elementor-element-3d0565c7 elementor-section-boxed elementor-section-height-default elementor-section-height-default"
                                data-id="3d0565c7" data-element_type="section">
                                <div class="elementor-container elementor-column-gap-default">
                                    <div class="elementor-row">
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-636c9b8b"
                                            data-id="636c9b8b" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-305e9eae elementor-widget elementor-widget-heading"
                                                        data-id="305e9eae" data-element_type="widget"
                                                        data-widget_type="heading.default">
                                                        <div class="elementor-widget-container">
                                                            <h2
                                                                class="elementor-heading-title elementor-size-default">
                                                                Opening Hours</h2>
                                                        </div>
                                                    </div>
                                                    <div class="elementor-element elementor-element-237c242 elementor-widget elementor-widget-eae-data-table"
                                                        data-id="237c242" data-element_type="widget"
                                                        data-widget_type="eae-data-table.default">
                                                        <div class="elementor-widget-container">

                                                            <div class="eae-table-container">
                                                                <div class="eae-table-wrapper">
                                                                    <div class="eae-table-wrap">

                                                                        <table class="eae-table"
                                                                            data-settings="{&quot;sort&quot;:false,&quot;search&quot;:false}">

                                                                            <thead class="eae-table-head">
                                                                                <tr class="eae-table-row">

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-253a221">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Day</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-c4ba098">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Time</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>
                                                                            </thead>
                                                                            <tbody class="eae-table__body">
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2b03c3e"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Tue</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-fb9d87a"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3:00pm
                                                                                                    - 7:30pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f2f570b"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Wed</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2f6ca86"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3:00pm
                                                                                                    - 7:30pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-48cc257"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Fri</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-00d4e19"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3:00pm
                                                                                                    - 7:30pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f292d29"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Sat</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-83231a8"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">9:00am
                                                                                                    - 12.00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-50450dc5"
                                            data-id="50450dc5" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-3f3b767e elementor-widget elementor-widget-google_maps"
                                                        data-id="3f3b767e" data-element_type="widget"
                                                        data-widget_type="google_maps.default">
                                                        <div class="elementor-widget-container">
                                                            <div class="elementor-custom-embed">
                                                                <iframe frameborder="0" scrolling="no"
                                                                    marginheight="0" marginwidth="0"
                                                                    src="https://maps.google.com/maps?q=21%20Sengkang%20West%20Ave%2C%20Fernvale%20Community%20Club%2C%20%2301-03%2C%20%20Singapore%20797650&amp;t=m&amp;z=17&amp;output=embed&amp;iwloc=near"
                                                                    title="21 Sengkang West Ave, Fernvale Community Club, #01-03,  Singapore 797650"
                                                                    aria-label="21 Sengkang West Ave, Fernvale Community Club, #01-03,  Singapore 797650"></iframe>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

            </div>




            <button type="button" class="pum-close popmake-close" aria-label="Close">
                CLOSE </button>

        </div>

    </div>
    <div id="pum-238620" class="pum pum-overlay pum-theme-235878 pum-theme-default-theme popmake-overlay click_open"
        data-popmake="{&quot;id&quot;:238620,&quot;slug&quot;:&quot;tampines&quot;,&quot;theme_id&quot;:235878,&quot;cookies&quot;:[],&quot;triggers&quot;:[{&quot;type&quot;:&quot;click_open&quot;,&quot;settings&quot;:{&quot;extra_selectors&quot;:&quot;#tampines&quot;}}],&quot;mobile_disabled&quot;:null,&quot;tablet_disabled&quot;:null,&quot;meta&quot;:{&quot;display&quot;:{&quot;stackable&quot;:false,&quot;overlay_disabled&quot;:false,&quot;scrollable_content&quot;:false,&quot;disable_reposition&quot;:false,&quot;size&quot;:&quot;medium&quot;,&quot;responsive_min_width&quot;:&quot;0%&quot;,&quot;responsive_min_width_unit&quot;:false,&quot;responsive_max_width&quot;:&quot;100%&quot;,&quot;responsive_max_width_unit&quot;:false,&quot;custom_width&quot;:&quot;640px&quot;,&quot;custom_width_unit&quot;:false,&quot;custom_height&quot;:&quot;380px&quot;,&quot;custom_height_unit&quot;:false,&quot;custom_height_auto&quot;:false,&quot;location&quot;:&quot;center top&quot;,&quot;position_from_trigger&quot;:false,&quot;position_top&quot;:&quot;100&quot;,&quot;position_left&quot;:&quot;0&quot;,&quot;position_bottom&quot;:&quot;0&quot;,&quot;position_right&quot;:&quot;0&quot;,&quot;position_fixed&quot;:false,&quot;animation_type&quot;:&quot;fade&quot;,&quot;animation_speed&quot;:&quot;350&quot;,&quot;animation_origin&quot;:&quot;center top&quot;,&quot;overlay_zindex&quot;:false,&quot;zindex&quot;:&quot;1999999999&quot;},&quot;close&quot;:{&quot;text&quot;:&quot;&quot;,&quot;button_delay&quot;:&quot;0&quot;,&quot;overlay_click&quot;:false,&quot;esc_press&quot;:false,&quot;f4_press&quot;:false},&quot;click_open&quot;:[]}}"
        role="dialog" aria-hidden="true">

        <div id="popmake-238620"
            class="pum-container popmake theme-235878 pum-responsive pum-responsive-medium responsive size-medium">







            <div class="pum-content popmake-content" tabindex="0">
                <div data-elementor-type="wp-post" data-elementor-id="238612" class="elementor elementor-238612">
                    <div class="elementor-inner">
                        <div class="elementor-section-wrap">
                            <section
                                class="has_eae_slider elementor-section elementor-top-section elementor-element elementor-element-3d0565c7 elementor-section-boxed elementor-section-height-default elementor-section-height-default"
                                data-id="3d0565c7" data-element_type="section">
                                <div class="elementor-container elementor-column-gap-default">
                                    <div class="elementor-row">
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-636c9b8b"
                                            data-id="636c9b8b" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-305e9eae elementor-widget elementor-widget-heading"
                                                        data-id="305e9eae" data-element_type="widget"
                                                        data-widget_type="heading.default">
                                                        <div class="elementor-widget-container">
                                                            <h2
                                                                class="elementor-heading-title elementor-size-default">
                                                                Opening Hours</h2>
                                                        </div>
                                                    </div>
                                                    <div class="elementor-element elementor-element-2fbb698 elementor-widget elementor-widget-eae-data-table"
                                                        data-id="2fbb698" data-element_type="widget"
                                                        data-widget_type="eae-data-table.default">
                                                        <div class="elementor-widget-container">

                                                            <div class="eae-table-container">
                                                                <div class="eae-table-wrapper">
                                                                    <div class="eae-table-wrap">

                                                                        <table class="eae-table"
                                                                            data-settings="{&quot;sort&quot;:false,&quot;search&quot;:false}">

                                                                            <thead class="eae-table-head">
                                                                                <tr class="eae-table-row">

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-253a221">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Day</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-c4ba098">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Time</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>
                                                                            </thead>
                                                                            <tbody class="eae-table__body">
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2b03c3e"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Tue</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-fb9d87a"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">4:00pm
                                                                                                    - 6:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f2f570b"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Thu</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2f6ca86"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">4:00pm
                                                                                                    - 6:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f292d29"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Sat</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-83231a8"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">9:30am
                                                                                                    - 11:30am</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-50450dc5"
                                            data-id="50450dc5" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-3f3b767e elementor-widget elementor-widget-google_maps"
                                                        data-id="3f3b767e" data-element_type="widget"
                                                        data-widget_type="google_maps.default">
                                                        <div class="elementor-widget-container">
                                                            <div class="elementor-custom-embed">
                                                                <iframe frameborder="0" scrolling="no"
                                                                    marginheight="0" marginwidth="0"
                                                                    src="https://maps.google.com/maps?q=eiMaths%40Tampines%20Central&amp;t=m&amp;z=17&amp;output=embed&amp;iwloc=near"
                                                                    title="eiMaths@Tampines Central"
                                                                    aria-label="eiMaths@Tampines Central"></iframe>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

            </div>




            <button type="button" class="pum-close popmake-close" aria-label="Close">
                CLOSE </button>

        </div>

    </div>
    <div id="pum-238772" class="pum pum-overlay pum-theme-235878 pum-theme-default-theme popmake-overlay click_open"
        data-popmake="{&quot;id&quot;:238772,&quot;slug&quot;:&quot;tampines-2&quot;,&quot;theme_id&quot;:235878,&quot;cookies&quot;:[],&quot;triggers&quot;:[{&quot;type&quot;:&quot;click_open&quot;,&quot;settings&quot;:{&quot;cookie_name&quot;:&quot;&quot;,&quot;extra_selectors&quot;:&quot;#tanglin&quot;}}],&quot;mobile_disabled&quot;:null,&quot;tablet_disabled&quot;:null,&quot;meta&quot;:{&quot;display&quot;:{&quot;stackable&quot;:false,&quot;overlay_disabled&quot;:false,&quot;scrollable_content&quot;:false,&quot;disable_reposition&quot;:false,&quot;size&quot;:&quot;medium&quot;,&quot;responsive_min_width&quot;:&quot;0%&quot;,&quot;responsive_min_width_unit&quot;:false,&quot;responsive_max_width&quot;:&quot;100%&quot;,&quot;responsive_max_width_unit&quot;:false,&quot;custom_width&quot;:&quot;640px&quot;,&quot;custom_width_unit&quot;:false,&quot;custom_height&quot;:&quot;380px&quot;,&quot;custom_height_unit&quot;:false,&quot;custom_height_auto&quot;:false,&quot;location&quot;:&quot;center top&quot;,&quot;position_from_trigger&quot;:false,&quot;position_top&quot;:&quot;100&quot;,&quot;position_left&quot;:&quot;0&quot;,&quot;position_bottom&quot;:&quot;0&quot;,&quot;position_right&quot;:&quot;0&quot;,&quot;position_fixed&quot;:false,&quot;animation_type&quot;:&quot;fade&quot;,&quot;animation_speed&quot;:&quot;350&quot;,&quot;animation_origin&quot;:&quot;center top&quot;,&quot;overlay_zindex&quot;:false,&quot;zindex&quot;:&quot;1999999999&quot;},&quot;close&quot;:{&quot;text&quot;:&quot;&quot;,&quot;button_delay&quot;:&quot;0&quot;,&quot;overlay_click&quot;:false,&quot;esc_press&quot;:false,&quot;f4_press&quot;:false},&quot;click_open&quot;:[]}}"
        role="dialog" aria-hidden="true">

        <div id="popmake-238772"
            class="pum-container popmake theme-235878 pum-responsive pum-responsive-medium responsive size-medium">







            <div class="pum-content popmake-content" tabindex="0">
                <div data-elementor-type="wp-post" data-elementor-id="238773" class="elementor elementor-238773">
                    <div class="elementor-inner">
                        <div class="elementor-section-wrap">
                            <section
                                class="has_eae_slider elementor-section elementor-top-section elementor-element elementor-element-3710e5c5 elementor-section-boxed elementor-section-height-default elementor-section-height-default"
                                data-id="3710e5c5" data-element_type="section">
                                <div class="elementor-container elementor-column-gap-default">
                                    <div class="elementor-row">
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-6037466e"
                                            data-id="6037466e" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-6d9427e6 elementor-widget elementor-widget-heading"
                                                        data-id="6d9427e6" data-element_type="widget"
                                                        data-widget_type="heading.default">
                                                        <div class="elementor-widget-container">
                                                            <h2
                                                                class="elementor-heading-title elementor-size-default">
                                                                Opening Hours</h2>
                                                        </div>
                                                    </div>
                                                    <div class="elementor-element elementor-element-d7515f6 elementor-widget elementor-widget-eae-data-table"
                                                        data-id="d7515f6" data-element_type="widget"
                                                        data-widget_type="eae-data-table.default">
                                                        <div class="elementor-widget-container">

                                                            <div class="eae-table-container">
                                                                <div class="eae-table-wrapper">
                                                                    <div class="eae-table-wrap">

                                                                        <table class="eae-table"
                                                                            data-settings="{&quot;sort&quot;:false,&quot;search&quot;:false}">

                                                                            <thead class="eae-table-head">
                                                                                <tr class="eae-table-row">

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-253a221">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Day</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-c4ba098">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Time</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>
                                                                            </thead>
                                                                            <tbody class="eae-table__body">
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2b03c3e"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Tue</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-fb9d87a"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">4:00pm
                                                                                                    - 6:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f2f570b"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Thu</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2f6ca86"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">4:00pm
                                                                                                    - 6:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f292d29"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Sat</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-83231a8"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">9:30am
                                                                                                    - 11:30am</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-2e775122"
                                            data-id="2e775122" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-62c1ea73 elementor-widget elementor-widget-google_maps"
                                                        data-id="62c1ea73" data-element_type="widget"
                                                        data-widget_type="google_maps.default">
                                                        <div class="elementor-widget-container">
                                                            <div class="elementor-custom-embed">
                                                                <iframe frameborder="0" scrolling="no"
                                                                    marginheight="0" marginwidth="0"
                                                                    src="https://maps.google.com/maps?q=eiMaths%40Tampines%20Central&amp;t=m&amp;z=17&amp;output=embed&amp;iwloc=near"
                                                                    title="eiMaths@Tampines Central"
                                                                    aria-label="eiMaths@Tampines Central"></iframe>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

            </div>




            <button type="button" class="pum-close popmake-close" aria-label="Close">
                CLOSE </button>

        </div>

    </div>
    <div id="pum-236192" class="pum pum-overlay pum-theme-235878 pum-theme-default-theme popmake-overlay click_open"
        data-popmake="{&quot;id&quot;:236192,&quot;slug&quot;:&quot;dubai&quot;,&quot;theme_id&quot;:235878,&quot;cookies&quot;:[],&quot;triggers&quot;:[{&quot;type&quot;:&quot;click_open&quot;,&quot;settings&quot;:{&quot;extra_selectors&quot;:&quot;#dubai&quot;}}],&quot;mobile_disabled&quot;:null,&quot;tablet_disabled&quot;:null,&quot;meta&quot;:{&quot;display&quot;:{&quot;stackable&quot;:false,&quot;overlay_disabled&quot;:false,&quot;scrollable_content&quot;:false,&quot;disable_reposition&quot;:false,&quot;size&quot;:&quot;medium&quot;,&quot;responsive_min_width&quot;:&quot;0%&quot;,&quot;responsive_min_width_unit&quot;:false,&quot;responsive_max_width&quot;:&quot;100%&quot;,&quot;responsive_max_width_unit&quot;:false,&quot;custom_width&quot;:&quot;640px&quot;,&quot;custom_width_unit&quot;:false,&quot;custom_height&quot;:&quot;380px&quot;,&quot;custom_height_unit&quot;:false,&quot;custom_height_auto&quot;:false,&quot;location&quot;:&quot;center top&quot;,&quot;position_from_trigger&quot;:false,&quot;position_top&quot;:&quot;100&quot;,&quot;position_left&quot;:&quot;0&quot;,&quot;position_bottom&quot;:&quot;0&quot;,&quot;position_right&quot;:&quot;0&quot;,&quot;position_fixed&quot;:false,&quot;animation_type&quot;:&quot;fade&quot;,&quot;animation_speed&quot;:&quot;350&quot;,&quot;animation_origin&quot;:&quot;center top&quot;,&quot;overlay_zindex&quot;:false,&quot;zindex&quot;:&quot;1999999999&quot;},&quot;close&quot;:{&quot;text&quot;:&quot;&quot;,&quot;button_delay&quot;:&quot;0&quot;,&quot;overlay_click&quot;:false,&quot;esc_press&quot;:false,&quot;f4_press&quot;:false},&quot;click_open&quot;:[]}}"
        role="dialog" aria-hidden="true">

        <div id="popmake-236192"
            class="pum-container popmake theme-235878 pum-responsive pum-responsive-medium responsive size-medium">







            <div class="pum-content popmake-content" tabindex="0">
                <div data-elementor-type="wp-post" data-elementor-id="236193" class="elementor elementor-236193">
                    <div class="elementor-inner">
                        <div class="elementor-section-wrap">
                            <section
                                class="has_eae_slider elementor-section elementor-top-section elementor-element elementor-element-3d0565c7 elementor-section-boxed elementor-section-height-default elementor-section-height-default"
                                data-id="3d0565c7" data-element_type="section">
                                <div class="elementor-container elementor-column-gap-default">
                                    <div class="elementor-row">
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-636c9b8b"
                                            data-id="636c9b8b" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-305e9eae elementor-widget elementor-widget-heading"
                                                        data-id="305e9eae" data-element_type="widget"
                                                        data-widget_type="heading.default">
                                                        <div class="elementor-widget-container">
                                                            <h2
                                                                class="elementor-heading-title elementor-size-default">
                                                                Opening Hours</h2>
                                                        </div>
                                                    </div>
                                                    <div class="elementor-element elementor-element-e622212 elementor-widget elementor-widget-eae-data-table"
                                                        data-id="e622212" data-element_type="widget"
                                                        data-widget_type="eae-data-table.default">
                                                        <div class="elementor-widget-container">

                                                            <div class="eae-table-container">
                                                                <div class="eae-table-wrapper">
                                                                    <div class="eae-table-wrap">

                                                                        <table class="eae-table"
                                                                            data-settings="{&quot;sort&quot;:false,&quot;search&quot;:false}">

                                                                            <thead class="eae-table-head">
                                                                                <tr class="eae-table-row">

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-253a221">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Day</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-c4ba098">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Time</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>
                                                                            </thead>
                                                                            <tbody class="eae-table__body">
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-399f732"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Tue</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-fb9d87a"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3
                                                                                                    pm - 8 pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2b03c3e"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Wed</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-cab5457"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3
                                                                                                    pm - 8 pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f2f570b"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Thu</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2f6ca86"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3
                                                                                                    pm - 8 pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f292d29"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Fri</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-83231a8"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3
                                                                                                    pm - 8 pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-50450dc5"
                                            data-id="50450dc5" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-3f3b767e elementor-widget elementor-widget-google_maps"
                                                        data-id="3f3b767e" data-element_type="widget"
                                                        data-widget_type="google_maps.default">
                                                        <div class="elementor-widget-container">
                                                            <div class="elementor-custom-embed">
                                                                <iframe frameborder="0" scrolling="no"
                                                                    marginheight="0" marginwidth="0"
                                                                    src="https://maps.google.com/maps?q=B2704%20Latifa%20Towers%2C%20Shiekh%20Zayed%20Road%20Dubai%2035053&amp;t=m&amp;z=17&amp;output=embed&amp;iwloc=near"
                                                                    title="B2704 Latifa Towers, Shiekh Zayed Road Dubai 35053"
                                                                    aria-label="B2704 Latifa Towers, Shiekh Zayed Road Dubai 35053"></iframe>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

            </div>




            <button type="button" class="pum-close popmake-close" aria-label="Close">
                CLOSE </button>

        </div>

    </div>
    <div id="pum-236149" class="pum pum-overlay pum-theme-235878 pum-theme-default-theme popmake-overlay click_open"
        data-popmake="{&quot;id&quot;:236149,&quot;slug&quot;:&quot;potong-pasir%e2%80%8b&quot;,&quot;theme_id&quot;:235878,&quot;cookies&quot;:[],&quot;triggers&quot;:[{&quot;type&quot;:&quot;click_open&quot;,&quot;settings&quot;:{&quot;extra_selectors&quot;:&quot;#potongpasir\u200b&quot;}}],&quot;mobile_disabled&quot;:null,&quot;tablet_disabled&quot;:null,&quot;meta&quot;:{&quot;display&quot;:{&quot;stackable&quot;:false,&quot;overlay_disabled&quot;:false,&quot;scrollable_content&quot;:false,&quot;disable_reposition&quot;:false,&quot;size&quot;:&quot;medium&quot;,&quot;responsive_min_width&quot;:&quot;0%&quot;,&quot;responsive_min_width_unit&quot;:false,&quot;responsive_max_width&quot;:&quot;100%&quot;,&quot;responsive_max_width_unit&quot;:false,&quot;custom_width&quot;:&quot;640px&quot;,&quot;custom_width_unit&quot;:false,&quot;custom_height&quot;:&quot;380px&quot;,&quot;custom_height_unit&quot;:false,&quot;custom_height_auto&quot;:false,&quot;location&quot;:&quot;center top&quot;,&quot;position_from_trigger&quot;:false,&quot;position_top&quot;:&quot;100&quot;,&quot;position_left&quot;:&quot;0&quot;,&quot;position_bottom&quot;:&quot;0&quot;,&quot;position_right&quot;:&quot;0&quot;,&quot;position_fixed&quot;:false,&quot;animation_type&quot;:&quot;fade&quot;,&quot;animation_speed&quot;:&quot;350&quot;,&quot;animation_origin&quot;:&quot;center top&quot;,&quot;overlay_zindex&quot;:false,&quot;zindex&quot;:&quot;1999999999&quot;},&quot;close&quot;:{&quot;text&quot;:&quot;&quot;,&quot;button_delay&quot;:&quot;0&quot;,&quot;overlay_click&quot;:false,&quot;esc_press&quot;:false,&quot;f4_press&quot;:false},&quot;click_open&quot;:[]}}"
        role="dialog" aria-hidden="true">

        <div id="popmake-236149"
            class="pum-container popmake theme-235878 pum-responsive pum-responsive-medium responsive size-medium">







            <div class="pum-content popmake-content" tabindex="0">
                <div data-elementor-type="wp-post" data-elementor-id="236148" class="elementor elementor-236148">
                    <div class="elementor-inner">
                        <div class="elementor-section-wrap">
                            <section
                                class="has_eae_slider elementor-section elementor-top-section elementor-element elementor-element-3d0565c7 elementor-section-boxed elementor-section-height-default elementor-section-height-default"
                                data-id="3d0565c7" data-element_type="section">
                                <div class="elementor-container elementor-column-gap-default">
                                    <div class="elementor-row">
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-636c9b8b"
                                            data-id="636c9b8b" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-305e9eae elementor-widget elementor-widget-heading"
                                                        data-id="305e9eae" data-element_type="widget"
                                                        data-widget_type="heading.default">
                                                        <div class="elementor-widget-container">
                                                            <h2
                                                                class="elementor-heading-title elementor-size-default">
                                                                Opening Hours</h2>
                                                        </div>
                                                    </div>
                                                    <div class="elementor-element elementor-element-f5e1ccb elementor-widget elementor-widget-eae-data-table"
                                                        data-id="f5e1ccb" data-element_type="widget"
                                                        data-widget_type="eae-data-table.default">
                                                        <div class="elementor-widget-container">

                                                            <div class="eae-table-container">
                                                                <div class="eae-table-wrapper">
                                                                    <div class="eae-table-wrap">

                                                                        <table class="eae-table"
                                                                            data-settings="{&quot;sort&quot;:false,&quot;search&quot;:false}">

                                                                            <thead class="eae-table-head">
                                                                                <tr class="eae-table-row">

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-253a221">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Day</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-c4ba098">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Time</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>
                                                                            </thead>
                                                                            <tbody class="eae-table__body">
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2b03c3e"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Tue</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-fb9d87a"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3:00pm
                                                                                                    - 8:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-23b283b"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Wed</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-3275543"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3:00pm
                                                                                                    - 8:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f2f570b"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Thu</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2f6ca86"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3:00pm
                                                                                                    - 8:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-48cc257"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Fri</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-00d4e19"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3:00pm
                                                                                                    - 8:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f292d29"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Sat</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-83231a8"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3:00pm
                                                                                                    - 7:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-895a361"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Sun</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-622a482"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3:00pm
                                                                                                    - 7:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-50450dc5"
                                            data-id="50450dc5" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-3f3b767e elementor-widget elementor-widget-google_maps"
                                                        data-id="3f3b767e" data-element_type="widget"
                                                        data-widget_type="google_maps.default">
                                                        <div class="elementor-widget-container">
                                                            <div class="elementor-custom-embed">
                                                                <iframe frameborder="0" scrolling="no"
                                                                    marginheight="0" marginwidth="0"
                                                                    src="https://maps.google.com/maps?q=eiMaths%40Potong%20Pasir%E2%80%8B&amp;t=m&amp;z=17&amp;output=embed&amp;iwloc=near"
                                                                    title="eiMaths@Potong Pasir​"
                                                                    aria-label="eiMaths@Potong Pasir​"></iframe>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

            </div>




            <button type="button" class="pum-close popmake-close" aria-label="Close">
                CLOSE </button>

        </div>

    </div>
    <div id="pum-236139" class="pum pum-overlay pum-theme-235878 pum-theme-default-theme popmake-overlay click_open"
        data-popmake="{&quot;id&quot;:236139,&quot;slug&quot;:&quot;hougang%e2%80%8b&quot;,&quot;theme_id&quot;:235878,&quot;cookies&quot;:[],&quot;triggers&quot;:[{&quot;type&quot;:&quot;click_open&quot;,&quot;settings&quot;:{&quot;extra_selectors&quot;:&quot;#hougang\u200b&quot;}}],&quot;mobile_disabled&quot;:null,&quot;tablet_disabled&quot;:null,&quot;meta&quot;:{&quot;display&quot;:{&quot;stackable&quot;:false,&quot;overlay_disabled&quot;:false,&quot;scrollable_content&quot;:false,&quot;disable_reposition&quot;:false,&quot;size&quot;:&quot;medium&quot;,&quot;responsive_min_width&quot;:&quot;0%&quot;,&quot;responsive_min_width_unit&quot;:false,&quot;responsive_max_width&quot;:&quot;100%&quot;,&quot;responsive_max_width_unit&quot;:false,&quot;custom_width&quot;:&quot;640px&quot;,&quot;custom_width_unit&quot;:false,&quot;custom_height&quot;:&quot;380px&quot;,&quot;custom_height_unit&quot;:false,&quot;custom_height_auto&quot;:false,&quot;location&quot;:&quot;center top&quot;,&quot;position_from_trigger&quot;:false,&quot;position_top&quot;:&quot;100&quot;,&quot;position_left&quot;:&quot;0&quot;,&quot;position_bottom&quot;:&quot;0&quot;,&quot;position_right&quot;:&quot;0&quot;,&quot;position_fixed&quot;:false,&quot;animation_type&quot;:&quot;fade&quot;,&quot;animation_speed&quot;:&quot;350&quot;,&quot;animation_origin&quot;:&quot;center top&quot;,&quot;overlay_zindex&quot;:false,&quot;zindex&quot;:&quot;1999999999&quot;},&quot;close&quot;:{&quot;text&quot;:&quot;&quot;,&quot;button_delay&quot;:&quot;0&quot;,&quot;overlay_click&quot;:false,&quot;esc_press&quot;:false,&quot;f4_press&quot;:false},&quot;click_open&quot;:[]}}"
        role="dialog" aria-hidden="true">

        <div id="popmake-236139"
            class="pum-container popmake theme-235878 pum-responsive pum-responsive-medium responsive size-medium">







            <div class="pum-content popmake-content" tabindex="0">
                <div data-elementor-type="wp-post" data-elementor-id="236138" class="elementor elementor-236138">
                    <div class="elementor-inner">
                        <div class="elementor-section-wrap">
                            <section
                                class="has_eae_slider elementor-section elementor-top-section elementor-element elementor-element-3d0565c7 elementor-section-boxed elementor-section-height-default elementor-section-height-default"
                                data-id="3d0565c7" data-element_type="section">
                                <div class="elementor-container elementor-column-gap-default">
                                    <div class="elementor-row">
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-636c9b8b"
                                            data-id="636c9b8b" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-305e9eae elementor-widget elementor-widget-heading"
                                                        data-id="305e9eae" data-element_type="widget"
                                                        data-widget_type="heading.default">
                                                        <div class="elementor-widget-container">
                                                            <h2
                                                                class="elementor-heading-title elementor-size-default">
                                                                Opening Hours</h2>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-50450dc5"
                                            data-id="50450dc5" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-3f3b767e elementor-widget elementor-widget-google_maps"
                                                        data-id="3f3b767e" data-element_type="widget"
                                                        data-widget_type="google_maps.default">
                                                        <div class="elementor-widget-container">
                                                            <div class="elementor-custom-embed">
                                                                <iframe frameborder="0" scrolling="no"
                                                                    marginheight="0" marginwidth="0"
                                                                    src="https://maps.google.com/maps?q=eiMaths%40Hougang&amp;t=m&amp;z=17&amp;output=embed&amp;iwloc=near"
                                                                    title="eiMaths@Hougang"
                                                                    aria-label="eiMaths@Hougang"></iframe>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

            </div>




            <button type="button" class="pum-close popmake-close" aria-label="Close">
                CLOSE </button>

        </div>

    </div>
    <div id="pum-236126" class="pum pum-overlay pum-theme-235878 pum-theme-default-theme popmake-overlay click_open"
        data-popmake="{&quot;id&quot;:236126,&quot;slug&quot;:&quot;serangoon%e2%80%8b&quot;,&quot;theme_id&quot;:235878,&quot;cookies&quot;:[],&quot;triggers&quot;:[{&quot;type&quot;:&quot;click_open&quot;,&quot;settings&quot;:{&quot;extra_selectors&quot;:&quot;#serangoon\u200b&quot;}}],&quot;mobile_disabled&quot;:null,&quot;tablet_disabled&quot;:null,&quot;meta&quot;:{&quot;display&quot;:{&quot;stackable&quot;:false,&quot;overlay_disabled&quot;:false,&quot;scrollable_content&quot;:false,&quot;disable_reposition&quot;:false,&quot;size&quot;:&quot;medium&quot;,&quot;responsive_min_width&quot;:&quot;0%&quot;,&quot;responsive_min_width_unit&quot;:false,&quot;responsive_max_width&quot;:&quot;100%&quot;,&quot;responsive_max_width_unit&quot;:false,&quot;custom_width&quot;:&quot;640px&quot;,&quot;custom_width_unit&quot;:false,&quot;custom_height&quot;:&quot;380px&quot;,&quot;custom_height_unit&quot;:false,&quot;custom_height_auto&quot;:false,&quot;location&quot;:&quot;center top&quot;,&quot;position_from_trigger&quot;:false,&quot;position_top&quot;:&quot;100&quot;,&quot;position_left&quot;:&quot;0&quot;,&quot;position_bottom&quot;:&quot;0&quot;,&quot;position_right&quot;:&quot;0&quot;,&quot;position_fixed&quot;:false,&quot;animation_type&quot;:&quot;fade&quot;,&quot;animation_speed&quot;:&quot;350&quot;,&quot;animation_origin&quot;:&quot;center top&quot;,&quot;overlay_zindex&quot;:false,&quot;zindex&quot;:&quot;1999999999&quot;},&quot;close&quot;:{&quot;text&quot;:&quot;&quot;,&quot;button_delay&quot;:&quot;0&quot;,&quot;overlay_click&quot;:false,&quot;esc_press&quot;:false,&quot;f4_press&quot;:false},&quot;click_open&quot;:[]}}"
        role="dialog" aria-hidden="true">

        <div id="popmake-236126"
            class="pum-container popmake theme-235878 pum-responsive pum-responsive-medium responsive size-medium">







            <div class="pum-content popmake-content" tabindex="0">
                <div data-elementor-type="wp-post" data-elementor-id="236124" class="elementor elementor-236124">
                    <div class="elementor-inner">
                        <div class="elementor-section-wrap">
                            <section
                                class="has_eae_slider elementor-section elementor-top-section elementor-element elementor-element-3d0565c7 elementor-section-boxed elementor-section-height-default elementor-section-height-default"
                                data-id="3d0565c7" data-element_type="section">
                                <div class="elementor-container elementor-column-gap-default">
                                    <div class="elementor-row">
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-636c9b8b"
                                            data-id="636c9b8b" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-305e9eae elementor-widget elementor-widget-heading"
                                                        data-id="305e9eae" data-element_type="widget"
                                                        data-widget_type="heading.default">
                                                        <div class="elementor-widget-container">
                                                            <h2
                                                                class="elementor-heading-title elementor-size-default">
                                                                Opening Hours</h2>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-50450dc5"
                                            data-id="50450dc5" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-3f3b767e elementor-widget elementor-widget-google_maps"
                                                        data-id="3f3b767e" data-element_type="widget"
                                                        data-widget_type="google_maps.default">
                                                        <div class="elementor-widget-container">
                                                            <div class="elementor-custom-embed">
                                                                <iframe frameborder="0" scrolling="no"
                                                                    marginheight="0" marginwidth="0"
                                                                    src="https://maps.google.com/maps?q=eiMaths%40Serangoon&amp;t=m&amp;z=17&amp;output=embed&amp;iwloc=near"
                                                                    title="eiMaths@Serangoon"
                                                                    aria-label="eiMaths@Serangoon"></iframe>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

            </div>




            <button type="button" class="pum-close popmake-close" aria-label="Close">
                CLOSE </button>

        </div>

    </div>
    <div id="pum-236113" class="pum pum-overlay pum-theme-235878 pum-theme-default-theme popmake-overlay click_open"
        data-popmake="{&quot;id&quot;:236113,&quot;slug&quot;:&quot;buangkok%e2%80%8b&quot;,&quot;theme_id&quot;:235878,&quot;cookies&quot;:[],&quot;triggers&quot;:[{&quot;type&quot;:&quot;click_open&quot;,&quot;settings&quot;:{&quot;cookie_name&quot;:&quot;&quot;,&quot;extra_selectors&quot;:&quot;#buangkok&quot;}}],&quot;mobile_disabled&quot;:null,&quot;tablet_disabled&quot;:null,&quot;meta&quot;:{&quot;display&quot;:{&quot;stackable&quot;:false,&quot;overlay_disabled&quot;:false,&quot;scrollable_content&quot;:false,&quot;disable_reposition&quot;:false,&quot;size&quot;:&quot;medium&quot;,&quot;responsive_min_width&quot;:&quot;0%&quot;,&quot;responsive_min_width_unit&quot;:false,&quot;responsive_max_width&quot;:&quot;100%&quot;,&quot;responsive_max_width_unit&quot;:false,&quot;custom_width&quot;:&quot;640px&quot;,&quot;custom_width_unit&quot;:false,&quot;custom_height&quot;:&quot;380px&quot;,&quot;custom_height_unit&quot;:false,&quot;custom_height_auto&quot;:false,&quot;location&quot;:&quot;center top&quot;,&quot;position_from_trigger&quot;:false,&quot;position_top&quot;:&quot;100&quot;,&quot;position_left&quot;:&quot;0&quot;,&quot;position_bottom&quot;:&quot;0&quot;,&quot;position_right&quot;:&quot;0&quot;,&quot;position_fixed&quot;:false,&quot;animation_type&quot;:&quot;fade&quot;,&quot;animation_speed&quot;:&quot;350&quot;,&quot;animation_origin&quot;:&quot;center top&quot;,&quot;overlay_zindex&quot;:false,&quot;zindex&quot;:&quot;1999999999&quot;},&quot;close&quot;:{&quot;text&quot;:&quot;&quot;,&quot;button_delay&quot;:&quot;0&quot;,&quot;overlay_click&quot;:false,&quot;esc_press&quot;:false,&quot;f4_press&quot;:false},&quot;click_open&quot;:[]}}"
        role="dialog" aria-hidden="true">

        <div id="popmake-236113"
            class="pum-container popmake theme-235878 pum-responsive pum-responsive-medium responsive size-medium">







            <div class="pum-content popmake-content" tabindex="0">
                <div data-elementor-type="wp-post" data-elementor-id="236114" class="elementor elementor-236114">
                    <div class="elementor-inner">
                        <div class="elementor-section-wrap">
                            <section
                                class="has_eae_slider elementor-section elementor-top-section elementor-element elementor-element-3d0565c7 elementor-section-boxed elementor-section-height-default elementor-section-height-default"
                                data-id="3d0565c7" data-element_type="section">
                                <div class="elementor-container elementor-column-gap-default">
                                    <div class="elementor-row">
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-636c9b8b"
                                            data-id="636c9b8b" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-305e9eae elementor-widget elementor-widget-heading"
                                                        data-id="305e9eae" data-element_type="widget"
                                                        data-widget_type="heading.default">
                                                        <div class="elementor-widget-container">
                                                            <h2
                                                                class="elementor-heading-title elementor-size-default">
                                                                Opening Hours</h2>
                                                        </div>
                                                    </div>
                                                    <div class="elementor-element elementor-element-62a831b elementor-widget elementor-widget-eae-data-table"
                                                        data-id="62a831b" data-element_type="widget"
                                                        data-widget_type="eae-data-table.default">
                                                        <div class="elementor-widget-container">

                                                            <div class="eae-table-container">
                                                                <div class="eae-table-wrapper">
                                                                    <div class="eae-table-wrap">

                                                                        <table class="eae-table"
                                                                            data-settings="{&quot;sort&quot;:false,&quot;search&quot;:false}">

                                                                            <thead class="eae-table-head">
                                                                                <tr class="eae-table-row">

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-253a221">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Day</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-c4ba098">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Time</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>
                                                                            </thead>
                                                                            <tbody class="eae-table__body">
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f2f570b"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Thu</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2f6ca86"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">5:30pm
                                                                                                    - 9:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-48cc257"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Fri</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-00d4e19"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">5:30pm
                                                                                                    - 9:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f292d29"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Sat</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-83231a8"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">10:00am
                                                                                                    - 2:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-50450dc5"
                                            data-id="50450dc5" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-3f3b767e elementor-widget elementor-widget-google_maps"
                                                        data-id="3f3b767e" data-element_type="widget"
                                                        data-widget_type="google_maps.default">
                                                        <div class="elementor-widget-container">
                                                            <div class="elementor-custom-embed">
                                                                <iframe frameborder="0" scrolling="no"
                                                                    marginheight="0" marginwidth="0"
                                                                    src="https://maps.google.com/maps?q=eiMaths%40Buangkok&amp;t=m&amp;z=17&amp;output=embed&amp;iwloc=near"
                                                                    title="eiMaths@Buangkok"
                                                                    aria-label="eiMaths@Buangkok"></iframe>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

            </div>




            <button type="button" class="pum-close popmake-close" aria-label="Close">
                CLOSE </button>

        </div>

    </div>
    <div id="pum-236102" class="pum pum-overlay pum-theme-235878 pum-theme-default-theme popmake-overlay click_open"
        data-popmake="{&quot;id&quot;:236102,&quot;slug&quot;:&quot;punggol&quot;,&quot;theme_id&quot;:235878,&quot;cookies&quot;:[],&quot;triggers&quot;:[{&quot;type&quot;:&quot;click_open&quot;,&quot;settings&quot;:{&quot;extra_selectors&quot;:&quot;#punggol&quot;}}],&quot;mobile_disabled&quot;:null,&quot;tablet_disabled&quot;:null,&quot;meta&quot;:{&quot;display&quot;:{&quot;stackable&quot;:false,&quot;overlay_disabled&quot;:false,&quot;scrollable_content&quot;:false,&quot;disable_reposition&quot;:false,&quot;size&quot;:&quot;medium&quot;,&quot;responsive_min_width&quot;:&quot;0%&quot;,&quot;responsive_min_width_unit&quot;:false,&quot;responsive_max_width&quot;:&quot;100%&quot;,&quot;responsive_max_width_unit&quot;:false,&quot;custom_width&quot;:&quot;640px&quot;,&quot;custom_width_unit&quot;:false,&quot;custom_height&quot;:&quot;380px&quot;,&quot;custom_height_unit&quot;:false,&quot;custom_height_auto&quot;:false,&quot;location&quot;:&quot;center top&quot;,&quot;position_from_trigger&quot;:false,&quot;position_top&quot;:&quot;100&quot;,&quot;position_left&quot;:&quot;0&quot;,&quot;position_bottom&quot;:&quot;0&quot;,&quot;position_right&quot;:&quot;0&quot;,&quot;position_fixed&quot;:false,&quot;animation_type&quot;:&quot;fade&quot;,&quot;animation_speed&quot;:&quot;350&quot;,&quot;animation_origin&quot;:&quot;center top&quot;,&quot;overlay_zindex&quot;:false,&quot;zindex&quot;:&quot;1999999999&quot;},&quot;close&quot;:{&quot;text&quot;:&quot;&quot;,&quot;button_delay&quot;:&quot;0&quot;,&quot;overlay_click&quot;:false,&quot;esc_press&quot;:false,&quot;f4_press&quot;:false},&quot;click_open&quot;:[]}}"
        role="dialog" aria-hidden="true">

        <div id="popmake-236102"
            class="pum-container popmake theme-235878 pum-responsive pum-responsive-medium responsive size-medium">







            <div class="pum-content popmake-content" tabindex="0">
                <div data-elementor-type="wp-post" data-elementor-id="236103" class="elementor elementor-236103">
                    <div class="elementor-inner">
                        <div class="elementor-section-wrap">
                            <section
                                class="has_eae_slider elementor-section elementor-top-section elementor-element elementor-element-3d0565c7 elementor-section-boxed elementor-section-height-default elementor-section-height-default"
                                data-id="3d0565c7" data-element_type="section">
                                <div class="elementor-container elementor-column-gap-default">
                                    <div class="elementor-row">
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-636c9b8b"
                                            data-id="636c9b8b" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-305e9eae elementor-widget elementor-widget-heading"
                                                        data-id="305e9eae" data-element_type="widget"
                                                        data-widget_type="heading.default">
                                                        <div class="elementor-widget-container">
                                                            <h2
                                                                class="elementor-heading-title elementor-size-default">
                                                                Opening Hours</h2>
                                                        </div>
                                                    </div>
                                                    <div class="elementor-element elementor-element-076a85e elementor-widget elementor-widget-eae-data-table"
                                                        data-id="076a85e" data-element_type="widget"
                                                        data-widget_type="eae-data-table.default">
                                                        <div class="elementor-widget-container">

                                                            <div class="eae-table-container">
                                                                <div class="eae-table-wrapper">
                                                                    <div class="eae-table-wrap">

                                                                        <table class="eae-table"
                                                                            data-settings="{&quot;sort&quot;:false,&quot;search&quot;:false}">

                                                                            <thead class="eae-table-head">
                                                                                <tr class="eae-table-row">

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-253a221">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Day</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-c4ba098">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Time</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>
                                                                            </thead>
                                                                            <tbody class="eae-table__body">
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-48cc257"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Fri</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-00d4e19"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">4:00pm
                                                                                                    - 5:30pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f292d29"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Sat</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-83231a8"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">9:30am
                                                                                                    - 11:00am</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-50450dc5"
                                            data-id="50450dc5" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-3f3b767e elementor-widget elementor-widget-google_maps"
                                                        data-id="3f3b767e" data-element_type="widget"
                                                        data-widget_type="google_maps.default">
                                                        <div class="elementor-widget-container">
                                                            <div class="elementor-custom-embed">
                                                                <iframe frameborder="0" scrolling="no"
                                                                    marginheight="0" marginwidth="0"
                                                                    src="https://maps.google.com/maps?q=eiMaths%40Punggol&amp;t=m&amp;z=17&amp;output=embed&amp;iwloc=near"
                                                                    title="eiMaths@Punggol"
                                                                    aria-label="eiMaths@Punggol"></iframe>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

            </div>




            <button type="button" class="pum-close popmake-close" aria-label="Close">
                CLOSE </button>

        </div>

    </div>
    <div id="pum-236089" class="pum pum-overlay pum-theme-235878 pum-theme-default-theme popmake-overlay click_open"
        data-popmake="{&quot;id&quot;:236089,&quot;slug&quot;:&quot;bukit-panjang&quot;,&quot;theme_id&quot;:235878,&quot;cookies&quot;:[],&quot;triggers&quot;:[{&quot;type&quot;:&quot;click_open&quot;,&quot;settings&quot;:{&quot;extra_selectors&quot;:&quot;#bukitpanjang&quot;}}],&quot;mobile_disabled&quot;:null,&quot;tablet_disabled&quot;:null,&quot;meta&quot;:{&quot;display&quot;:{&quot;stackable&quot;:false,&quot;overlay_disabled&quot;:false,&quot;scrollable_content&quot;:false,&quot;disable_reposition&quot;:false,&quot;size&quot;:&quot;medium&quot;,&quot;responsive_min_width&quot;:&quot;0%&quot;,&quot;responsive_min_width_unit&quot;:false,&quot;responsive_max_width&quot;:&quot;100%&quot;,&quot;responsive_max_width_unit&quot;:false,&quot;custom_width&quot;:&quot;640px&quot;,&quot;custom_width_unit&quot;:false,&quot;custom_height&quot;:&quot;380px&quot;,&quot;custom_height_unit&quot;:false,&quot;custom_height_auto&quot;:false,&quot;location&quot;:&quot;center top&quot;,&quot;position_from_trigger&quot;:false,&quot;position_top&quot;:&quot;100&quot;,&quot;position_left&quot;:&quot;0&quot;,&quot;position_bottom&quot;:&quot;0&quot;,&quot;position_right&quot;:&quot;0&quot;,&quot;position_fixed&quot;:false,&quot;animation_type&quot;:&quot;fade&quot;,&quot;animation_speed&quot;:&quot;350&quot;,&quot;animation_origin&quot;:&quot;center top&quot;,&quot;overlay_zindex&quot;:false,&quot;zindex&quot;:&quot;1999999999&quot;},&quot;close&quot;:{&quot;text&quot;:&quot;&quot;,&quot;button_delay&quot;:&quot;0&quot;,&quot;overlay_click&quot;:false,&quot;esc_press&quot;:false,&quot;f4_press&quot;:false},&quot;click_open&quot;:[]}}"
        role="dialog" aria-hidden="true">

        <div id="popmake-236089"
            class="pum-container popmake theme-235878 pum-responsive pum-responsive-medium responsive size-medium">







            <div class="pum-content popmake-content" tabindex="0">
                <div data-elementor-type="wp-post" data-elementor-id="236090" class="elementor elementor-236090">
                    <div class="elementor-inner">
                        <div class="elementor-section-wrap">
                            <section
                                class="has_eae_slider elementor-section elementor-top-section elementor-element elementor-element-3d0565c7 elementor-section-boxed elementor-section-height-default elementor-section-height-default"
                                data-id="3d0565c7" data-element_type="section">
                                <div class="elementor-container elementor-column-gap-default">
                                    <div class="elementor-row">
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-636c9b8b"
                                            data-id="636c9b8b" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-305e9eae elementor-widget elementor-widget-heading"
                                                        data-id="305e9eae" data-element_type="widget"
                                                        data-widget_type="heading.default">
                                                        <div class="elementor-widget-container">
                                                            <h2
                                                                class="elementor-heading-title elementor-size-default">
                                                                Opening Hours</h2>
                                                        </div>
                                                    </div>
                                                    <div class="elementor-element elementor-element-e0b4df2 elementor-widget elementor-widget-eae-data-table"
                                                        data-id="e0b4df2" data-element_type="widget"
                                                        data-widget_type="eae-data-table.default">
                                                        <div class="elementor-widget-container">

                                                            <div class="eae-table-container">
                                                                <div class="eae-table-wrapper">
                                                                    <div class="eae-table-wrap">

                                                                        <table class="eae-table"
                                                                            data-settings="{&quot;sort&quot;:false,&quot;search&quot;:false}">

                                                                            <thead class="eae-table-head">
                                                                                <tr class="eae-table-row">

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-253a221">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Day</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-c4ba098">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Time</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>
                                                                            </thead>
                                                                            <tbody class="eae-table__body">
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f2f570b"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Wed</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2f6ca86"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">6:30pm
                                                                                                    - 8:30pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f292d29"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Sat</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-83231a8"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">11:00am
                                                                                                    - 3:30pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-50450dc5"
                                            data-id="50450dc5" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-3f3b767e elementor-widget elementor-widget-google_maps"
                                                        data-id="3f3b767e" data-element_type="widget"
                                                        data-widget_type="google_maps.default">
                                                        <div class="elementor-widget-container">
                                                            <div class="elementor-custom-embed">
                                                                <iframe frameborder="0" scrolling="no"
                                                                    marginheight="0" marginwidth="0"
                                                                    src="https://maps.google.com/maps?q=eiMaths%40Bukit%20Panjang&amp;t=m&amp;z=17&amp;output=embed&amp;iwloc=near"
                                                                    title="eiMaths@Bukit Panjang"
                                                                    aria-label="eiMaths@Bukit Panjang"></iframe>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

            </div>




            <button type="button" class="pum-close popmake-close" aria-label="Close">
                CLOSE </button>

        </div>

    </div>
    <div id="pum-236081" class="pum pum-overlay pum-theme-235878 pum-theme-default-theme popmake-overlay click_open"
        data-popmake="{&quot;id&quot;:236081,&quot;slug&quot;:&quot;jurong-safra-2&quot;,&quot;theme_id&quot;:235878,&quot;cookies&quot;:[],&quot;triggers&quot;:[{&quot;type&quot;:&quot;click_open&quot;,&quot;settings&quot;:{&quot;extra_selectors&quot;:&quot;#jurongsafra2&quot;}}],&quot;mobile_disabled&quot;:null,&quot;tablet_disabled&quot;:null,&quot;meta&quot;:{&quot;display&quot;:{&quot;stackable&quot;:false,&quot;overlay_disabled&quot;:false,&quot;scrollable_content&quot;:false,&quot;disable_reposition&quot;:false,&quot;size&quot;:&quot;medium&quot;,&quot;responsive_min_width&quot;:&quot;0%&quot;,&quot;responsive_min_width_unit&quot;:false,&quot;responsive_max_width&quot;:&quot;100%&quot;,&quot;responsive_max_width_unit&quot;:false,&quot;custom_width&quot;:&quot;640px&quot;,&quot;custom_width_unit&quot;:false,&quot;custom_height&quot;:&quot;380px&quot;,&quot;custom_height_unit&quot;:false,&quot;custom_height_auto&quot;:false,&quot;location&quot;:&quot;center top&quot;,&quot;position_from_trigger&quot;:false,&quot;position_top&quot;:&quot;100&quot;,&quot;position_left&quot;:&quot;0&quot;,&quot;position_bottom&quot;:&quot;0&quot;,&quot;position_right&quot;:&quot;0&quot;,&quot;position_fixed&quot;:false,&quot;animation_type&quot;:&quot;fade&quot;,&quot;animation_speed&quot;:&quot;350&quot;,&quot;animation_origin&quot;:&quot;center top&quot;,&quot;overlay_zindex&quot;:false,&quot;zindex&quot;:&quot;1999999999&quot;},&quot;close&quot;:{&quot;text&quot;:&quot;&quot;,&quot;button_delay&quot;:&quot;0&quot;,&quot;overlay_click&quot;:false,&quot;esc_press&quot;:false,&quot;f4_press&quot;:false},&quot;click_open&quot;:[]}}"
        role="dialog" aria-hidden="true">

        <div id="popmake-236081"
            class="pum-container popmake theme-235878 pum-responsive pum-responsive-medium responsive size-medium">







            <div class="pum-content popmake-content" tabindex="0">
                <div data-elementor-type="wp-post" data-elementor-id="236082" class="elementor elementor-236082">
                    <div class="elementor-inner">
                        <div class="elementor-section-wrap">
                            <section
                                class="has_eae_slider elementor-section elementor-top-section elementor-element elementor-element-3d0565c7 elementor-section-boxed elementor-section-height-default elementor-section-height-default"
                                data-id="3d0565c7" data-element_type="section">
                                <div class="elementor-container elementor-column-gap-default">
                                    <div class="elementor-row">
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-636c9b8b"
                                            data-id="636c9b8b" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-305e9eae elementor-widget elementor-widget-heading"
                                                        data-id="305e9eae" data-element_type="widget"
                                                        data-widget_type="heading.default">
                                                        <div class="elementor-widget-container">
                                                            <h2
                                                                class="elementor-heading-title elementor-size-default">
                                                                Opening Hours</h2>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-50450dc5"
                                            data-id="50450dc5" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-3f3b767e elementor-widget elementor-widget-google_maps"
                                                        data-id="3f3b767e" data-element_type="widget"
                                                        data-widget_type="google_maps.default">
                                                        <div class="elementor-widget-container">
                                                            <div class="elementor-custom-embed">
                                                                <iframe frameborder="0" scrolling="no"
                                                                    marginheight="0" marginwidth="0"
                                                                    src="https://maps.google.com/maps?q=Blk%20751%20Jurong%20West%20St73%20%23B1-175%20S640751&amp;t=m&amp;z=17&amp;output=embed&amp;iwloc=near"
                                                                    title="Blk 751 Jurong West St73 #B1-175 S640751"
                                                                    aria-label="Blk 751 Jurong West St73 #B1-175 S640751"></iframe>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

            </div>




            <button type="button" class="pum-close popmake-close" aria-label="Close">
                CLOSE </button>

        </div>

    </div>
    <div id="pum-236065" class="pum pum-overlay pum-theme-235878 pum-theme-default-theme popmake-overlay click_open"
        data-popmake="{&quot;id&quot;:236065,&quot;slug&quot;:&quot;jurong-safra-1&quot;,&quot;theme_id&quot;:235878,&quot;cookies&quot;:[],&quot;triggers&quot;:[{&quot;type&quot;:&quot;click_open&quot;,&quot;settings&quot;:{&quot;extra_selectors&quot;:&quot;#jurongsafra1&quot;}}],&quot;mobile_disabled&quot;:null,&quot;tablet_disabled&quot;:null,&quot;meta&quot;:{&quot;display&quot;:{&quot;stackable&quot;:false,&quot;overlay_disabled&quot;:false,&quot;scrollable_content&quot;:false,&quot;disable_reposition&quot;:false,&quot;size&quot;:&quot;medium&quot;,&quot;responsive_min_width&quot;:&quot;0%&quot;,&quot;responsive_min_width_unit&quot;:false,&quot;responsive_max_width&quot;:&quot;100%&quot;,&quot;responsive_max_width_unit&quot;:false,&quot;custom_width&quot;:&quot;640px&quot;,&quot;custom_width_unit&quot;:false,&quot;custom_height&quot;:&quot;380px&quot;,&quot;custom_height_unit&quot;:false,&quot;custom_height_auto&quot;:false,&quot;location&quot;:&quot;center top&quot;,&quot;position_from_trigger&quot;:false,&quot;position_top&quot;:&quot;100&quot;,&quot;position_left&quot;:&quot;0&quot;,&quot;position_bottom&quot;:&quot;0&quot;,&quot;position_right&quot;:&quot;0&quot;,&quot;position_fixed&quot;:false,&quot;animation_type&quot;:&quot;fade&quot;,&quot;animation_speed&quot;:&quot;350&quot;,&quot;animation_origin&quot;:&quot;center top&quot;,&quot;overlay_zindex&quot;:false,&quot;zindex&quot;:&quot;1999999999&quot;},&quot;close&quot;:{&quot;text&quot;:&quot;&quot;,&quot;button_delay&quot;:&quot;0&quot;,&quot;overlay_click&quot;:false,&quot;esc_press&quot;:false,&quot;f4_press&quot;:false},&quot;click_open&quot;:[]}}"
        role="dialog" aria-hidden="true">

        <div id="popmake-236065"
            class="pum-container popmake theme-235878 pum-responsive pum-responsive-medium responsive size-medium">







            <div class="pum-content popmake-content" tabindex="0">
                <div data-elementor-type="wp-post" data-elementor-id="236063" class="elementor elementor-236063">
                    <div class="elementor-inner">
                        <div class="elementor-section-wrap">
                            <section
                                class="has_eae_slider elementor-section elementor-top-section elementor-element elementor-element-3d0565c7 elementor-section-boxed elementor-section-height-default elementor-section-height-default"
                                data-id="3d0565c7" data-element_type="section">
                                <div class="elementor-container elementor-column-gap-default">
                                    <div class="elementor-row">
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-636c9b8b"
                                            data-id="636c9b8b" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-305e9eae elementor-widget elementor-widget-heading"
                                                        data-id="305e9eae" data-element_type="widget"
                                                        data-widget_type="heading.default">
                                                        <div class="elementor-widget-container">
                                                            <h2
                                                                class="elementor-heading-title elementor-size-default">
                                                                Opening Hours</h2>
                                                        </div>
                                                    </div>
                                                    <div class="elementor-element elementor-element-435de8c elementor-widget elementor-widget-eae-data-table"
                                                        data-id="435de8c" data-element_type="widget"
                                                        data-widget_type="eae-data-table.default">
                                                        <div class="elementor-widget-container">

                                                            <div class="eae-table-container">
                                                                <div class="eae-table-wrapper">
                                                                    <div class="eae-table-wrap">

                                                                        <table class="eae-table"
                                                                            data-settings="{&quot;sort&quot;:false,&quot;search&quot;:false}">

                                                                            <thead class="eae-table-head">
                                                                                <tr class="eae-table-row">

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-253a221">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Day</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-c4ba098">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Time</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>
                                                                            </thead>
                                                                            <tbody class="eae-table__body">
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f2f570b"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Mon</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2f6ca86"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">6:00pm
                                                                                                    - 9:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2b03c3e"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Tue</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-fb9d87a"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">6:00pm
                                                                                                    - 9:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f292d29"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Sat</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-83231a8"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">9:00am
                                                                                                    - 1:30pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-50450dc5"
                                            data-id="50450dc5" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-3f3b767e elementor-widget elementor-widget-google_maps"
                                                        data-id="3f3b767e" data-element_type="widget"
                                                        data-widget_type="google_maps.default">
                                                        <div class="elementor-widget-container">
                                                            <div class="elementor-custom-embed">
                                                                <iframe frameborder="0" scrolling="no"
                                                                    marginheight="0" marginwidth="0"
                                                                    src="https://maps.google.com/maps?q=eiMaths%40Jurong%20SAFRA&amp;t=m&amp;z=17&amp;output=embed&amp;iwloc=near"
                                                                    title="eiMaths@Jurong SAFRA"
                                                                    aria-label="eiMaths@Jurong SAFRA"></iframe>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

            </div>




            <button type="button" class="pum-close popmake-close" aria-label="Close">
                CLOSE </button>

        </div>

    </div>
    <div id="pum-236054" class="pum pum-overlay pum-theme-235878 pum-theme-default-theme popmake-overlay click_open"
        data-popmake="{&quot;id&quot;:236054,&quot;slug&quot;:&quot;katong&quot;,&quot;theme_id&quot;:235878,&quot;cookies&quot;:[],&quot;triggers&quot;:[{&quot;type&quot;:&quot;click_open&quot;,&quot;settings&quot;:{&quot;cookie_name&quot;:&quot;&quot;,&quot;extra_selectors&quot;:&quot;#katong&quot;}}],&quot;mobile_disabled&quot;:null,&quot;tablet_disabled&quot;:null,&quot;meta&quot;:{&quot;display&quot;:{&quot;stackable&quot;:false,&quot;overlay_disabled&quot;:false,&quot;scrollable_content&quot;:false,&quot;disable_reposition&quot;:false,&quot;size&quot;:&quot;medium&quot;,&quot;responsive_min_width&quot;:&quot;0%&quot;,&quot;responsive_min_width_unit&quot;:false,&quot;responsive_max_width&quot;:&quot;100%&quot;,&quot;responsive_max_width_unit&quot;:false,&quot;custom_width&quot;:&quot;640px&quot;,&quot;custom_width_unit&quot;:false,&quot;custom_height&quot;:&quot;380px&quot;,&quot;custom_height_unit&quot;:false,&quot;custom_height_auto&quot;:false,&quot;location&quot;:&quot;center top&quot;,&quot;position_from_trigger&quot;:false,&quot;position_top&quot;:&quot;100&quot;,&quot;position_left&quot;:&quot;0&quot;,&quot;position_bottom&quot;:&quot;0&quot;,&quot;position_right&quot;:&quot;0&quot;,&quot;position_fixed&quot;:false,&quot;animation_type&quot;:&quot;fade&quot;,&quot;animation_speed&quot;:&quot;350&quot;,&quot;animation_origin&quot;:&quot;center top&quot;,&quot;overlay_zindex&quot;:false,&quot;zindex&quot;:&quot;1999999999&quot;},&quot;close&quot;:{&quot;text&quot;:&quot;&quot;,&quot;button_delay&quot;:&quot;0&quot;,&quot;overlay_click&quot;:false,&quot;esc_press&quot;:false,&quot;f4_press&quot;:false},&quot;click_open&quot;:[]}}"
        role="dialog" aria-hidden="true">

        <div id="popmake-236054"
            class="pum-container popmake theme-235878 pum-responsive pum-responsive-medium responsive size-medium">







            <div class="pum-content popmake-content" tabindex="0">
                <div data-elementor-type="wp-post" data-elementor-id="236049" class="elementor elementor-236049">
                    <div class="elementor-inner">
                        <div class="elementor-section-wrap">
                            <section
                                class="has_eae_slider elementor-section elementor-top-section elementor-element elementor-element-3d0565c7 elementor-section-boxed elementor-section-height-default elementor-section-height-default"
                                data-id="3d0565c7" data-element_type="section">
                                <div class="elementor-container elementor-column-gap-default">
                                    <div class="elementor-row">
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-636c9b8b"
                                            data-id="636c9b8b" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-305e9eae elementor-widget elementor-widget-heading"
                                                        data-id="305e9eae" data-element_type="widget"
                                                        data-widget_type="heading.default">
                                                        <div class="elementor-widget-container">
                                                            <h2
                                                                class="elementor-heading-title elementor-size-default">
                                                                Opening Hours</h2>
                                                        </div>
                                                    </div>
                                                    <div class="elementor-element elementor-element-dd9406f elementor-widget elementor-widget-eae-data-table"
                                                        data-id="dd9406f" data-element_type="widget"
                                                        data-widget_type="eae-data-table.default">
                                                        <div class="elementor-widget-container">

                                                            <div class="eae-table-container">
                                                                <div class="eae-table-wrapper">
                                                                    <div class="eae-table-wrap">

                                                                        <table class="eae-table"
                                                                            data-settings="{&quot;sort&quot;:false,&quot;search&quot;:false}">

                                                                            <thead class="eae-table-head">
                                                                                <tr class="eae-table-row">

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-253a221">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Day</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-c4ba098">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Time</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>
                                                                            </thead>
                                                                            <tbody class="eae-table__body">
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-48cc257"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Mon</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-00d4e19"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3:00pm
                                                                                                    - 8:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2b03c3e"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Tue</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-fb9d87a"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3:00pm
                                                                                                    - 8:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f2f570b"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Wed</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2f6ca86"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3:00pm
                                                                                                    - 8:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f292d29"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Sat</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-83231a8"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">9:00am
                                                                                                    - 3:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-50450dc5"
                                            data-id="50450dc5" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-3f3b767e elementor-widget elementor-widget-google_maps"
                                                        data-id="3f3b767e" data-element_type="widget"
                                                        data-widget_type="google_maps.default">
                                                        <div class="elementor-widget-container">
                                                            <div class="elementor-custom-embed">
                                                                <iframe frameborder="0" scrolling="no"
                                                                    marginheight="0" marginwidth="0"
                                                                    src="https://maps.google.com/maps?q=eiMaths%40Roxy%20Square&amp;t=m&amp;z=17&amp;output=embed&amp;iwloc=near"
                                                                    title="eiMaths@Roxy Square"
                                                                    aria-label="eiMaths@Roxy Square"></iframe>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

            </div>




            <button type="button" class="pum-close popmake-close" aria-label="Close">
                CLOSE </button>

        </div>

    </div>
    <div id="pum-236040" class="pum pum-overlay pum-theme-235878 pum-theme-default-theme popmake-overlay click_open"
        data-popmake="{&quot;id&quot;:236040,&quot;slug&quot;:&quot;aljunied%e2%80%8b&quot;,&quot;theme_id&quot;:235878,&quot;cookies&quot;:[],&quot;triggers&quot;:[{&quot;type&quot;:&quot;click_open&quot;,&quot;settings&quot;:{&quot;extra_selectors&quot;:&quot;#aljunied&quot;}}],&quot;mobile_disabled&quot;:null,&quot;tablet_disabled&quot;:null,&quot;meta&quot;:{&quot;display&quot;:{&quot;stackable&quot;:false,&quot;overlay_disabled&quot;:false,&quot;scrollable_content&quot;:false,&quot;disable_reposition&quot;:false,&quot;size&quot;:&quot;medium&quot;,&quot;responsive_min_width&quot;:&quot;0%&quot;,&quot;responsive_min_width_unit&quot;:false,&quot;responsive_max_width&quot;:&quot;100%&quot;,&quot;responsive_max_width_unit&quot;:false,&quot;custom_width&quot;:&quot;640px&quot;,&quot;custom_width_unit&quot;:false,&quot;custom_height&quot;:&quot;380px&quot;,&quot;custom_height_unit&quot;:false,&quot;custom_height_auto&quot;:false,&quot;location&quot;:&quot;center top&quot;,&quot;position_from_trigger&quot;:false,&quot;position_top&quot;:&quot;100&quot;,&quot;position_left&quot;:&quot;0&quot;,&quot;position_bottom&quot;:&quot;0&quot;,&quot;position_right&quot;:&quot;0&quot;,&quot;position_fixed&quot;:false,&quot;animation_type&quot;:&quot;fade&quot;,&quot;animation_speed&quot;:&quot;350&quot;,&quot;animation_origin&quot;:&quot;center top&quot;,&quot;overlay_zindex&quot;:false,&quot;zindex&quot;:&quot;1999999999&quot;},&quot;close&quot;:{&quot;text&quot;:&quot;&quot;,&quot;button_delay&quot;:&quot;0&quot;,&quot;overlay_click&quot;:false,&quot;esc_press&quot;:false,&quot;f4_press&quot;:false},&quot;click_open&quot;:[]}}"
        role="dialog" aria-hidden="true">

        <div id="popmake-236040"
            class="pum-container popmake theme-235878 pum-responsive pum-responsive-medium responsive size-medium">







            <div class="pum-content popmake-content" tabindex="0">
                <div data-elementor-type="wp-post" data-elementor-id="236038" class="elementor elementor-236038">
                    <div class="elementor-inner">
                        <div class="elementor-section-wrap">
                            <section
                                class="has_eae_slider elementor-section elementor-top-section elementor-element elementor-element-3d0565c7 elementor-section-boxed elementor-section-height-default elementor-section-height-default"
                                data-id="3d0565c7" data-element_type="section">
                                <div class="elementor-container elementor-column-gap-default">
                                    <div class="elementor-row">
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-636c9b8b"
                                            data-id="636c9b8b" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-305e9eae elementor-widget elementor-widget-heading"
                                                        data-id="305e9eae" data-element_type="widget"
                                                        data-widget_type="heading.default">
                                                        <div class="elementor-widget-container">
                                                            <h2
                                                                class="elementor-heading-title elementor-size-default">
                                                                Opening Hours</h2>
                                                        </div>
                                                    </div>
                                                    <div class="elementor-element elementor-element-0808bf9 elementor-widget elementor-widget-eae-data-table"
                                                        data-id="0808bf9" data-element_type="widget"
                                                        data-widget_type="eae-data-table.default">
                                                        <div class="elementor-widget-container">

                                                            <div class="eae-table-container">
                                                                <div class="eae-table-wrapper">
                                                                    <div class="eae-table-wrap">

                                                                        <table class="eae-table"
                                                                            data-settings="{&quot;sort&quot;:false,&quot;search&quot;:false}">

                                                                            <thead class="eae-table-head">
                                                                                <tr class="eae-table-row">

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-253a221">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Day</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-c4ba098">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Time</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>
                                                                            </thead>
                                                                            <tbody class="eae-table__body">
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f2f570b"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Mon</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2f6ca86"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3:30pm
                                                                                                    - 9:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2b03c3e"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Tue</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-fb9d87a"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3:30pm
                                                                                                    - 9:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-48cc257"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Fri</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-00d4e19"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3:00pm
                                                                                                    - 9:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f292d29"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Sat</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-83231a8"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">9:00am
                                                                                                    - 3:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-50450dc5"
                                            data-id="50450dc5" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-3f3b767e elementor-widget elementor-widget-google_maps"
                                                        data-id="3f3b767e" data-element_type="widget"
                                                        data-widget_type="google_maps.default">
                                                        <div class="elementor-widget-container">
                                                            <div class="elementor-custom-embed">
                                                                <iframe frameborder="0" scrolling="no"
                                                                    marginheight="0" marginwidth="0"
                                                                    src="https://maps.google.com/maps?q=eiMaths%40Aljunied&amp;t=m&amp;z=17&amp;output=embed&amp;iwloc=near"
                                                                    title="eiMaths@Aljunied"
                                                                    aria-label="eiMaths@Aljunied"></iframe>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

            </div>




            <button type="button" class="pum-close popmake-close" aria-label="Close">
                CLOSE </button>

        </div>

    </div>
    <div id="pum-236029" class="pum pum-overlay pum-theme-235878 pum-theme-default-theme popmake-overlay click_open"
        data-popmake="{&quot;id&quot;:236029,&quot;slug&quot;:&quot;yishunring&quot;,&quot;theme_id&quot;:235878,&quot;cookies&quot;:[],&quot;triggers&quot;:[{&quot;type&quot;:&quot;click_open&quot;,&quot;settings&quot;:{&quot;extra_selectors&quot;:&quot;#yishunring&quot;}}],&quot;mobile_disabled&quot;:null,&quot;tablet_disabled&quot;:null,&quot;meta&quot;:{&quot;display&quot;:{&quot;stackable&quot;:false,&quot;overlay_disabled&quot;:false,&quot;scrollable_content&quot;:false,&quot;disable_reposition&quot;:false,&quot;size&quot;:&quot;medium&quot;,&quot;responsive_min_width&quot;:&quot;0%&quot;,&quot;responsive_min_width_unit&quot;:false,&quot;responsive_max_width&quot;:&quot;100%&quot;,&quot;responsive_max_width_unit&quot;:false,&quot;custom_width&quot;:&quot;640px&quot;,&quot;custom_width_unit&quot;:false,&quot;custom_height&quot;:&quot;380px&quot;,&quot;custom_height_unit&quot;:false,&quot;custom_height_auto&quot;:false,&quot;location&quot;:&quot;center top&quot;,&quot;position_from_trigger&quot;:false,&quot;position_top&quot;:&quot;100&quot;,&quot;position_left&quot;:&quot;0&quot;,&quot;position_bottom&quot;:&quot;0&quot;,&quot;position_right&quot;:&quot;0&quot;,&quot;position_fixed&quot;:false,&quot;animation_type&quot;:&quot;fade&quot;,&quot;animation_speed&quot;:&quot;350&quot;,&quot;animation_origin&quot;:&quot;center top&quot;,&quot;overlay_zindex&quot;:false,&quot;zindex&quot;:&quot;1999999999&quot;},&quot;close&quot;:{&quot;text&quot;:&quot;&quot;,&quot;button_delay&quot;:&quot;0&quot;,&quot;overlay_click&quot;:false,&quot;esc_press&quot;:false,&quot;f4_press&quot;:false},&quot;click_open&quot;:[]}}"
        role="dialog" aria-hidden="true">

        <div id="popmake-236029"
            class="pum-container popmake theme-235878 pum-responsive pum-responsive-medium responsive size-medium">







            <div class="pum-content popmake-content" tabindex="0">
                <div data-elementor-type="wp-post" data-elementor-id="236020" class="elementor elementor-236020">
                    <div class="elementor-inner">
                        <div class="elementor-section-wrap">
                            <section
                                class="has_eae_slider elementor-section elementor-top-section elementor-element elementor-element-3d0565c7 elementor-section-boxed elementor-section-height-default elementor-section-height-default"
                                data-id="3d0565c7" data-element_type="section">
                                <div class="elementor-container elementor-column-gap-default">
                                    <div class="elementor-row">
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-636c9b8b"
                                            data-id="636c9b8b" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-305e9eae elementor-widget elementor-widget-heading"
                                                        data-id="305e9eae" data-element_type="widget"
                                                        data-widget_type="heading.default">
                                                        <div class="elementor-widget-container">
                                                            <h2
                                                                class="elementor-heading-title elementor-size-default">
                                                                Opening Hours</h2>
                                                        </div>
                                                    </div>
                                                    <div class="elementor-element elementor-element-b292000 elementor-widget elementor-widget-eae-data-table"
                                                        data-id="b292000" data-element_type="widget"
                                                        data-widget_type="eae-data-table.default">
                                                        <div class="elementor-widget-container">

                                                            <div class="eae-table-container">
                                                                <div class="eae-table-wrapper">
                                                                    <div class="eae-table-wrap">

                                                                        <table class="eae-table"
                                                                            data-settings="{&quot;sort&quot;:false,&quot;search&quot;:false}">

                                                                            <thead class="eae-table-head">
                                                                                <tr class="eae-table-row">

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-253a221">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Day</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-c4ba098">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Time</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>
                                                                            </thead>
                                                                            <tbody class="eae-table__body">
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2b03c3e"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Wed</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-fb9d87a"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3.30pm
                                                                                                    - 9.00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f2f570b"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Thu</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2f6ca86"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3.30pm
                                                                                                    - 9.00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-48cc257"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Fri</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-00d4e19"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3.30pm
                                                                                                    - 9.00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f292d29"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Sat</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-83231a8"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">9.30am
                                                                                                    - 3.30pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-895a361"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Sun</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-622a482"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">10.00am
                                                                                                    - 1.00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-50450dc5"
                                            data-id="50450dc5" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-3f3b767e elementor-widget elementor-widget-google_maps"
                                                        data-id="3f3b767e" data-element_type="widget"
                                                        data-widget_type="google_maps.default">
                                                        <div class="elementor-widget-container">
                                                            <div class="elementor-custom-embed">
                                                                <iframe frameborder="0" scrolling="no"
                                                                    marginheight="0" marginwidth="0"
                                                                    src="https://maps.google.com/maps?q=eiMaths%40Khatib&amp;t=m&amp;z=17&amp;output=embed&amp;iwloc=near"
                                                                    title="eiMaths@Khatib"
                                                                    aria-label="eiMaths@Khatib"></iframe>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

            </div>




            <button type="button" class="pum-close popmake-close" aria-label="Close">
                CLOSE </button>

        </div>

    </div>
    <div id="pum-236015" class="pum pum-overlay pum-theme-235878 pum-theme-default-theme popmake-overlay click_open"
        data-popmake="{&quot;id&quot;:236015,&quot;slug&quot;:&quot;admiralty&quot;,&quot;theme_id&quot;:235878,&quot;cookies&quot;:[],&quot;triggers&quot;:[{&quot;type&quot;:&quot;click_open&quot;,&quot;settings&quot;:{&quot;extra_selectors&quot;:&quot;#admiralty&quot;}}],&quot;mobile_disabled&quot;:null,&quot;tablet_disabled&quot;:null,&quot;meta&quot;:{&quot;display&quot;:{&quot;stackable&quot;:false,&quot;overlay_disabled&quot;:false,&quot;scrollable_content&quot;:false,&quot;disable_reposition&quot;:false,&quot;size&quot;:&quot;medium&quot;,&quot;responsive_min_width&quot;:&quot;0%&quot;,&quot;responsive_min_width_unit&quot;:false,&quot;responsive_max_width&quot;:&quot;100%&quot;,&quot;responsive_max_width_unit&quot;:false,&quot;custom_width&quot;:&quot;640px&quot;,&quot;custom_width_unit&quot;:false,&quot;custom_height&quot;:&quot;380px&quot;,&quot;custom_height_unit&quot;:false,&quot;custom_height_auto&quot;:false,&quot;location&quot;:&quot;center top&quot;,&quot;position_from_trigger&quot;:false,&quot;position_top&quot;:&quot;100&quot;,&quot;position_left&quot;:&quot;0&quot;,&quot;position_bottom&quot;:&quot;0&quot;,&quot;position_right&quot;:&quot;0&quot;,&quot;position_fixed&quot;:false,&quot;animation_type&quot;:&quot;fade&quot;,&quot;animation_speed&quot;:&quot;350&quot;,&quot;animation_origin&quot;:&quot;center top&quot;,&quot;overlay_zindex&quot;:false,&quot;zindex&quot;:&quot;1999999999&quot;},&quot;close&quot;:{&quot;text&quot;:&quot;&quot;,&quot;button_delay&quot;:&quot;0&quot;,&quot;overlay_click&quot;:false,&quot;esc_press&quot;:false,&quot;f4_press&quot;:false},&quot;click_open&quot;:[]}}"
        role="dialog" aria-hidden="true">

        <div id="popmake-236015"
            class="pum-container popmake theme-235878 pum-responsive pum-responsive-medium responsive size-medium">







            <div class="pum-content popmake-content" tabindex="0">
                <div data-elementor-type="wp-post" data-elementor-id="236008" class="elementor elementor-236008">
                    <div class="elementor-inner">
                        <div class="elementor-section-wrap">
                            <section
                                class="has_eae_slider elementor-section elementor-top-section elementor-element elementor-element-3d0565c7 elementor-section-boxed elementor-section-height-default elementor-section-height-default"
                                data-id="3d0565c7" data-element_type="section">
                                <div class="elementor-container elementor-column-gap-default">
                                    <div class="elementor-row">
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-636c9b8b"
                                            data-id="636c9b8b" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-305e9eae elementor-widget elementor-widget-heading"
                                                        data-id="305e9eae" data-element_type="widget"
                                                        data-widget_type="heading.default">
                                                        <div class="elementor-widget-container">
                                                            <h2
                                                                class="elementor-heading-title elementor-size-default">
                                                                Opening Hours</h2>
                                                        </div>
                                                    </div>
                                                    <div class="elementor-element elementor-element-d98500c elementor-widget elementor-widget-eae-data-table"
                                                        data-id="d98500c" data-element_type="widget"
                                                        data-widget_type="eae-data-table.default">
                                                        <div class="elementor-widget-container">

                                                            <div class="eae-table-container">
                                                                <div class="eae-table-wrapper">
                                                                    <div class="eae-table-wrap">

                                                                        <table class="eae-table"
                                                                            data-settings="{&quot;sort&quot;:false,&quot;search&quot;:false}">

                                                                            <thead class="eae-table-head">
                                                                                <tr class="eae-table-row">

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-253a221">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Day</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-c4ba098">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Time</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>
                                                                            </thead>
                                                                            <tbody class="eae-table__body">
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f2f570b"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Mon</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2f6ca86"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">5:00pm
                                                                                                    - 9:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-48cc257"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Wed</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-00d4e19"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3:30pm
                                                                                                    - 7:30pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f292d29"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Sat</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-83231a8"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">4:00pm
                                                                                                    - 6:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-50450dc5"
                                            data-id="50450dc5" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-3f3b767e elementor-widget elementor-widget-google_maps"
                                                        data-id="3f3b767e" data-element_type="widget"
                                                        data-widget_type="google_maps.default">
                                                        <div class="elementor-widget-container">
                                                            <div class="elementor-custom-embed">
                                                                <iframe frameborder="0" scrolling="no"
                                                                    marginheight="0" marginwidth="0"
                                                                    src="https://maps.google.com/maps?q=eiMaths%40Admiralty&amp;t=m&amp;z=17&amp;output=embed&amp;iwloc=near"
                                                                    title="eiMaths@Admiralty"
                                                                    aria-label="eiMaths@Admiralty"></iframe>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

            </div>




            <button type="button" class="pum-close popmake-close" aria-label="Close">
                CLOSE </button>

        </div>

    </div>
    <div id="pum-235999" class="pum pum-overlay pum-theme-235878 pum-theme-default-theme popmake-overlay click_open"
        data-popmake="{&quot;id&quot;:235999,&quot;slug&quot;:&quot;woodland-2&quot;,&quot;theme_id&quot;:235878,&quot;cookies&quot;:[],&quot;triggers&quot;:[{&quot;type&quot;:&quot;click_open&quot;,&quot;settings&quot;:{&quot;extra_selectors&quot;:&quot;#woodland2&quot;}}],&quot;mobile_disabled&quot;:null,&quot;tablet_disabled&quot;:null,&quot;meta&quot;:{&quot;display&quot;:{&quot;stackable&quot;:false,&quot;overlay_disabled&quot;:false,&quot;scrollable_content&quot;:false,&quot;disable_reposition&quot;:false,&quot;size&quot;:&quot;medium&quot;,&quot;responsive_min_width&quot;:&quot;0%&quot;,&quot;responsive_min_width_unit&quot;:false,&quot;responsive_max_width&quot;:&quot;100%&quot;,&quot;responsive_max_width_unit&quot;:false,&quot;custom_width&quot;:&quot;640px&quot;,&quot;custom_width_unit&quot;:false,&quot;custom_height&quot;:&quot;380px&quot;,&quot;custom_height_unit&quot;:false,&quot;custom_height_auto&quot;:false,&quot;location&quot;:&quot;center top&quot;,&quot;position_from_trigger&quot;:false,&quot;position_top&quot;:&quot;100&quot;,&quot;position_left&quot;:&quot;0&quot;,&quot;position_bottom&quot;:&quot;0&quot;,&quot;position_right&quot;:&quot;0&quot;,&quot;position_fixed&quot;:false,&quot;animation_type&quot;:&quot;fade&quot;,&quot;animation_speed&quot;:&quot;350&quot;,&quot;animation_origin&quot;:&quot;center top&quot;,&quot;overlay_zindex&quot;:false,&quot;zindex&quot;:&quot;1999999999&quot;},&quot;close&quot;:{&quot;text&quot;:&quot;&quot;,&quot;button_delay&quot;:&quot;0&quot;,&quot;overlay_click&quot;:false,&quot;esc_press&quot;:false,&quot;f4_press&quot;:false},&quot;click_open&quot;:[]}}"
        role="dialog" aria-hidden="true">

        <div id="popmake-235999"
            class="pum-container popmake theme-235878 pum-responsive pum-responsive-medium responsive size-medium">







            <div class="pum-content popmake-content" tabindex="0">
                <div data-elementor-type="wp-post" data-elementor-id="235992" class="elementor elementor-235992">
                    <div class="elementor-inner">
                        <div class="elementor-section-wrap">
                            <section
                                class="has_eae_slider elementor-section elementor-top-section elementor-element elementor-element-5f603bba elementor-section-boxed elementor-section-height-default elementor-section-height-default"
                                data-id="5f603bba" data-element_type="section">
                                <div class="elementor-container elementor-column-gap-default">
                                    <div class="elementor-row">
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-20ad10e6"
                                            data-id="20ad10e6" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-3326ce91 elementor-widget elementor-widget-heading"
                                                        data-id="3326ce91" data-element_type="widget"
                                                        data-widget_type="heading.default">
                                                        <div class="elementor-widget-container">
                                                            <h2
                                                                class="elementor-heading-title elementor-size-default">
                                                                Opening Hours</h2>
                                                        </div>
                                                    </div>
                                                    <div class="elementor-element elementor-element-ca38c00 elementor-widget elementor-widget-eae-data-table"
                                                        data-id="ca38c00" data-element_type="widget"
                                                        data-widget_type="eae-data-table.default">
                                                        <div class="elementor-widget-container">

                                                            <div class="eae-table-container">
                                                                <div class="eae-table-wrapper">
                                                                    <div class="eae-table-wrap">

                                                                        <table class="eae-table"
                                                                            data-settings="{&quot;sort&quot;:false,&quot;search&quot;:false}">

                                                                            <thead class="eae-table-head">
                                                                                <tr class="eae-table-row">

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-253a221">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Day</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-c4ba098">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Time</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>
                                                                            </thead>
                                                                            <tbody class="eae-table__body">
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2b03c3e"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Tue</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-fb9d87a"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">7:00pm
                                                                                                    - 9:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f292d29"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Sat</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-83231a8"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">4:00pm
                                                                                                    - 6:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-46eb8fb3"
                                            data-id="46eb8fb3" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-7e728684 elementor-widget elementor-widget-google_maps"
                                                        data-id="7e728684" data-element_type="widget"
                                                        data-widget_type="google_maps.default">
                                                        <div class="elementor-widget-container">
                                                            <div class="elementor-custom-embed">
                                                                <iframe frameborder="0" scrolling="no"
                                                                    marginheight="0" marginwidth="0"
                                                                    src="https://maps.google.com/maps?q=eimaths%40Woodlands%20Drive&amp;t=m&amp;z=17&amp;output=embed&amp;iwloc=near"
                                                                    title="eimaths@Woodlands Drive"
                                                                    aria-label="eimaths@Woodlands Drive"></iframe>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

            </div>




            <button type="button" class="pum-close popmake-close" aria-label="Close">
                CLOSE </button>

        </div>

    </div>
    <div id="pum-235978" class="pum pum-overlay pum-theme-235878 pum-theme-default-theme popmake-overlay click_open"
        data-popmake="{&quot;id&quot;:235978,&quot;slug&quot;:&quot;woodland-1&quot;,&quot;theme_id&quot;:235878,&quot;cookies&quot;:[],&quot;triggers&quot;:[{&quot;type&quot;:&quot;click_open&quot;,&quot;settings&quot;:{&quot;extra_selectors&quot;:&quot;#woodland1&quot;}}],&quot;mobile_disabled&quot;:null,&quot;tablet_disabled&quot;:null,&quot;meta&quot;:{&quot;display&quot;:{&quot;stackable&quot;:false,&quot;overlay_disabled&quot;:false,&quot;scrollable_content&quot;:false,&quot;disable_reposition&quot;:false,&quot;size&quot;:&quot;medium&quot;,&quot;responsive_min_width&quot;:&quot;0%&quot;,&quot;responsive_min_width_unit&quot;:false,&quot;responsive_max_width&quot;:&quot;100%&quot;,&quot;responsive_max_width_unit&quot;:false,&quot;custom_width&quot;:&quot;640px&quot;,&quot;custom_width_unit&quot;:false,&quot;custom_height&quot;:&quot;380px&quot;,&quot;custom_height_unit&quot;:false,&quot;custom_height_auto&quot;:false,&quot;location&quot;:&quot;center top&quot;,&quot;position_from_trigger&quot;:false,&quot;position_top&quot;:&quot;100&quot;,&quot;position_left&quot;:&quot;0&quot;,&quot;position_bottom&quot;:&quot;0&quot;,&quot;position_right&quot;:&quot;0&quot;,&quot;position_fixed&quot;:false,&quot;animation_type&quot;:&quot;fade&quot;,&quot;animation_speed&quot;:&quot;350&quot;,&quot;animation_origin&quot;:&quot;center top&quot;,&quot;overlay_zindex&quot;:false,&quot;zindex&quot;:&quot;1999999999&quot;},&quot;close&quot;:{&quot;text&quot;:&quot;&quot;,&quot;button_delay&quot;:&quot;0&quot;,&quot;overlay_click&quot;:false,&quot;esc_press&quot;:false,&quot;f4_press&quot;:false},&quot;click_open&quot;:[]}}"
        role="dialog" aria-hidden="true">

        <div id="popmake-235978"
            class="pum-container popmake theme-235878 pum-responsive pum-responsive-medium responsive size-medium">







            <div class="pum-content popmake-content" tabindex="0">
                <div data-elementor-type="wp-post" data-elementor-id="235971" class="elementor elementor-235971">
                    <div class="elementor-inner">
                        <div class="elementor-section-wrap">
                            <section
                                class="has_eae_slider elementor-section elementor-top-section elementor-element elementor-element-4c6ca61f elementor-section-boxed elementor-section-height-default elementor-section-height-default"
                                data-id="4c6ca61f" data-element_type="section">
                                <div class="elementor-container elementor-column-gap-default">
                                    <div class="elementor-row">
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-7c6e7b22"
                                            data-id="7c6e7b22" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-7713b11f elementor-widget elementor-widget-heading"
                                                        data-id="7713b11f" data-element_type="widget"
                                                        data-widget_type="heading.default">
                                                        <div class="elementor-widget-container">
                                                            <h2
                                                                class="elementor-heading-title elementor-size-default">
                                                                Opening Hours</h2>
                                                        </div>
                                                    </div>
                                                    <div class="elementor-element elementor-element-676a3c6 elementor-widget elementor-widget-eae-data-table"
                                                        data-id="676a3c6" data-element_type="widget"
                                                        data-widget_type="eae-data-table.default">
                                                        <div class="elementor-widget-container">

                                                            <div class="eae-table-container">
                                                                <div class="eae-table-wrapper">
                                                                    <div class="eae-table-wrap">

                                                                        <table class="eae-table"
                                                                            data-settings="{&quot;sort&quot;:false,&quot;search&quot;:false}">

                                                                            <thead class="eae-table-head">
                                                                                <tr class="eae-table-row">

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-253a221">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Day</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-c4ba098">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Time</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>
                                                                            </thead>
                                                                            <tbody class="eae-table__body">
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f2f570b"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Thu</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2f6ca86"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">7:00pm
                                                                                                    - 9:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f292d29"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Sat</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-83231a8"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">10:00am
                                                                                                    - 2:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-34ce491f"
                                            data-id="34ce491f" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-30bb4df6 elementor-widget elementor-widget-google_maps"
                                                        data-id="30bb4df6" data-element_type="widget"
                                                        data-widget_type="google_maps.default">
                                                        <div class="elementor-widget-container">
                                                            <div class="elementor-custom-embed">
                                                                <iframe frameborder="0" scrolling="no"
                                                                    marginheight="0" marginwidth="0"
                                                                    src="https://maps.google.com/maps?q=eiMaths%40Woodlands%20Blk363&amp;t=m&amp;z=17&amp;output=embed&amp;iwloc=near"
                                                                    title="eiMaths@Woodlands Blk363"
                                                                    aria-label="eiMaths@Woodlands Blk363"></iframe>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

            </div>




            <button type="button" class="pum-close popmake-close" aria-label="Close">
                CLOSE </button>

        </div>

    </div>
    <div id="pum-235952" class="pum pum-overlay pum-theme-235878 pum-theme-default-theme popmake-overlay click_open"
        data-popmake="{&quot;id&quot;:235952,&quot;slug&quot;:&quot;tiong-bahru&quot;,&quot;theme_id&quot;:235878,&quot;cookies&quot;:[],&quot;triggers&quot;:[{&quot;type&quot;:&quot;click_open&quot;,&quot;settings&quot;:{&quot;extra_selectors&quot;:&quot;#tiongbahru&quot;}}],&quot;mobile_disabled&quot;:null,&quot;tablet_disabled&quot;:null,&quot;meta&quot;:{&quot;display&quot;:{&quot;stackable&quot;:false,&quot;overlay_disabled&quot;:false,&quot;scrollable_content&quot;:false,&quot;disable_reposition&quot;:false,&quot;size&quot;:&quot;medium&quot;,&quot;responsive_min_width&quot;:&quot;0%&quot;,&quot;responsive_min_width_unit&quot;:false,&quot;responsive_max_width&quot;:&quot;100%&quot;,&quot;responsive_max_width_unit&quot;:false,&quot;custom_width&quot;:&quot;640px&quot;,&quot;custom_width_unit&quot;:false,&quot;custom_height&quot;:&quot;380px&quot;,&quot;custom_height_unit&quot;:false,&quot;custom_height_auto&quot;:false,&quot;location&quot;:&quot;center top&quot;,&quot;position_from_trigger&quot;:false,&quot;position_top&quot;:&quot;100&quot;,&quot;position_left&quot;:&quot;0&quot;,&quot;position_bottom&quot;:&quot;0&quot;,&quot;position_right&quot;:&quot;0&quot;,&quot;position_fixed&quot;:false,&quot;animation_type&quot;:&quot;fade&quot;,&quot;animation_speed&quot;:&quot;350&quot;,&quot;animation_origin&quot;:&quot;center top&quot;,&quot;overlay_zindex&quot;:false,&quot;zindex&quot;:&quot;1999999999&quot;},&quot;close&quot;:{&quot;text&quot;:&quot;&quot;,&quot;button_delay&quot;:&quot;0&quot;,&quot;overlay_click&quot;:false,&quot;esc_press&quot;:false,&quot;f4_press&quot;:false},&quot;click_open&quot;:[]}}"
        role="dialog" aria-hidden="true">

        <div id="popmake-235952"
            class="pum-container popmake theme-235878 pum-responsive pum-responsive-medium responsive size-medium">







            <div class="pum-content popmake-content" tabindex="0">
                <div data-elementor-type="wp-post" data-elementor-id="235946" class="elementor elementor-235946">
                    <div class="elementor-inner">
                        <div class="elementor-section-wrap">
                            <section
                                class="has_eae_slider elementor-section elementor-top-section elementor-element elementor-element-16b77bd2 elementor-section-boxed elementor-section-height-default elementor-section-height-default"
                                data-id="16b77bd2" data-element_type="section">
                                <div class="elementor-container elementor-column-gap-default">
                                    <div class="elementor-row">
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-45c65ddf"
                                            data-id="45c65ddf" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-562842fe elementor-widget elementor-widget-heading"
                                                        data-id="562842fe" data-element_type="widget"
                                                        data-widget_type="heading.default">
                                                        <div class="elementor-widget-container">
                                                            <h2
                                                                class="elementor-heading-title elementor-size-default">
                                                                Opening Hours</h2>
                                                        </div>
                                                    </div>
                                                    <div class="elementor-element elementor-element-20e203f elementor-widget elementor-widget-eae-data-table"
                                                        data-id="20e203f" data-element_type="widget"
                                                        data-widget_type="eae-data-table.default">
                                                        <div class="elementor-widget-container">

                                                            <div class="eae-table-container">
                                                                <div class="eae-table-wrapper">
                                                                    <div class="eae-table-wrap">

                                                                        <table class="eae-table"
                                                                            data-settings="{&quot;sort&quot;:false,&quot;search&quot;:false}">

                                                                            <thead class="eae-table-head">
                                                                                <tr class="eae-table-row">

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-253a221">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Day</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-c4ba098">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Time</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>
                                                                            </thead>
                                                                            <tbody class="eae-table__body">
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2b03c3e"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Tue</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-fb9d87a"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">5:00pm
                                                                                                    - 9:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f2f570b"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Wed</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2f6ca86"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">5:00pm
                                                                                                    - 9:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-48cc257"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Fri</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-00d4e19"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">5:00pm
                                                                                                    - 9:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f292d29"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Sat</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-83231a8"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">9:00am
                                                                                                    - 11:00am</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-4e82d71f"
                                            data-id="4e82d71f" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-2925b3dc elementor-widget elementor-widget-google_maps"
                                                        data-id="2925b3dc" data-element_type="widget"
                                                        data-widget_type="google_maps.default">
                                                        <div class="elementor-widget-container">
                                                            <div class="elementor-custom-embed">
                                                                <iframe frameborder="0" scrolling="no"
                                                                    marginheight="0" marginwidth="0"
                                                                    src="https://maps.google.com/maps?q=eiMaths%40Tiong%20Bahru&amp;t=m&amp;z=17&amp;output=embed&amp;iwloc=near"
                                                                    title="eiMaths@Tiong Bahru"
                                                                    aria-label="eiMaths@Tiong Bahru"></iframe>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

            </div>




            <button type="button" class="pum-close popmake-close" aria-label="Close">
                CLOSE </button>

        </div>

    </div>
    <div id="pum-235887" class="pum pum-overlay pum-theme-235878 pum-theme-default-theme popmake-overlay click_open"
        data-popmake="{&quot;id&quot;:235887,&quot;slug&quot;:&quot;toa-payoh&quot;,&quot;theme_id&quot;:235878,&quot;cookies&quot;:[],&quot;triggers&quot;:[{&quot;type&quot;:&quot;click_open&quot;,&quot;settings&quot;:{&quot;extra_selectors&quot;:&quot;#toapayoh&quot;}}],&quot;mobile_disabled&quot;:null,&quot;tablet_disabled&quot;:null,&quot;meta&quot;:{&quot;display&quot;:{&quot;stackable&quot;:false,&quot;overlay_disabled&quot;:false,&quot;scrollable_content&quot;:false,&quot;disable_reposition&quot;:false,&quot;size&quot;:&quot;medium&quot;,&quot;responsive_min_width&quot;:&quot;0%&quot;,&quot;responsive_min_width_unit&quot;:false,&quot;responsive_max_width&quot;:&quot;100%&quot;,&quot;responsive_max_width_unit&quot;:false,&quot;custom_width&quot;:&quot;640px&quot;,&quot;custom_width_unit&quot;:false,&quot;custom_height&quot;:&quot;380px&quot;,&quot;custom_height_unit&quot;:false,&quot;custom_height_auto&quot;:false,&quot;location&quot;:&quot;center top&quot;,&quot;position_from_trigger&quot;:false,&quot;position_top&quot;:&quot;100&quot;,&quot;position_left&quot;:&quot;0&quot;,&quot;position_bottom&quot;:&quot;0&quot;,&quot;position_right&quot;:&quot;0&quot;,&quot;position_fixed&quot;:false,&quot;animation_type&quot;:&quot;fade&quot;,&quot;animation_speed&quot;:&quot;350&quot;,&quot;animation_origin&quot;:&quot;center top&quot;,&quot;overlay_zindex&quot;:false,&quot;zindex&quot;:&quot;1999999999&quot;},&quot;close&quot;:{&quot;text&quot;:&quot;&quot;,&quot;button_delay&quot;:&quot;0&quot;,&quot;overlay_click&quot;:false,&quot;esc_press&quot;:false,&quot;f4_press&quot;:false},&quot;click_open&quot;:[]}}"
        role="dialog" aria-hidden="true">

        <div id="popmake-235887"
            class="pum-container popmake theme-235878 pum-responsive pum-responsive-medium responsive size-medium">







            <div class="pum-content popmake-content" tabindex="0">
                <div data-elementor-type="wp-post" data-elementor-id="235855" class="elementor elementor-235855">
                    <div class="elementor-inner">
                        <div class="elementor-section-wrap">
                            <section
                                class="has_eae_slider elementor-section elementor-top-section elementor-element elementor-element-6185a87 elementor-section-boxed elementor-section-height-default elementor-section-height-default"
                                data-id="6185a87" data-element_type="section">
                                <div class="elementor-container elementor-column-gap-default">
                                    <div class="elementor-row">
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-4117673"
                                            data-id="4117673" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-37b710d elementor-widget elementor-widget-heading"
                                                        data-id="37b710d" data-element_type="widget"
                                                        data-widget_type="heading.default">
                                                        <div class="elementor-widget-container">
                                                            <h2
                                                                class="elementor-heading-title elementor-size-default">
                                                                Opening Hours</h2>
                                                        </div>
                                                    </div>
                                                    <div class="elementor-element elementor-element-13a67d7 elementor-widget elementor-widget-eae-data-table"
                                                        data-id="13a67d7" data-element_type="widget"
                                                        data-widget_type="eae-data-table.default">
                                                        <div class="elementor-widget-container">

                                                            <div class="eae-table-container">
                                                                <div class="eae-table-wrapper">
                                                                    <div class="eae-table-wrap">

                                                                        <table class="eae-table"
                                                                            data-settings="{&quot;sort&quot;:false,&quot;search&quot;:false}">

                                                                            <thead class="eae-table-head">
                                                                                <tr class="eae-table-row">

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-253a221">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Day</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>

                                                                                    <th
                                                                                        class="eae-table__head_column elementor-repeater-item-c4ba098">
                                                                                        <div class="eae-table__head__wrapper"
                                                                                            style="flex-direction: row;">
                                                                                            <div
                                                                                                class="eae-table__head-column-wrapper">

                                                                                                <span
                                                                                                    class="eae-table__column-text">Time</span>


                                                                                            </div>
                                                                                        </div>
                                                                                    </th>
                                                                            </thead>
                                                                            <tbody class="eae-table__body">
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2b03c3e"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Tue</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-fb9d87a"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3:00pm
                                                                                                    - 8:30pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f2f570b"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Thu</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-2f6ca86"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">5:00pm
                                                                                                    - 8:30pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-48cc257"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Fri</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-00d4e19"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">3:00pm
                                                                                                    - 9:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-f292d29"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Sat</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-83231a8"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">4:30pm
                                                                                                    - 6:00pm</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                                </tr>
                                                                                <tr class="eae-table__body-row">

                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-895a361"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">Sun</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>


                                                                                    <td class="eae-table__body_row_column elementor-repeater-item-622a482"
                                                                                        colspan="1"
                                                                                        rowspan="1">
                                                                                        <div
                                                                                            class="eae-table__body-column-wrapper">
                                                                                            <div
                                                                                                class="eae-table__col-inner">
                                                                                                <span
                                                                                                    class="eae-table-body__text">9:00am
                                                                                                    - 10:30am</span>



                                                                                            </div>
                                                                                        </div>
                                                                                    </td>

                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="has_eae_slider elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-cdc651d"
                                            data-id="cdc651d" data-element_type="column">
                                            <div class="elementor-column-wrap elementor-element-populated">
                                                <div class="elementor-widget-wrap">
                                                    <div class="elementor-element elementor-element-cbceb21 elementor-widget elementor-widget-google_maps"
                                                        data-id="cbceb21" data-element_type="widget"
                                                        data-widget_type="google_maps.default">
                                                        <div class="elementor-widget-container">
                                                            <div class="elementor-custom-embed">
                                                                <iframe frameborder="0" scrolling="no"
                                                                    marginheight="0" marginwidth="0"
                                                                    src="https://maps.google.com/maps?q=eiMaths%40Toa%20Payoh&amp;t=m&amp;z=17&amp;output=embed&amp;iwloc=near"
                                                                    title="eiMaths@Toa Payoh"
                                                                    aria-label="eiMaths@Toa Payoh"></iframe>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>

            </div>




            <button type="button" class="pum-close popmake-close" aria-label="Close">
                CLOSE </button>

        </div>

    </div>
    <!-- Meta Pixel Event Code -->
    <script type='text/javascript'>
        document.addEventListener('wpcf7mailsent', function(event) {
            if ("fb_pxl_code" in event.detail.apiResponse) {
                eval(event.detail.apiResponse.fb_pxl_code);
            }
        }, false);
    </script>
    <!-- End Meta Pixel Event Code -->
    <div id='fb-pxl-ajax-code'></div>
    <script type="text/javascript">
        var et_animation_data = [{
            "class": "et_pb_blurb_0",
            "style": "fade",
            "repeat": "once",
            "duration": "1000ms",
            "delay": "0ms",
            "intensity": "50%",
            "starting_opacity": "0%",
            "speed_curve": "ease-in-out"
        }, {
            "class": "et_pb_blurb_1",
            "style": "fade",
            "repeat": "once",
            "duration": "1000ms",
            "delay": "0ms",
            "intensity": "50%",
            "starting_opacity": "0%",
            "speed_curve": "ease-in-out"
        }, {
            "class": "et_pb_blurb_2",
            "style": "fade",
            "repeat": "once",
            "duration": "1000ms",
            "delay": "0ms",
            "intensity": "50%",
            "starting_opacity": "0%",
            "speed_curve": "ease-in-out"
        }, {
            "class": "et_pb_code_0",
            "style": "zoom",
            "repeat": "once",
            "duration": "1100ms",
            "delay": "0ms",
            "intensity": "50%",
            "starting_opacity": "0%",
            "speed_curve": "ease-in-out"
        }, {
            "class": "et_pb_code_0_tb_footer",
            "style": "zoom",
            "repeat": "once",
            "duration": "1100ms",
            "delay": "0ms",
            "intensity": "50%",
            "starting_opacity": "0%",
            "speed_curve": "ease-in-out"
        }];
        var et_link_options_data = [{
            "class": "et_pb_blurb_0",
            "url": "tel:+66612531717",
            "target": "_self"
        }, {
            "class": "et_pb_blurb_1",
            "url": "mailto:info@eimaths-th.com",
            "target": "_self"
        }, {
            "class": "et_pb_blurb_2",
            "url": "https://goo.gl/maps/CiTU6q4waCMYdiFH9",
            "target": "_blank"
        }];
    </script>
    <script type="text/javascript">
        (function() {
            var c = document.body.className;
            c = c.replace(/woocommerce-no-js/, 'woocommerce-js');
            document.body.className = c;
        })();
    </script>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            jQuery(".et-social-icon a").attr('target', 'blank');
        });
    </script>
    <link rel='stylesheet' id='e-animations-css'
        href='https://www.eimaths-th.com/wp-content/plugins/elementor/assets/lib/animations/animations.min2e46.css?ver=3.9.2'
        type='text/css' media='all' />
    <script type='text/javascript' id='eae-main-js-extra'>
        /* <![CDATA[ */
        var eae = {
            "ajaxurl": "https:\/\/eimaths.com\/wp-admin\/admin-ajax.php",
            "current_url": "aHR0cHM6Ly9laW1hdGhzLmNvbS9jb250YWN0LXVzLw==",
            "breakpoints": {
                "xs": 0,
                "sm": 480,
                "md": 768,
                "lg": 1025,
                "xl": 1440,
                "xxl": 1600
            }
        };
        var eae_editor = {
            "plugin_url": "https:\/\/eimaths.com\/wp-content\/plugins\/addon-elements-for-elementor-page-builder\/"
        };
        /* ]]> */
    </script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/plugins/addon-elements-for-elementor-page-builder/assets/js/eae.min8392.js?ver=1.11.16'
        id='eae-main-js'></script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/plugins/elementor/assets/lib/font-awesome/js/v4-shims.min5152.js?ver=1.0'
        id='font-awesome-4-shim-js'></script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/plugins/addon-elements-for-elementor-page-builder/assets/js/animated-main.min5152.js?ver=1.0'
        id='animated-main-js'></script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/plugins/addon-elements-for-elementor-page-builder/assets/js/particles.min001e.js?ver=2.0.0'
        id='eae-particles-js'></script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/plugins/addon-elements-for-elementor-page-builder/assets/lib/magnific.minf488.js?ver=1.1.0'
        id='wts-magnific-js'></script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/plugins/addon-elements-for-elementor-page-builder/assets/lib/vegas/vegas.min8d5a.js?ver=2.4.0'
        id='vegas-js'></script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/plugins/honeypot/includes/js/wpa4c56.js?ver=2.0.2' id='wpascript-js'>
    </script>
    {{-- <script type='text/javascript' id='wpascript-js-after'>
        wpa_field_info = {
            "wpa_field_name": "wpqdnb2654",
            "wpa_field_value": 885250,
            "wpa_add_test": "no"
        }
    </script> --}}
    <script type='text/javascript' src='https://www.eimaths-th.com/wp-includes/js/imagesloaded.mineda1.js?ver=4.1.4'
        id='imagesloaded-js'></script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/plugins/testimonial-pro/public/assets/js/jquery.validate.min5bf8.js?ver=2.2.5'
        id='tpro-validate-js-js'></script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/plugins/testimonial-pro/public/assets/js/jquery.magnific-popup.min5bf8.js?ver=2.2.5'
        id='tpro-magnific-popup-js-js'></script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/plugins/testimonial-pro/public/assets/js/infinite-scroll.pkgd.min5bf8.js?ver=2.2.5'
        id='tpro-infite-scroll-js'></script>
    <script type='text/javascript' src='https://www.eimaths-th.com/wp-includes/js/masonry.min3a05.js?ver=4.2.2'
        id='masonry-js'></script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-includes/js/jquery/jquery.masonry.minef70.js?ver=3.1.2b' id='jquery-masonry-js'>
    </script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/plugins/testimonial-pro/public/assets/js/scripts.min5bf8.js?ver=2.2.5'
        id='tpro-scripts-js'></script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/plugins/woocommerce/assets/js/jquery-blockui/jquery.blockUI.min8bd8.js?ver=2.7.0-wc.7.2.2'
        id='jquery-blockui-js'></script>
    <script type='text/javascript' id='wc-add-to-cart-js-extra'>
        /* <![CDATA[ */
        var wc_add_to_cart_params = {
            "ajax_url": "\/wp-admin\/admin-ajax.php",
            "wc_ajax_url": "\/?wc-ajax=%%endpoint%%",
            "i18n_view_cart": "View cart",
            "cart_url": "https:\/\/eimaths.com\/cart\/",
            "is_cart": "",
            "cart_redirect_after_add": "yes"
        };
        /* ]]> */
    </script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/plugins/woocommerce/assets/js/frontend/add-to-cart.mincb57.js?ver=7.2.2'
        id='wc-add-to-cart-js'></script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/plugins/woocommerce/assets/js/js-cookie/js.cookie.min5f13.js?ver=2.1.4-wc.7.2.2'
        id='js-cookie-js'></script>
    <script type='text/javascript' id='woocommerce-js-extra'>
        /* <![CDATA[ */
        var woocommerce_params = {
            "ajax_url": "\/wp-admin\/admin-ajax.php",
            "wc_ajax_url": "\/?wc-ajax=%%endpoint%%"
        };
        /* ]]> */
    </script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/plugins/woocommerce/assets/js/frontend/woocommerce.mincb57.js?ver=7.2.2'
        id='woocommerce-js'></script>
    <script type='text/javascript' id='wc-cart-fragments-js-extra'>
        /* <![CDATA[ */
        var wc_cart_fragments_params = {
            "ajax_url": "\/wp-admin\/admin-ajax.php",
            "wc_ajax_url": "\/?wc-ajax=%%endpoint%%",
            "cart_hash_key": "wc_cart_hash_bcde66f9f64cc6df34c36813e19e3513",
            "fragment_name": "wc_fragments_bcde66f9f64cc6df34c36813e19e3513",
            "request_timeout": "5000"
        };
        /* ]]> */
    </script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/plugins/woocommerce/assets/js/frontend/cart-fragments.mincb57.js?ver=7.2.2'
        id='wc-cart-fragments-js'></script>
    <script type='text/javascript' id='divi-custom-script-js-extra'>
        /* <![CDATA[ */
        var DIVI = {
            "item_count": "%d Item",
            "items_count": "%d Items"
        };
        var et_builder_utils_params = {
            "condition": {
                "diviTheme": true,
                "extraTheme": false
            },
            "scrollLocations": ["app", "top"],
            "builderScrollLocations": {
                "desktop": "app",
                "tablet": "app",
                "phone": "app"
            },
            "onloadScrollLocation": "app",
            "builderType": "fe"
        };
        var et_frontend_scripts = {
            "builderCssContainerPrefix": "#et-boc",
            "builderCssLayoutPrefix": "#et-boc .et-l"
        };
        var et_pb_custom = {

            "ajaxurl": "https:\/\/eimaths.com\/wp-admin\/admin-ajax.php",
            "images_uri": "https:\/\/eimaths.com\/wp-content\/themes\/Divi\/images",
            "builder_images_uri": "https:\/\/eimaths.com\/wp-content\/themes\/Divi\/includes\/builder\/images",
            "et_frontend_nonce": "ea1eb06f18",
            "subscription_failed": "Please, check the fields below to make sure you entered the correct information.",
            "et_ab_log_nonce": "1133b4fe76",
            "fill_message": "Please, fill in the following fields:",
            "contact_error_message": "Please, fix the following errors:",
            "invalid": "Invalid email",
            "captcha": "Captcha",
            "prev": "Prev",
            "previous": "Previous",
            "next": "Next",
            "wrong_captcha": "You entered the wrong number in captcha.",
            "wrong_checkbox": "Checkbox",
            "ignore_waypoints": "no",
            "is_divi_theme_used": "1",
            "widget_search_selector": ".widget_search",
            "ab_tests": [],
            "is_ab_testing_active": "",
            "page_id": "260",
            "unique_test_id": "",
            "ab_bounce_rate": "5",
            "is_cache_plugin_active": "yes",
            "is_shortcode_tracking": "",
            "tinymce_uri": "https:\/\/eimaths.com\/wp-content\/themes\/Divi\/includes\/builder\/frontend-builder\/assets\/vendors",
            "accent_color": "#2ea3f2",
            "waypoints_options": {
                "context": [".pum-overlay"]
            }
        };
        var et_pb_box_shadow_elements = [];
        /* ]]> */
    </script>
    <script type='text/javascript' src='https://www.eimaths-th.com/wp-content/themes/Divi/js/scripts.min0349.js?ver=4.19.1'
        id='divi-custom-script-js'></script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/plugins/addon-elements-for-elementor-page-builder/assets/lib/tablesorter/tablesorterfa23.js?ver=2.31.3'
        id='eae-data-table-js'></script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/plugins/addon-elements-for-elementor-page-builder/assets/lib/lottie/lottie.min76f9.js?ver=5.6.8'
        id='eae-lottie-js'></script>
    <script type='text/javascript' src='https://www.eimaths-th.com/wp-includes/js/jquery/ui/core.min0028.js?ver=1.13.1'
        id='jquery-ui-core-js'></script>
    <script type='text/javascript' id='popup-maker-site-js-extra'>
        /* <![CDATA[ */
        var pum_vars = {
            "version": "1.17.1",
            "pm_dir_url": "https:\/\/eimaths.com\/wp-content\/plugins\/popup-maker\/",
            "ajaxurl": "https:\/\/eimaths.com\/wp-admin\/admin-ajax.php",
            "restapi": "https:\/\/eimaths.com\/wp-json\/pum\/v1",
            "rest_nonce": null,
            "default_theme": "235878",
            "debug_mode": "",
            "disable_tracking": "",
            "home_url": "\/",
            "message_position": "top",
            "core_sub_forms_enabled": "1",
            "popups": [],
            "analytics_route": "analytics",
            "analytics_api": "https:\/\/eimaths.com\/wp-json\/pum\/v1"
        };
        var pum_sub_vars = {
            "ajaxurl": "https:\/\/eimaths.com\/wp-admin\/admin-ajax.php",
            "message_position": "top"
        };
        var pum_popups = {
            "pum-241233": {
                "triggers": [{
                    "type": "click_open",
                    "settings": {
                        "extra_selectors": "#angmokio-popup"
                    }
                }],
                "cookies": [],
                "disable_on_mobile": false,
                "disable_on_tablet": false,
                "atc_promotion": null,
                "explain": null,
                "type_section": null,
                "theme_id": "235878",
                "size": "medium",
                "responsive_min_width": "0%",
                "responsive_max_width": "100%",
                "custom_width": "640px",
                "custom_height_auto": false,
                "custom_height": "380px",
                "scrollable_content": false,
                "animation_type": "fade",
                "animation_speed": "350",
                "animation_origin": "center top",
                "open_sound": "none",
                "custom_sound": "",
                "location": "center top",
                "position_top": "100",
                "position_bottom": "0",
                "position_left": "0",
                "position_right": "0",
                "position_from_trigger": false,
                "position_fixed": false,
                "overlay_disabled": false,
                "stackable": false,
                "disable_reposition": false,
                "zindex": "1999999999",
                "close_button_delay": "0",
                "fi_promotion": null,
                "close_on_form_submission": false,
                "close_on_form_submission_delay": "0",
                "close_on_overlay_click": false,
                "close_on_esc_press": false,
                "close_on_f4_press": false,
                "disable_form_reopen": false,
                "disable_accessibility": false,
                "theme_slug": "default-theme",
                "id": 241233,
                "slug": "ang-mo-kio"
            },
            "pum-241137": {
                "triggers": [{
                    "type": "click_open",
                    "settings": {
                        "extra_selectors": "#chicago_popup"
                    }
                }],
                "cookies": [],
                "disable_on_mobile": false,
                "disable_on_tablet": false,
                "atc_promotion": null,
                "explain": null,
                "type_section": null,
                "theme_id": "235878",
                "size": "medium",
                "responsive_min_width": "0%",
                "responsive_max_width": "100%",
                "custom_width": "640px",
                "custom_height_auto": false,
                "custom_height": "380px",
                "scrollable_content": false,
                "animation_type": "fade",
                "animation_speed": "350",
                "animation_origin": "center top",
                "open_sound": "none",
                "custom_sound": "",
                "location": "center top",
                "position_top": "100",
                "position_bottom": "0",
                "position_left": "0",
                "position_right": "0",
                "position_from_trigger": false,
                "position_fixed": false,
                "overlay_disabled": false,
                "stackable": false,
                "disable_reposition": false,
                "zindex": "1999999999",
                "close_button_delay": "0",
                "fi_promotion": null,
                "close_on_form_submission": false,
                "close_on_form_submission_delay": "0",
                "close_on_overlay_click": false,
                "close_on_esc_press": false,
                "close_on_f4_press": false,
                "disable_form_reopen": false,
                "disable_accessibility": false,
                "theme_slug": "default-theme",
                "id": 241137,
                "slug": "chicago"
            },
            "pum-236210": {
                "triggers": [{
                    "type": "click_open",
                    "settings": {
                        "cookie_name": "",
                        "extra_selectors": "#oklahoma"
                    }
                }],
                "cookies": [],
                "disable_on_mobile": false,
                "disable_on_tablet": false,
                "atc_promotion": null,
                "explain": null,
                "type_section": null,
                "theme_id": "235878",
                "size": "medium",
                "responsive_min_width": "0%",
                "responsive_max_width": "100%",
                "custom_width": "640px",
                "custom_height_auto": false,
                "custom_height": "380px",
                "scrollable_content": false,
                "animation_type": "fade",
                "animation_speed": "350",
                "animation_origin": "center top",
                "open_sound": "none",
                "custom_sound": "",
                "location": "center top",
                "position_top": "100",
                "position_bottom": "0",
                "position_left": "0",
                "position_right": "0",
                "position_from_trigger": false,
                "position_fixed": false,
                "overlay_disabled": false,
                "stackable": false,
                "disable_reposition": false,
                "zindex": "1999999999",
                "close_button_delay": "0",
                "fi_promotion": null,
                "close_on_form_submission": false,
                "close_on_form_submission_delay": "0",
                "close_on_overlay_click": false,
                "close_on_esc_press": false,
                "close_on_f4_press": false,
                "disable_form_reopen": false,
                "disable_accessibility": false,
                "theme_slug": "default-theme",
                "id": 236210,
                "slug": "oklahoma"
            },
            "pum-235966": {
                "triggers": [{
                    "type": "click_open",
                    "settings": {
                        "extra_selectors": "#orchard"
                    }
                }],
                "cookies": [],
                "disable_on_mobile": false,
                "disable_on_tablet": false,
                "atc_promotion": null,
                "explain": null,
                "type_section": null,
                "theme_id": "235878",
                "size": "medium",
                "responsive_min_width": "0%",
                "responsive_max_width": "100%",
                "custom_width": "640px",
                "custom_height_auto": false,
                "custom_height": "380px",
                "scrollable_content": false,
                "animation_type": "fade",
                "animation_speed": "350",
                "animation_origin": "center top",
                "open_sound": "none",
                "custom_sound": "",
                "location": "center top",
                "position_top": "100",
                "position_bottom": "0",
                "position_left": "0",
                "position_right": "0",
                "position_from_trigger": false,
                "position_fixed": false,
                "overlay_disabled": false,
                "stackable": false,
                "disable_reposition": false,
                "zindex": "1999999999",
                "close_button_delay": "0",
                "fi_promotion": null,
                "close_on_form_submission": false,
                "close_on_form_submission_delay": "0",
                "close_on_overlay_click": false,
                "close_on_esc_press": false,
                "close_on_f4_press": false,
                "disable_form_reopen": false,
                "disable_accessibility": false,
                "theme_slug": "default-theme",
                "id": 235966,
                "slug": "orchard"
            },
            "pum-239286": {
                "triggers": [{
                    "type": "click_open",
                    "settings": {
                        "extra_selectors": "#Sengkang"
                    }
                }],
                "cookies": [],
                "disable_on_mobile": false,
                "disable_on_tablet": false,
                "atc_promotion": null,
                "explain": null,
                "type_section": null,
                "theme_id": "235878",
                "size": "medium",
                "responsive_min_width": "0%",
                "responsive_max_width": "100%",
                "custom_width": "640px",
                "custom_height_auto": false,
                "custom_height": "380px",
                "scrollable_content": false,
                "animation_type": "fade",
                "animation_speed": "350",
                "animation_origin": "center top",
                "open_sound": "none",
                "custom_sound": "",
                "location": "center top",
                "position_top": "100",
                "position_bottom": "0",
                "position_left": "0",
                "position_right": "0",
                "position_from_trigger": false,
                "position_fixed": false,
                "overlay_disabled": false,
                "stackable": false,
                "disable_reposition": false,
                "zindex": "1999999999",
                "close_button_delay": "0",
                "fi_promotion": null,
                "close_on_form_submission": false,
                "close_on_form_submission_delay": "0",
                "close_on_overlay_click": false,
                "close_on_esc_press": false,
                "close_on_f4_press": false,
                "disable_form_reopen": false,
                "disable_accessibility": false,
                "theme_slug": "default-theme",
                "id": 239286,
                "slug": "sengkang"
            },
            "pum-238620": {
                "triggers": [{
                    "type": "click_open",
                    "settings": {
                        "extra_selectors": "#tampines"
                    }
                }],
                "cookies": [],
                "disable_on_mobile": false,
                "disable_on_tablet": false,
                "atc_promotion": null,
                "explain": null,
                "type_section": null,
                "theme_id": "235878",
                "size": "medium",
                "responsive_min_width": "0%",
                "responsive_max_width": "100%",
                "custom_width": "640px",
                "custom_height_auto": false,
                "custom_height": "380px",
                "scrollable_content": false,
                "animation_type": "fade",
                "animation_speed": "350",
                "animation_origin": "center top",
                "open_sound": "none",
                "custom_sound": "",
                "location": "center top",
                "position_top": "100",
                "position_bottom": "0",
                "position_left": "0",
                "position_right": "0",
                "position_from_trigger": false,
                "position_fixed": false,
                "overlay_disabled": false,
                "stackable": false,
                "disable_reposition": false,
                "zindex": "1999999999",
                "close_button_delay": "0",
                "fi_promotion": null,
                "close_on_form_submission": false,
                "close_on_form_submission_delay": "0",
                "close_on_overlay_click": false,
                "close_on_esc_press": false,
                "close_on_f4_press": false,
                "disable_form_reopen": false,
                "disable_accessibility": false,
                "theme_slug": "default-theme",
                "id": 238620,
                "slug": "tampines"
            },
            "pum-238772": {
                "triggers": [{
                    "type": "click_open",
                    "settings": {
                        "cookie_name": "",
                        "extra_selectors": "#tanglin"
                    }
                }],
                "cookies": [],
                "disable_on_mobile": false,
                "disable_on_tablet": false,
                "atc_promotion": null,
                "explain": null,
                "type_section": null,
                "theme_id": "235878",
                "size": "medium",
                "responsive_min_width": "0%",
                "responsive_max_width": "100%",
                "custom_width": "640px",
                "custom_height_auto": false,
                "custom_height": "380px",
                "scrollable_content": false,
                "animation_type": "fade",
                "animation_speed": "350",
                "animation_origin": "center top",
                "open_sound": "none",
                "custom_sound": "",
                "location": "center top",
                "position_top": "100",
                "position_bottom": "0",
                "position_left": "0",
                "position_right": "0",
                "position_from_trigger": false,
                "position_fixed": false,
                "overlay_disabled": false,
                "stackable": false,
                "disable_reposition": false,
                "zindex": "1999999999",
                "close_button_delay": "0",
                "fi_promotion": null,
                "close_on_form_submission": false,
                "close_on_form_submission_delay": "0",
                "close_on_overlay_click": false,
                "close_on_esc_press": false,
                "close_on_f4_press": false,
                "disable_form_reopen": false,
                "disable_accessibility": false,
                "theme_slug": "default-theme",
                "id": 238772,
                "slug": "tampines-2"
            },
            "pum-236192": {
                "triggers": [{
                    "type": "click_open",
                    "settings": {
                        "extra_selectors": "#dubai"
                    }
                }],
                "cookies": [],
                "disable_on_mobile": false,
                "disable_on_tablet": false,
                "atc_promotion": null,
                "explain": null,
                "type_section": null,
                "theme_id": "235878",
                "size": "medium",
                "responsive_min_width": "0%",
                "responsive_max_width": "100%",
                "custom_width": "640px",
                "custom_height_auto": false,
                "custom_height": "380px",
                "scrollable_content": false,
                "animation_type": "fade",
                "animation_speed": "350",
                "animation_origin": "center top",
                "open_sound": "none",
                "custom_sound": "",
                "location": "center top",
                "position_top": "100",
                "position_bottom": "0",
                "position_left": "0",
                "position_right": "0",
                "position_from_trigger": false,
                "position_fixed": false,
                "overlay_disabled": false,
                "stackable": false,
                "disable_reposition": false,
                "zindex": "1999999999",
                "close_button_delay": "0",
                "fi_promotion": null,
                "close_on_form_submission": false,
                "close_on_form_submission_delay": "0",
                "close_on_overlay_click": false,
                "close_on_esc_press": false,
                "close_on_f4_press": false,
                "disable_form_reopen": false,
                "disable_accessibility": false,
                "theme_slug": "default-theme",
                "id": 236192,
                "slug": "dubai"
            },
            "pum-236149": {
                "triggers": [{
                    "type": "click_open",
                    "settings": {
                        "extra_selectors": "#potongpasir\u200b"
                    }
                }],
                "cookies": [],
                "disable_on_mobile": false,
                "disable_on_tablet": false,
                "atc_promotion": null,
                "explain": null,
                "type_section": null,
                "theme_id": "235878",
                "size": "medium",
                "responsive_min_width": "0%",
                "responsive_max_width": "100%",
                "custom_width": "640px",
                "custom_height_auto": false,
                "custom_height": "380px",
                "scrollable_content": false,
                "animation_type": "fade",
                "animation_speed": "350",
                "animation_origin": "center top",
                "open_sound": "none",
                "custom_sound": "",
                "location": "center top",
                "position_top": "100",
                "position_bottom": "0",
                "position_left": "0",
                "position_right": "0",
                "position_from_trigger": false,
                "position_fixed": false,
                "overlay_disabled": false,
                "stackable": false,
                "disable_reposition": false,
                "zindex": "1999999999",
                "close_button_delay": "0",
                "fi_promotion": null,
                "close_on_form_submission": false,
                "close_on_form_submission_delay": "0",
                "close_on_overlay_click": false,
                "close_on_esc_press": false,
                "close_on_f4_press": false,
                "disable_form_reopen": false,
                "disable_accessibility": false,
                "theme_slug": "default-theme",
                "id": 236149,
                "slug": "potong-pasir%e2%80%8b"
            },
            "pum-236139": {
                "triggers": [{
                    "type": "click_open",
                    "settings": {
                        "extra_selectors": "#hougang\u200b"
                    }
                }],
                "cookies": [],
                "disable_on_mobile": false,
                "disable_on_tablet": false,
                "atc_promotion": null,
                "explain": null,
                "type_section": null,
                "theme_id": "235878",
                "size": "medium",
                "responsive_min_width": "0%",
                "responsive_max_width": "100%",
                "custom_width": "640px",
                "custom_height_auto": false,
                "custom_height": "380px",
                "scrollable_content": false,
                "animation_type": "fade",
                "animation_speed": "350",
                "animation_origin": "center top",
                "open_sound": "none",
                "custom_sound": "",
                "location": "center top",
                "position_top": "100",
                "position_bottom": "0",
                "position_left": "0",
                "position_right": "0",
                "position_from_trigger": false,
                "position_fixed": false,
                "overlay_disabled": false,
                "stackable": false,
                "disable_reposition": false,
                "zindex": "1999999999",
                "close_button_delay": "0",
                "fi_promotion": null,
                "close_on_form_submission": false,
                "close_on_form_submission_delay": "0",
                "close_on_overlay_click": false,
                "close_on_esc_press": false,
                "close_on_f4_press": false,
                "disable_form_reopen": false,
                "disable_accessibility": false,
                "theme_slug": "default-theme",
                "id": 236139,
                "slug": "hougang%e2%80%8b"
            },
            "pum-236126": {
                "triggers": [{
                    "type": "click_open",
                    "settings": {
                        "extra_selectors": "#serangoon\u200b"
                    }
                }],
                "cookies": [],
                "disable_on_mobile": false,
                "disable_on_tablet": false,
                "atc_promotion": null,
                "explain": null,
                "type_section": null,
                "theme_id": "235878",
                "size": "medium",
                "responsive_min_width": "0%",
                "responsive_max_width": "100%",
                "custom_width": "640px",
                "custom_height_auto": false,
                "custom_height": "380px",
                "scrollable_content": false,
                "animation_type": "fade",
                "animation_speed": "350",
                "animation_origin": "center top",
                "open_sound": "none",
                "custom_sound": "",
                "location": "center top",
                "position_top": "100",
                "position_bottom": "0",
                "position_left": "0",
                "position_right": "0",
                "position_from_trigger": false,
                "position_fixed": false,
                "overlay_disabled": false,
                "stackable": false,
                "disable_reposition": false,
                "zindex": "1999999999",
                "close_button_delay": "0",
                "fi_promotion": null,
                "close_on_form_submission": false,
                "close_on_form_submission_delay": "0",
                "close_on_overlay_click": false,
                "close_on_esc_press": false,
                "close_on_f4_press": false,
                "disable_form_reopen": false,
                "disable_accessibility": false,
                "theme_slug": "default-theme",
                "id": 236126,
                "slug": "serangoon%e2%80%8b"
            },
            "pum-236113": {
                "triggers": [{
                    "type": "click_open",
                    "settings": {
                        "cookie_name": "",
                        "extra_selectors": "#buangkok"
                    }
                }],
                "cookies": [],
                "disable_on_mobile": false,
                "disable_on_tablet": false,
                "atc_promotion": null,
                "explain": null,
                "type_section": null,
                "theme_id": "235878",
                "size": "medium",
                "responsive_min_width": "0%",
                "responsive_max_width": "100%",
                "custom_width": "640px",
                "custom_height_auto": false,
                "custom_height": "380px",
                "scrollable_content": false,
                "animation_type": "fade",
                "animation_speed": "350",
                "animation_origin": "center top",
                "open_sound": "none",
                "custom_sound": "",
                "location": "center top",
                "position_top": "100",
                "position_bottom": "0",
                "position_left": "0",
                "position_right": "0",
                "position_from_trigger": false,
                "position_fixed": false,
                "overlay_disabled": false,
                "stackable": false,
                "disable_reposition": false,
                "zindex": "1999999999",
                "close_button_delay": "0",
                "fi_promotion": null,
                "close_on_form_submission": false,
                "close_on_form_submission_delay": "0",
                "close_on_overlay_click": false,
                "close_on_esc_press": false,
                "close_on_f4_press": false,
                "disable_form_reopen": false,
                "disable_accessibility": false,
                "theme_slug": "default-theme",
                "id": 236113,
                "slug": "buangkok%e2%80%8b"
            },
            "pum-236102": {
                "triggers": [{
                    "type": "click_open",
                    "settings": {
                        "extra_selectors": "#punggol"
                    }
                }],
                "cookies": [],
                "disable_on_mobile": false,
                "disable_on_tablet": false,
                "atc_promotion": null,
                "explain": null,
                "type_section": null,
                "theme_id": "235878",
                "size": "medium",
                "responsive_min_width": "0%",
                "responsive_max_width": "100%",
                "custom_width": "640px",
                "custom_height_auto": false,
                "custom_height": "380px",
                "scrollable_content": false,
                "animation_type": "fade",
                "animation_speed": "350",
                "animation_origin": "center top",
                "open_sound": "none",
                "custom_sound": "",
                "location": "center top",
                "position_top": "100",
                "position_bottom": "0",
                "position_left": "0",
                "position_right": "0",
                "position_from_trigger": false,
                "position_fixed": false,
                "overlay_disabled": false,
                "stackable": false,
                "disable_reposition": false,
                "zindex": "1999999999",
                "close_button_delay": "0",
                "fi_promotion": null,
                "close_on_form_submission": false,
                "close_on_form_submission_delay": "0",
                "close_on_overlay_click": false,
                "close_on_esc_press": false,
                "close_on_f4_press": false,
                "disable_form_reopen": false,
                "disable_accessibility": false,
                "theme_slug": "default-theme",
                "id": 236102,
                "slug": "punggol"
            },
            "pum-236089": {
                "triggers": [{
                    "type": "click_open",
                    "settings": {
                        "extra_selectors": "#bukitpanjang"
                    }
                }],
                "cookies": [],
                "disable_on_mobile": false,
                "disable_on_tablet": false,
                "atc_promotion": null,
                "explain": null,
                "type_section": null,
                "theme_id": "235878",
                "size": "medium",
                "responsive_min_width": "0%",
                "responsive_max_width": "100%",
                "custom_width": "640px",
                "custom_height_auto": false,
                "custom_height": "380px",
                "scrollable_content": false,
                "animation_type": "fade",
                "animation_speed": "350",
                "animation_origin": "center top",
                "open_sound": "none",
                "custom_sound": "",
                "location": "center top",
                "position_top": "100",
                "position_bottom": "0",
                "position_left": "0",
                "position_right": "0",
                "position_from_trigger": false,
                "position_fixed": false,
                "overlay_disabled": false,
                "stackable": false,
                "disable_reposition": false,
                "zindex": "1999999999",
                "close_button_delay": "0",
                "fi_promotion": null,
                "close_on_form_submission": false,
                "close_on_form_submission_delay": "0",
                "close_on_overlay_click": false,
                "close_on_esc_press": false,
                "close_on_f4_press": false,
                "disable_form_reopen": false,
                "disable_accessibility": false,
                "theme_slug": "default-theme",
                "id": 236089,
                "slug": "bukit-panjang"
            },
            "pum-236081": {
                "triggers": [{
                    "type": "click_open",
                    "settings": {
                        "extra_selectors": "#jurongsafra2"
                    }
                }],
                "cookies": [],
                "disable_on_mobile": false,
                "disable_on_tablet": false,
                "atc_promotion": null,
                "explain": null,
                "type_section": null,
                "theme_id": "235878",
                "size": "medium",
                "responsive_min_width": "0%",
                "responsive_max_width": "100%",
                "custom_width": "640px",
                "custom_height_auto": false,
                "custom_height": "380px",
                "scrollable_content": false,
                "animation_type": "fade",
                "animation_speed": "350",
                "animation_origin": "center top",
                "open_sound": "none",
                "custom_sound": "",
                "location": "center top",
                "position_top": "100",
                "position_bottom": "0",
                "position_left": "0",
                "position_right": "0",
                "position_from_trigger": false,
                "position_fixed": false,
                "overlay_disabled": false,
                "stackable": false,
                "disable_reposition": false,
                "zindex": "1999999999",
                "close_button_delay": "0",
                "fi_promotion": null,
                "close_on_form_submission": false,
                "close_on_form_submission_delay": "0",
                "close_on_overlay_click": false,
                "close_on_esc_press": false,
                "close_on_f4_press": false,
                "disable_form_reopen": false,
                "disable_accessibility": false,
                "theme_slug": "default-theme",
                "id": 236081,
                "slug": "jurong-safra-2"
            },
            "pum-236065": {
                "triggers": [{
                    "type": "click_open",
                    "settings": {
                        "extra_selectors": "#jurongsafra1"
                    }
                }],
                "cookies": [],
                "disable_on_mobile": false,
                "disable_on_tablet": false,
                "atc_promotion": null,
                "explain": null,
                "type_section": null,
                "theme_id": "235878",
                "size": "medium",
                "responsive_min_width": "0%",
                "responsive_max_width": "100%",
                "custom_width": "640px",
                "custom_height_auto": false,
                "custom_height": "380px",
                "scrollable_content": false,
                "animation_type": "fade",
                "animation_speed": "350",
                "animation_origin": "center top",
                "open_sound": "none",
                "custom_sound": "",
                "location": "center top",
                "position_top": "100",
                "position_bottom": "0",
                "position_left": "0",
                "position_right": "0",
                "position_from_trigger": false,
                "position_fixed": false,
                "overlay_disabled": false,
                "stackable": false,
                "disable_reposition": false,
                "zindex": "1999999999",
                "close_button_delay": "0",
                "fi_promotion": null,
                "close_on_form_submission": false,
                "close_on_form_submission_delay": "0",
                "close_on_overlay_click": false,
                "close_on_esc_press": false,
                "close_on_f4_press": false,
                "disable_form_reopen": false,
                "disable_accessibility": false,
                "theme_slug": "default-theme",
                "id": 236065,
                "slug": "jurong-safra-1"
            },
            "pum-236054": {
                "triggers": [{
                    "type": "click_open",
                    "settings": {
                        "cookie_name": "",
                        "extra_selectors": "#katong"
                    }
                }],
                "cookies": [],
                "disable_on_mobile": false,
                "disable_on_tablet": false,
                "atc_promotion": null,
                "explain": null,
                "type_section": null,
                "theme_id": "235878",
                "size": "medium",
                "responsive_min_width": "0%",
                "responsive_max_width": "100%",
                "custom_width": "640px",
                "custom_height_auto": false,
                "custom_height": "380px",
                "scrollable_content": false,
                "animation_type": "fade",
                "animation_speed": "350",
                "animation_origin": "center top",
                "open_sound": "none",
                "custom_sound": "",
                "location": "center top",
                "position_top": "100",
                "position_bottom": "0",
                "position_left": "0",
                "position_right": "0",
                "position_from_trigger": false,
                "position_fixed": false,
                "overlay_disabled": false,
                "stackable": false,
                "disable_reposition": false,
                "zindex": "1999999999",
                "close_button_delay": "0",
                "fi_promotion": null,
                "close_on_form_submission": false,
                "close_on_form_submission_delay": "0",
                "close_on_overlay_click": false,
                "close_on_esc_press": false,
                "close_on_f4_press": false,
                "disable_form_reopen": false,
                "disable_accessibility": false,
                "theme_slug": "default-theme",
                "id": 236054,
                "slug": "katong"
            },
            "pum-236040": {
                "triggers": [{
                    "type": "click_open",
                    "settings": {
                        "extra_selectors": "#aljunied"
                    }
                }],
                "cookies": [],
                "disable_on_mobile": false,
                "disable_on_tablet": false,
                "atc_promotion": null,
                "explain": null,
                "type_section": null,
                "theme_id": "235878",
                "size": "medium",
                "responsive_min_width": "0%",
                "responsive_max_width": "100%",
                "custom_width": "640px",
                "custom_height_auto": false,
                "custom_height": "380px",
                "scrollable_content": false,
                "animation_type": "fade",
                "animation_speed": "350",
                "animation_origin": "center top",
                "open_sound": "none",
                "custom_sound": "",
                "location": "center top",
                "position_top": "100",
                "position_bottom": "0",
                "position_left": "0",
                "position_right": "0",
                "position_from_trigger": false,
                "position_fixed": false,
                "overlay_disabled": false,
                "stackable": false,
                "disable_reposition": false,
                "zindex": "1999999999",
                "close_button_delay": "0",
                "fi_promotion": null,
                "close_on_form_submission": false,
                "close_on_form_submission_delay": "0",
                "close_on_overlay_click": false,
                "close_on_esc_press": false,
                "close_on_f4_press": false,
                "disable_form_reopen": false,
                "disable_accessibility": false,
                "theme_slug": "default-theme",
                "id": 236040,
                "slug": "aljunied%e2%80%8b"
            },
            "pum-236029": {
                "triggers": [{
                    "type": "click_open",
                    "settings": {
                        "extra_selectors": "#yishunring"
                    }
                }],
                "cookies": [],
                "disable_on_mobile": false,
                "disable_on_tablet": false,
                "atc_promotion": null,
                "explain": null,
                "type_section": null,
                "theme_id": "235878",
                "size": "medium",
                "responsive_min_width": "0%",
                "responsive_max_width": "100%",
                "custom_width": "640px",
                "custom_height_auto": false,
                "custom_height": "380px",
                "scrollable_content": false,
                "animation_type": "fade",
                "animation_speed": "350",
                "animation_origin": "center top",
                "open_sound": "none",
                "custom_sound": "",
                "location": "center top",
                "position_top": "100",
                "position_bottom": "0",
                "position_left": "0",
                "position_right": "0",
                "position_from_trigger": false,
                "position_fixed": false,
                "overlay_disabled": false,
                "stackable": false,
                "disable_reposition": false,
                "zindex": "1999999999",
                "close_button_delay": "0",
                "fi_promotion": null,
                "close_on_form_submission": false,
                "close_on_form_submission_delay": "0",
                "close_on_overlay_click": false,
                "close_on_esc_press": false,
                "close_on_f4_press": false,
                "disable_form_reopen": false,
                "disable_accessibility": false,
                "theme_slug": "default-theme",
                "id": 236029,
                "slug": "yishunring"
            },
            "pum-236015": {
                "triggers": [{
                    "type": "click_open",
                    "settings": {
                        "extra_selectors": "#admiralty"
                    }
                }],
                "cookies": [],
                "disable_on_mobile": false,
                "disable_on_tablet": false,
                "atc_promotion": null,
                "explain": null,
                "type_section": null,
                "theme_id": "235878",
                "size": "medium",
                "responsive_min_width": "0%",
                "responsive_max_width": "100%",
                "custom_width": "640px",
                "custom_height_auto": false,
                "custom_height": "380px",
                "scrollable_content": false,
                "animation_type": "fade",
                "animation_speed": "350",
                "animation_origin": "center top",
                "open_sound": "none",
                "custom_sound": "",
                "location": "center top",
                "position_top": "100",
                "position_bottom": "0",
                "position_left": "0",
                "position_right": "0",
                "position_from_trigger": false,
                "position_fixed": false,
                "overlay_disabled": false,
                "stackable": false,
                "disable_reposition": false,
                "zindex": "1999999999",
                "close_button_delay": "0",
                "fi_promotion": null,
                "close_on_form_submission": false,
                "close_on_form_submission_delay": "0",
                "close_on_overlay_click": false,
                "close_on_esc_press": false,
                "close_on_f4_press": false,
                "disable_form_reopen": false,
                "disable_accessibility": false,
                "theme_slug": "default-theme",
                "id": 236015,
                "slug": "admiralty"
            },
            "pum-235999": {
                "triggers": [{
                    "type": "click_open",
                    "settings": {
                        "extra_selectors": "#woodland2"
                    }
                }],
                "cookies": [],
                "disable_on_mobile": false,
                "disable_on_tablet": false,
                "atc_promotion": null,
                "explain": null,
                "type_section": null,
                "theme_id": "235878",
                "size": "medium",
                "responsive_min_width": "0%",
                "responsive_max_width": "100%",
                "custom_width": "640px",
                "custom_height_auto": false,
                "custom_height": "380px",
                "scrollable_content": false,
                "animation_type": "fade",
                "animation_speed": "350",
                "animation_origin": "center top",
                "open_sound": "none",
                "custom_sound": "",
                "location": "center top",
                "position_top": "100",
                "position_bottom": "0",
                "position_left": "0",
                "position_right": "0",
                "position_from_trigger": false,
                "position_fixed": false,
                "overlay_disabled": false,
                "stackable": false,
                "disable_reposition": false,
                "zindex": "1999999999",
                "close_button_delay": "0",
                "fi_promotion": null,
                "close_on_form_submission": false,
                "close_on_form_submission_delay": "0",
                "close_on_overlay_click": false,
                "close_on_esc_press": false,
                "close_on_f4_press": false,
                "disable_form_reopen": false,
                "disable_accessibility": false,
                "theme_slug": "default-theme",
                "id": 235999,
                "slug": "woodland-2"
            },
            "pum-235978": {
                "triggers": [{
                    "type": "click_open",
                    "settings": {
                        "extra_selectors": "#woodland1"
                    }
                }],
                "cookies": [],
                "disable_on_mobile": false,
                "disable_on_tablet": false,
                "atc_promotion": null,
                "explain": null,
                "type_section": null,
                "theme_id": "235878",
                "size": "medium",
                "responsive_min_width": "0%",
                "responsive_max_width": "100%",
                "custom_width": "640px",
                "custom_height_auto": false,
                "custom_height": "380px",
                "scrollable_content": false,
                "animation_type": "fade",
                "animation_speed": "350",
                "animation_origin": "center top",
                "open_sound": "none",
                "custom_sound": "",
                "location": "center top",
                "position_top": "100",
                "position_bottom": "0",
                "position_left": "0",
                "position_right": "0",
                "position_from_trigger": false,
                "position_fixed": false,
                "overlay_disabled": false,
                "stackable": false,
                "disable_reposition": false,
                "zindex": "1999999999",
                "close_button_delay": "0",
                "fi_promotion": null,
                "close_on_form_submission": false,
                "close_on_form_submission_delay": "0",
                "close_on_overlay_click": false,
                "close_on_esc_press": false,
                "close_on_f4_press": false,
                "disable_form_reopen": false,
                "disable_accessibility": false,
                "theme_slug": "default-theme",
                "id": 235978,
                "slug": "woodland-1"
            },
            "pum-235952": {
                "triggers": [{
                    "type": "click_open",
                    "settings": {
                        "extra_selectors": "#tiongbahru"
                    }
                }],
                "cookies": [],
                "disable_on_mobile": false,
                "disable_on_tablet": false,
                "atc_promotion": null,
                "explain": null,
                "type_section": null,
                "theme_id": "235878",
                "size": "medium",
                "responsive_min_width": "0%",
                "responsive_max_width": "100%",
                "custom_width": "640px",
                "custom_height_auto": false,
                "custom_height": "380px",
                "scrollable_content": false,
                "animation_type": "fade",
                "animation_speed": "350",
                "animation_origin": "center top",
                "open_sound": "none",
                "custom_sound": "",
                "location": "center top",
                "position_top": "100",
                "position_bottom": "0",
                "position_left": "0",
                "position_right": "0",
                "position_from_trigger": false,
                "position_fixed": false,
                "overlay_disabled": false,
                "stackable": false,
                "disable_reposition": false,
                "zindex": "1999999999",
                "close_button_delay": "0",
                "fi_promotion": null,
                "close_on_form_submission": false,
                "close_on_form_submission_delay": "0",
                "close_on_overlay_click": false,
                "close_on_esc_press": false,
                "close_on_f4_press": false,
                "disable_form_reopen": false,
                "disable_accessibility": false,
                "theme_slug": "default-theme",
                "id": 235952,
                "slug": "tiong-bahru"
            },
            "pum-235887": {
                "triggers": [{
                    "type": "click_open",
                    "settings": {
                        "extra_selectors": "#toapayoh"
                    }
                }],
                "cookies": [],
                "disable_on_mobile": false,
                "disable_on_tablet": false,
                "atc_promotion": null,
                "explain": null,
                "type_section": null,
                "theme_id": "235878",
                "size": "medium",
                "responsive_min_width": "0%",
                "responsive_max_width": "100%",
                "custom_width": "640px",
                "custom_height_auto": false,
                "custom_height": "380px",
                "scrollable_content": false,
                "animation_type": "fade",
                "animation_speed": "350",
                "animation_origin": "center top",
                "open_sound": "none",
                "custom_sound": "",
                "location": "center top",
                "position_top": "100",
                "position_bottom": "0",
                "position_left": "0",
                "position_right": "0",
                "position_from_trigger": false,
                "position_fixed": false,
                "overlay_disabled": false,
                "stackable": false,
                "disable_reposition": false,
                "zindex": "1999999999",
                "close_button_delay": "0",
                "fi_promotion": null,
                "close_on_form_submission": false,
                "close_on_form_submission_delay": "0",
                "close_on_overlay_click": false,
                "close_on_esc_press": false,
                "close_on_f4_press": false,
                "disable_form_reopen": false,
                "disable_accessibility": false,
                "theme_slug": "default-theme",
                "id": 235887,
                "slug": "toa-payoh"
            }
        };
        /* ]]> */
    </script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/uploads/pum/pum-site-scripts5cf7.js?defer&amp;generated=1671691530&amp;ver=1.17.1'
        id='popup-maker-site-js'></script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/themes/Divi/core/admin/js/common0349.js?ver=4.19.1'
        id='et-core-common-js'></script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/themes/Divi/includes/builder/feature/dynamic-assets/assets/js/jquery.fitvids0349.js?ver=4.19.1'
        id='fitvids-js'></script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/plugins/elementor/assets/js/webpack.runtime.min2e46.js?ver=3.9.2'
        id='elementor-webpack-runtime-js'></script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/plugins/elementor/assets/js/frontend-modules.min2e46.js?ver=3.9.2'
        id='elementor-frontend-modules-js'></script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/plugins/elementor/assets/lib/waypoints/waypoints.min05da.js?ver=4.0.2'
        id='elementor-waypoints-js'></script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/plugins/elementor/assets/lib/swiper/swiper.min48f5.js?ver=5.3.6'
        id='swiper-js'></script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/plugins/elementor/assets/lib/share-link/share-link.min2e46.js?ver=3.9.2'
        id='share-link-js'></script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/plugins/elementor/assets/lib/dialog/dialog.mind227.js?ver=4.9.0'
        id='elementor-dialog-js'></script>
    <script type='text/javascript' id='elementor-frontend-js-before'>
        var elementorFrontendConfig = {
            "environmentMode": {
                "edit": false,
                "wpPreview": false,
                "isScriptDebug": false
            },
            "i18n": {
                "shareOnFacebook": "Share on Facebook",
                "shareOnTwitter": "Share on Twitter",
                "pinIt": "Pin it",
                "download": "Download",
                "downloadImage": "Download image",
                "fullscreen": "Fullscreen",
                "zoom": "Zoom",
                "share": "Share",
                "playVideo": "Play Video",
                "previous": "Previous",
                "next": "Next",
                "close": "Close"
            },
            "is_rtl": false,
            "breakpoints": {
                "xs": 0,
                "sm": 480,
                "md": 768,
                "lg": 1025,
                "xl": 1440,
                "xxl": 1600
            },
            "responsive": {
                "breakpoints": {
                    "mobile": {
                        "label": "Mobile",
                        "value": 767,
                        "default_value": 767,
                        "direction": "max",
                        "is_enabled": true
                    },
                    "mobile_extra": {
                        "label": "Mobile Extra",
                        "value": 880,
                        "default_value": 880,
                        "direction": "max",
                        "is_enabled": false
                    },
                    "tablet": {
                        "label": "Tablet",
                        "value": 1024,
                        "default_value": 1024,
                        "direction": "max",
                        "is_enabled": true
                    },
                    "tablet_extra": {
                        "label": "Tablet Extra",
                        "value": 1200,
                        "default_value": 1200,
                        "direction": "max",
                        "is_enabled": false
                    },
                    "laptop": {
                        "label": "Laptop",
                        "value": 1366,
                        "default_value": 1366,
                        "direction": "max",
                        "is_enabled": false
                    },
                    "widescreen": {
                        "label": "Widescreen",
                        "value": 2400,
                        "default_value": 2400,
                        "direction": "min",
                        "is_enabled": false
                    }
                }
            },
            "version": "3.9.2",
            "is_static": false,
            "experimentalFeatures": {
                "e_import_export": true,
                "e_hidden_wordpress_widgets": true,
                "landing-pages": true,
                "elements-color-picker": true,
                "favorite-widgets": true,
                "admin-top-bar": true,
                "kit-elements-defaults": true
            },
            "urls": {
                "assets": "https:\/\/eimaths.com\/wp-content\/plugins\/elementor\/assets\/"
            },
            "settings": {
                "page": [],
                "editorPreferences": []
            },
            "kit": {
                "active_breakpoints": ["viewport_mobile", "viewport_tablet"],
                "global_image_lightbox": "yes",
                "lightbox_enable_counter": "yes",
                "lightbox_enable_fullscreen": "yes",
                "lightbox_enable_zoom": "yes",
                "lightbox_enable_share": "yes",
                "lightbox_title_src": "title",
                "lightbox_description_src": "description"
            },
            "post": {
                "id": 260,
                "title": "Contact%20us%20-%20eiMaths",
                "excerpt": "",
                "featuredImage": false
            }
        };
    </script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/plugins/elementor/assets/js/frontend.min2e46.js?ver=3.9.2'
        id='elementor-frontend-js'></script>
    <script type='text/javascript'
        src='https://www.eimaths-th.com/wp-content/plugins/elementor/assets/js/preloaded-modules.min2e46.js?ver=3.9.2'
        id='preloaded-modules-js'></script>
    <!-- start Simple Custom CSS and JS -->
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('input[data-original_id="visitor_ip"]').attr('hidden', true);
            $.ajax({
                url: '//api.ipify.org/?format=json',
                dataType: 'JSON',
                beforeSend: function() {

                },
                success: function(response) {
                    $('input[data-original_id="visitor_ip"]').val(response.ip);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.warn(textStatus, errorThrown);
                }
            });
        });
    </script>
    <!-- end Simple Custom CSS and JS -->
    <!-- start Simple Custom CSS and JS -->
    <style type="text/css">
        .et_pb_pricing_heading {
            background-color: black;
        }

        .et_pb_pricing_content_top {
            display: none;
        }
    </style>
    <!-- end Simple Custom CSS and JS -->
    <!-- start Simple Custom CSS and JS -->
    {{-- <a class="wapp" target="_blank" href="https://wa.me/6592381382" rel="noopener">
        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
            x="0px" y="0px" width="200px" height="200px" viewBox="0 0 200 200"
            style="enable-background:new 0 0 200 200;" xml:space="preserve">
            <style type="text/css">
                .st0 {
                    fill: #2DA749;
                }

                .st1 {
                    fill: #FFFFFF;
                }
            </style>
            <defs>
            </defs>
            <g>
                <path id="background_1_" class="st0"
                    d="M164.2,196.6c-42.7,4.6-85.7,4.6-128.4,0c-17.1-1.8-30.6-15.3-32.4-32.4
		c-4.6-42.7-4.6-85.7,0-128.4C5.2,18.7,18.7,5.2,35.8,3.4c42.7-4.6,85.7-4.6,128.4,0c17.1,1.8,30.6,15.3,32.4,32.4
		c4.6,42.7,4.6,85.7,0,128.4C194.8,181.3,181.3,194.8,164.2,196.6L164.2,196.6z" />
                <path class="st1"
                    d="M144.1,55.9C120,31.6,80.7,31.4,56.4,55.5c-19.2,19-23.9,48.1-11.6,72.2l-6.6,31.9c-0.1,0.6,0,1.3,0.4,1.9
		c0.6,0.8,1.6,1.2,2.5,1l31.3-7.4c30.7,15.2,67.9,2.7,83.1-27.9C167.3,103.4,162.7,74.8,144.1,55.9z M134.3,133.8
		c-14.8,14.7-37.3,18.4-55.9,9.1l-4.4-2.2l-19.2,4.5l0.1-0.2l4-19.3l-2.1-4.2c-9.5-18.7-5.9-41.5,8.9-56.3c19-19,49.7-19,68.7,0
		c0.1,0.1,0.2,0.2,0.2,0.2C153.3,84.4,153.2,115,134.3,133.8z M133.1,118.6c-0.1,0.4-0.3,0.7-0.5,1c-2.4,3.7-6.1,8.2-10.7,9.4
		c-8.2,2-20.7,0.1-36.4-14.5l-0.2-0.2C71.6,101.6,68,91,68.9,82.6c0.5-4.8,4.5-9.1,7.8-11.9c1.9-1.6,4.7-1.4,6.3,0.5
		c0.3,0.3,0.5,0.7,0.7,1.1l5.1,11.4c0.7,1.5,0.5,3.2-0.6,4.5l-2.6,3.3c-1.1,1.4-1.3,3.3-0.4,4.9c1.4,2.5,4.9,6.2,8.7,9.6
		c4.3,3.9,9,7.4,12,8.6c1.6,0.7,3.5,0.3,4.8-1l3-3c1.2-1.1,2.9-1.6,4.4-1.1l12,3.4C132.5,113.7,133.8,116.2,133.1,118.6z" />
            </g>
        </svg>
    </a><!-- end Simple Custom CSS and JS --> --}}

    <span class="et_pb_scroll_top et-pb-icon"></span>
</body>

<!-- Mirrored from eimaths.com/contact-us/ by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 03 Jul 2023 08:15:04 GMT -->

</html>
