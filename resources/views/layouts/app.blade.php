<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"> {{-- Use Laravel's lang helper --}}

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token for AJAX requests if needed later -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Dynamic Title -->
    <title>@yield('title', 'KanBan List App')</title>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/icon" href="{{ asset_min('favicon.ico') }}" />

    <!-- Fonts -->
    <link
        href="https://fonts.googleapis.com/css?family=Poppins:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset_min('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset_min('assets/css/linearicons.css') }}">
    <link rel="stylesheet" href="{{ asset_min('assets/css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset_min('assets/css/slick.css') }}">
    <link rel="stylesheet" href="{{ asset_min('assets/css/slick-theme.css') }}">
    <link rel="stylesheet" href="{{ asset_min('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset_min('assets/css/bootsnav.css') }}">
    <link rel="stylesheet" href="{{ asset_min('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset_min('assets/css/responsive.css') }}">

    @vite(['resources/css/app.css'])
    @yield('head-extras')

</head>

<body>

    @yield('content')

    <script src="{{ asset_min('plugins/jquery/jquery-3.7.1.min.js') }}"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js"></script>

    <script src="{{ asset_min('assets/js/bootstrap.min.js') }}"></script>

    <script src="{{ asset_min('assets/js/bootsnav.js') }}"></script>

    <script src="{{ asset_min('assets/js/feather.min.js') }}"></script>

    <script src="{{ asset_min('assets/js/jquery.counterup.min.js') }}"></script>

    <script src="{{ asset_min('assets/js/waypoints.min.js') }}"></script>

    <script src="{{ asset_min('assets/js/slick.min.js') }}"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>

    <script src="{{ asset_min('assets/js/custom.js') }}"></script>

    @yield('footer-scripts')

</body>

</html>
