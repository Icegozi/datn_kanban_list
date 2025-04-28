<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"> {{-- Use Laravel's lang helper --}}

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token for AJAX requests if needed later -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Dynamic Title -->
    <title>@yield('title', 'KanBan List App')</title> {{-- Default Title --}}

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/icon" href="{{asset('favicon.ico')}}" /> {{-- Ensure favicon.ico is in public/ --}}

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    
    <!-- Template CSS -->
    {{-- Ensure these files exist in the corresponding public/assets/... paths --}}
    {{-- <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}"> --}}
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/linearicons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/animate.css') }}"> {{-- Added from static html --}}
    {{-- <link rel="stylesheet" href="{{ asset('assets/css/flaticon.css') }}"> --}}
    <link rel="stylesheet" href="{{ asset('assets/css/slick.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/slick-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}"> {{-- Use the CSS version --}}
    <link rel="stylesheet" href="{{ asset('assets/css/bootsnav.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive.css') }}">

    <!-- Vite CSS (Optional - keep if you have global styles here, remove if template covers everything) -->
    @vite(['resources/css/app.css'])

    <!-- Add any other head elements like specific meta tags for sections here -->
    @yield('head-extras')

</head>

<body>
    <!--[if lte IE 9]>
        <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
    <![endif]-->

    {{-- Use a wrapper div if needed, or yield directly --}}
    {{-- <div class="main-wrapper"> --}}
        @yield('content')
    {{-- </div> --}}


    <!-- Template JavaScript (Load at the end) -->
    {{-- Ensure these files exist in the corresponding public/assets/... or public/plugins/... paths --}}
    {{-- Use the template's jQuery unless Vite's app.js provides it AND it's compatible --}}
    <script src="{{ asset('plugins/jquery/jquery-3.7.1.min.js') }}"></script> 

    <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js"></script>

    <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>

    <script src="{{ asset('assets/js/bootsnav.js') }}"></script>

    <script src="{{ asset('assets/js/feather.min.js') }}"></script> 

    <script src="{{ asset('assets/js/jquery.counterup.min.js') }}"></script>

    <script src="{{ asset('assets/js/waypoints.min.js') }}"></script> 

    <script src="{{ asset('assets/js/slick.min.js') }}"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>

    <script src="{{ asset('assets/js/custom.js') }}"></script> 

    @yield('footer-scripts')

</body>
</html>