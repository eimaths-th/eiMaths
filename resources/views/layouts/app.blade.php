{{-- <x-laravel-ui-adminlte::adminlte-layout> --}}
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Language" content="en">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title> {{ config('app.name') }} {{ config('app.subtitle') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/cropped-footer-logo-32x32.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/cropped-footer-logo-32x32.png') }}" type="image/x-icon">

    <meta name="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, shrink-to-fit=no" />
    {{--  <meta name="description" content="This is an example dashboard created using build-in elements and components."> --}}
    <meta name="msapplication-tap-highlight" content="no">

    @include('layouts.includes.css')
    @yield('style')
</head>

<body class="hold-transition sidebar-mini layout-fixed layout-footer-fixed">
    <div id="loadingi"></div>
    <div class="wrapper">
        <!-- Main Header -->
        @include('layouts.header')

        <!-- Left side column. contains the logo and sidebar -->
        @include('layouts.sidebar')

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            @yield('content')
        </div>

        <!-- Main Footer -->
        @include('layouts.footer')

    </div>
</body>

@include('layouts.includes.js')


<script>
    /* var loadingDiv = document.createElement('div');
    loadingDiv.id = 'loadingi';
    loadingDiv.style.display = 'block';
    loadingDiv.style.position = 'fixed';
    loadingDiv.style.top = '0';
    loadingDiv.style.left = '0';
    loadingDiv.style.width = '100%';
    loadingDiv.style.height = '100%';
    loadingDiv.style.backgroundColor = 'rgba(0, 0, 0, 0.8)';
    loadingDiv.style.zIndex = '9999';
    loadingDiv.style.backgroundImage = 'url("https://linkme.vip2541.co/images/loading.gif")';
    loadingDiv.style.backgroundRepeat = 'no-repeat';
    loadingDiv.style.backgroundPosition = 'center';
    loadingDiv.style.backgroundSize = '50px';
    document.body.insertBefore(loadingDiv, document.body.firstChild); */

    function updateDigitalClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');

        document.getElementById('hours').textContent = hours;
        document.getElementById('minutes').textContent = minutes;
        document.getElementById('seconds').textContent = seconds;
    }

    /* updateDigitalClock();
    setInterval(updateDigitalClock, 1000); */

    function updateWeather(lat, lon) {
        const weatherElement = document.getElementById('weather');
        const locationElement = document.getElementById('location');

        const lapiKey = 'AIzaSyC8v6o6VKtOzrbvcTP-BEnuEvZGeW7rSPk';
        const geoApiUrl = `https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lon}&key=${lapiKey}`;

        // Fetch location data
        fetch(geoApiUrl)
            .then(response => response.json())
            .then(locationData => {
                const addressComponents = locationData.results[0].address_components;
                const locationName =
                    `${addressComponents[1].short_name} ${addressComponents[2].short_name} ${addressComponents[3].short_name}`;
                const addressl = locationData.results[0].formatted_address;

                // Display location name
                locationElement.innerHTML = `<i class="fa-solid fa-location-dot"></i> ${locationName}`;
            })
            .catch(error => {
                console.error('Error fetching location data:', error);
            });

        const apiKey = 'fbe9ed2bd4d3caedef17a2f42e43dc7d';
        const apiUrl =
            `https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&appid=${apiKey}&units=metric`;

        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                const temperature = data.main.temp;
                const weatherIconCode = data.weather[0].icon;
                const weatherIconUrl = `http://openweathermap.org/img/w/${weatherIconCode}.png`;
                const weatherDescription = data.weather[0].description;

                const weatherHTML = `Weather :
          <img src="${weatherIconUrl}" alt="${weatherDescription}" width="35px">
          ${temperature.toFixed(1)}°C
        `;

                weatherElement.innerHTML = weatherHTML;
            })
            .catch(error => {
                console.error('Error fetching weather data:', error);
            });
    }

    function getLocationAndWeather() {
        const locationElement = document.getElementById('location');
        const weatherElement = document.getElementById('weather');
        const apiKey = 'fbe9ed2bd4d3caedef17a2f42e43dc7d'; // Replace with your OpenWeatherMap API key.
        const geocodingApiKey =
            'AIzaSyC8v6o6VKtOzrbvcTP-BEnuEvZGeW7rSPk'; // Replace with your Google Geocoding API key.

        let lat, lon; // Define lat and lon variables at a higher scope.

        // Step 1: Use the Google Geolocation API to get the user's location
        fetch('https://www.googleapis.com/geolocation/v1/geolocate?key=' + geocodingApiKey, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({}),
            })
            .then(response => response.json())
            .then(locationData => {
                const {
                    location
                } = locationData;
                lat = location.lat; // Assign values to lat and lon here.
                lon = location.lng;

                // Step 2: Use the obtained location (lat, lon) to fetch location data from Google Geocoding API
                const geocodingApiUrl =
                    `https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lon}&key=${geocodingApiKey}`;

                return fetch(geocodingApiUrl);
            })
            .then(response => response.json())
            .then(geocodingData => {
                if (geocodingData.status === 'OK') {
                    const results = geocodingData.results;
                    const locationName = results[0].formatted_address;

                    // Display location information
                    locationElement.innerHTML = `Location: ${locationName}`;
                }

                // Step 3: Use the obtained location (lat, lon) to fetch weather data from OpenWeatherMap
                const weatherApiUrl =
                    `https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&appid=${apiKey}&units=metric`;

                return fetch(weatherApiUrl);
            })
            .then(response => response.json())
            .then(weatherData => {
                const temperature = weatherData.main.temp;
                const weatherIconCode = weatherData.weather[0].icon;
                const weatherIconUrl = `http://openweathermap.org/img/w/${weatherIconCode}.png`;
                const weatherDescription = weatherData.weather[0].description;

                const weatherHTML = `Weather:
            <img src="${weatherIconUrl}" alt="${weatherDescription}" width="35px">
            ${temperature.toFixed(1)}°C
        `;

                // Display weather information
                weatherElement.innerHTML = weatherHTML;
            })
            .catch(error => {
                console.error('Error fetching data:', error);
            });
    }


    // Get user's location and update weather
    if ('geolocation' in navigator) {
        navigator.geolocation.getCurrentPosition(position => {
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;
            updateWeather(latitude, longitude);
        }, error => {
            console.error('Error getting location:', error);
        });
    } else {
        console.error('Geolocation is not available.');
    }
    //updateWeather('14.683409', '100.706897');
    //getLocationAndWeather();

    function updateClock() {
        const datetimeElement = document.getElementById('real-time-clock');
        const now = new Date();

        // Thai language and desired formatting options
        const thaiOptions = {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false,
            timeZone: 'Asia/Bangkok'
        };

        // Format the date and time
        const thaiDateTimeString = now.toLocaleString('th-TH', thaiOptions);

        // Create the complete text  <i class="fas fa-clock"></i> เวลา: ${thaiDateTimeString.slice(11)}
        const text = `<i class="fas fa-calendar"></i> Date: ${thaiDateTimeString.slice(0, 10)}
        &nbsp;&nbsp;<i class="fas fa-clock"></i> Time: ${thaiDateTimeString.slice(11)}`;

        datetimeElement.innerHTML = text;

    }

    // Update the clock immediately and then every second
    updateClock();
    setInterval(updateClock, 1000);

    $(window).scroll(function() {
        if ($(this).scrollTop() > 100) {
            $('.scroll-to-top').fadeIn();
        } else {
            $('.scroll-to-top').fadeOut();
        }
    });

    $('.scroll-to-top').click(function() {
        $('html, body').animate({
            scrollTop: 0
        }, 800);
        return false;
    });

    function initializeTooltips() {
        $('[data-toggle="tooltip"]').tooltip();
    }

    function ezBSAlert(options) {
        var deferredObject = $.Deferred();
        var defaults = {
            type: "alert", //alert, prompt,confirm
            modalSize: 'modal-sm', //modal-sm, modal-lg
            okButtonText: 'OK',
            cancelButtonText: 'Cancel',
            yesButtonText: 'Yes',
            noButtonText: 'No',
            headerText: 'Attention',
            messageText: 'Message',
            alertType: 'default', //default, primary, success, info, warning, danger
            inputFieldType: 'text', //could ask for number,email,etc
        }
        $.extend(defaults, options);

        var _show = function() {
            var headClass = "navbar-default";
            switch (defaults.alertType) {
                case "primary":
                    headClass = "alert-primary";
                    break;
                case "success":
                    headClass = "alert-success";
                    break;
                case "info":
                    headClass = "alert-info";
                    break;
                case "warning":
                    headClass = "alert-warning";
                    break;
                case "danger":
                    headClass = "alert-danger";
                    break;
            }
            $('BODY').append(
                '<div id="ezAlerts" class="modal fade">' +
                '<div class="modal-dialog" class="' + defaults.modalSize + '">' +
                '<div class="modal-content">' +
                '<div id="ezAlerts-header" class="modal-header ' + headClass + '">' +
                '<h4 id="ezAlerts-title" class="modal-title">Modal title</h4>' +
                '<button id="close-button" type="button" class="close" data-bs-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>' +
                '</div>' +
                '<div id="ezAlerts-body" class="modal-body">' +
                '<div id="ezAlerts-message" ></div>' +
                '</div>' +
                '<div id="ezAlerts-footer" class="modal-footer">' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>'
            );

            $('.modal-header').css({
                'padding': '15px 15px',
                '-webkit-border-top-left-radius': '5px',
                '-webkit-border-top-right-radius': '5px',
                '-moz-border-radius-topleft': '5px',
                '-moz-border-radius-topright': '5px',
                'border-top-left-radius': '5px',
                'border-top-right-radius': '5px'
            });

            $('#ezAlerts-title').text(defaults.headerText);
            $('#ezAlerts-message').html(defaults.messageText);

            var keyb = "false",
                backd = "static";
            var calbackParam = "";
            switch (defaults.type) {
                case 'alert':
                    keyb = "true";
                    backd = "true";
                    $('#ezAlerts-footer').html('<button class="btn btn-' + defaults.alertType + '">' + defaults
                        .okButtonText + '</button>').on('click', ".btn", function() {
                        calbackParam = true;
                        $('#ezAlerts').modal('hide');
                    });
                    break;
                case 'confirm':
                    var btnhtml = '<button id="ezok-btn" class="btn btn-primary">' + defaults.yesButtonText +
                        '</button>';
                    if (defaults.noButtonText && defaults.noButtonText.length > 0) {
                        btnhtml += '<button id="ezclose-btn" class="btn btn-default">' + defaults.noButtonText +
                            '</button>';
                    }
                    $('#ezAlerts-footer').html(btnhtml).on('click', 'button', function(e) {
                        if (e.target.id === 'ezok-btn') {
                            calbackParam = true;
                            $('#ezAlerts').modal('hide');
                        } else if (e.target.id === 'ezclose-btn') {
                            calbackParam = false;
                            $('#ezAlerts').modal('hide');
                        }
                    });
                    break;
                case 'prompt':
                    $('#ezAlerts-message').html(defaults.messageText +
                        '<br /><br /><div class="form-group"><input type="' + defaults.inputFieldType +
                        '" class="form-control" id="prompt" /></div>');
                    $('#ezAlerts-footer').html('<button class="btn btn-primary">' + defaults.okButtonText +
                        '</button>').on('click', ".btn", function() {
                        calbackParam = $('#prompt').val();
                        $('#ezAlerts').modal('hide');
                    });
                    break;
            }

            $('#ezAlerts').modal({
                show: false,
                backdrop: backd,
                keyboard: keyb === "true",
            }).on('hidden.bs.modal', function(e) {
                $('#ezAlerts').remove();
                deferredObject.resolve(calbackParam);
            }).on('shown.bs.modal', function(e) {
                if ($('#prompt').length > 0) {
                    $('#prompt').focus();
                }
            }).modal('show');
        }

        _show();
        return deferredObject.promise();
    }
    //hide logo when resize
    $(document).ready(function() {

        /*  $(".nav-item").on("mouseleave", function () {
             $(this).removeClass("menu-open");
         }); */

        $('.sidebar-toggle-btn').on('click', function() {
            // Get the logo element
            var logo = $('#logo');

            // Check if the sidebar is being opened or closed
            if (logo.is(':visible')) {
                // Hide the logo when the sidebar is toggled
                logo.hide();
            } else {
                // Show the logo when the sidebar is toggled
                logo.show();
            }
        });

        initializeTooltips();

        $('#Listview').on('draw.dt', function() {
            initializeTooltips();
        });

    });

    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": false,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    }

    window.onload = function() {
        var loadingElement = document.getElementById('loadingi');
        if (loadingElement) {
            setTimeout(function() {
                loadingElement.style.display = 'none';
            }, 1500);

        }
    };
</script>
@yield('script')

</html>
{{-- </x-laravel-ui-adminlte::adminlte-layout> --}}
