<!DOCTYPE html>
<html lang="en" style="height: 100%;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Kanban App')</title>

    <!-- Fonts and Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/OverlayScrollbars.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/adminlte.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/jquery/jquery-ui.css')}}">
    
</head>

<body class="sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed" style="height: 100%;">
    <div class="wrapper d-flex flex-column min-vh-100">
    
        {{-- Topbar --}}
        @include('components.topbar') {{-- Sẽ tạo ở dưới --}}
        
        <div class="d-flex flex-grow-1">
            @include('components.sidebar')
            {{-- Content --}}
            <div class="content-wrapper flex-grow-1 p-3">
                <div class="sheep-wrapper" id="draggable-sheep">
                    <div class="sheep">
                        <div class="wool"></div>
                        <div class="face">
                            <div class="eye left"></div>
                            <div class="eye right"></div>
                            <div class="cheek left"></div>
                            <div class="cheek right"></div>
                            <div class="mouth"></div>
                            <div class="horn left"></div>
                            <div class="horn right"></div>
                        </div>
                        <div class="hands"></div>
                    </div>
                </div>
                <div class="cute-border w-100 h-100">
                    @yield('content')
                </div>
            </div>
        </div>
    
        {{-- Footer --}}
        @include('components.footer')
    
    </div>

{{-- Scripts --}}
<script src="{{ asset('plugins/jquery/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('plugins/overlayscrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
<script src="{{ asset('assets/js/adminlte.js') }}"></script>
<script src="{{ asset('assets/js/admin.js')}}"></script>
<script src="{{ asset('plugins/jquery/jquery-ui.min.js')}}"></script>

</body>
</html>
