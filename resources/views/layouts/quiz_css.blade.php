<meta charset="utf-8">
<title>eiMaths Quiz | @yield('title')</title>

<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">


<!-- Fonts -->
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

<link rel="icon" type="image/x-icon" href="{{ asset('images/cropped-footer-logo-32x32.png') }}">
<link rel="shortcut icon" href="{{ asset('images/cropped-footer-logo-32x32.png') }}" type="image/x-icon">
<!-- Styles and Scripts -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@vite(['resources/css/app.css', 'resources/js/app.js'])

{{-- @vite(['resources/css/app.css']) --}}
@livewireStyles
<style>
    .nav-link.active {
        font-weight: bold;
        color: #ff0000;
        /* Customize the active link color */
    }
</style>

@stack('css')

<style>
    .logo-image {
        height: auto;
        width: 15%;
    }

    .footer {
        background-color: #ff9720;
        color: #ffffff;
        padding: 1.5rem;
        width: 100%;
        display: flex;
        justify-content: center;
    }

    .footer p {
        margin: 0;
    }

    .footer ul {
        list-style: none;
        padding: 0;
    }

    .footer ul li {
        display: inline;
        margin-right: 15px;
    }

    .footer ul li a {
        color: #ffffff;
        text-decoration: none;
    }

    .footer ul li a:hover {
        text-decoration: underline;
    }

    .footer .contact-info {
        display: block;
        /* Prevent flex interfering with layout */
        margin-bottom: 10px;
        /* Optional: Add space between items */
    }

    .footer h4 {
        margin: 0;
    }

    .footer a {
        margin-left: 5px;
    }

    .footer-container img {
        display: block;
        margin-bottom: 10px;
    }

    .footer-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: left;
        width: 100%;
        flex-grow: 1;
    }

    .row {
        display: flex;
        flex-wrap: wrap;
    }


    .col-lg-4-30px {
        padding: 30px;
    }
</style>
{{-- <style>
    .logo-image {
        height: auto;
        width: 15%;
    }

    .footer {
        background-color: #ff9720;
        color: #ffffff;
        padding: 1.5rem;
        width: 100%;
        display: flex;
        justify-content: center;
    }

    .footer p {
        margin: 0;
    }

    .footer ul {
        list-style: none;
        padding: 0;
    }

    .footer ul li {
        display: inline;
        margin-right: 15px;
    }

    .footer ul li a {
        color: #ffffff;
        text-decoration: none;
    }

    .footer ul li a:hover {
        text-decoration: underline;
    }

    .contact-info {
        display: block;
        margin-bottom: 10px;
        white-space: nowrap;
        /* Prevent text wrapping */
        /* overflow: hidden; */
        /* Hide overflowing content */
        text-overflow: ellipsis;
        /* Add ellipsis (...) if content overflows */
    }

    .footer h4 {
        margin: 0;
    }

    .footer a {
        margin-left: 5px;
    }

    .footer-container img {
        display: block;
        margin-bottom: 10px;
    }

    .footer-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: left;
        width: 100%;
        flex-grow: 2;
    }

    /* .row {
        display: flex;
        flex-wrap: wrap;
    }

    .col-lg-6 {
        padding: 15px;
    } */
</style> --}}
