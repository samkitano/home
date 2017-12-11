<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Samy - Web Dev</title>

        <link type="text/css" rel="stylesheet" href="css/app.css">
        <script defer src="fa/js/fontawesome-all.js"></script>
    </head>

    <body>
        <div id="app">
            <app items="{{ json_encode($projects) }}"></app>
        </div>

        <script src="js/app.js"></script>
    </body>
</html>
