<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | AU-SARF Tracer System</title>
    <link rel="icon" type="image/png" href="{{ asset('img/logo/arellano_logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('img/logo/arellano_logo.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/searchable-select.css') }}">

</head>
<body>
  
<div class="container">
    @yield('content')
</div>
<script src="{{ asset('js/searchable-select.js') }}"></script>
</body>
</html>
