<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prevent Back Navigation</title>
    <script>
        window.addEventListener('popstate', function(event) {
            // Redirect to the home page
            window.location.href = '/contacts';  // Change '/' to the URL of your home page if it's different
        });

        // Push a new state to make sure the popstate event is triggered
        history.pushState(null, null, window.location.href);
    </script>
</head>
<body>
    <h1>Current Page</h1>
    <p>This page prevents users from navigating back.</p>
    <a href="{{ route('home') }}" class="btn btn-primary">home</a>
</body>
</html>
