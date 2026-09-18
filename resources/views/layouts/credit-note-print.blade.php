<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ config('app.name') }} — {{ __('credit-note.navigation.title') }}</title>
    <style>
        @include('credit-notes.partials.document-styles')
    </style>
    @yield('style')
</head>

<body class="credit-note-print-body">
    <div class="credit-note-print-shell">
        <nav class="credit-note-print-toolbar" aria-label="{{ __('credit-note.actions.print') }}">
            <a class="btn btn-outline" href="{{ route('credit-notes.index') }}">&larr; {{ __('credit-note.actions.back_to_register') }}</a>
            <div class="credit-note-print-toolbar-title"><span aria-hidden="true">&#128722;</span> {{ __('credit-note.actions.print') }}</div>
            <button class="btn btn-primary" type="button" onclick="window.print()">{{ __('credit-note.actions.print') }}</button>
        </nav>
        @yield('content')
    </div>
    <script>
        window.addEventListener('beforeprint', function () {
            document.body.classList.add('is-printing');
        });
        window.addEventListener('afterprint', function () {
            document.body.classList.remove('is-printing');
        });
    </script>
</body>

</html>
