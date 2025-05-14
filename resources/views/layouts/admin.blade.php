<!DOCTYPE html>
<html lang="en" style="height: 100%;">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kanban App')</title>

    <!-- Fonts and Icons -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('plugins/bootstrap/css/bootstrap.min.css') }}">
    <!-- <link rel="stylesheet" href="{{ asset('assets/css/OverlayScrollbars.min.css') }}"> -->
    <!-- <link rel="stylesheet" href="{{ asset('assets/css/adminlte.css') }}"> -->
    <link rel="stylesheet" href="{{ asset('assets/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/jquery/jquery-ui.css')}}">
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@1.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
</head>

<body class="sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed" style="height: 100%;">
    <div class="wrapper d-flex flex-column min-vh-100">

        {{-- Topbar --}}
        @include('components.admin_topbar') {{-- Sẽ tạo ở dưới --}}

        <div class="d-flex flex-grow-1">
            @include('components.admin_sidebar')
            {{-- Content --}}
            <div class="content-wrapper flex-grow-1 p-3">
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
    <script src="{{ asset('assets/js/adminlte.js') }}"></script>
    <script>
        window.routeUrls = {
            userIndex: @json(route('admin.user.index')),
            userList: @json(route('admin.user.sidebar')),
            userStore: @json(route('admin.user.store')),
            userShowBase: @json(route('admin.user.show', ['id' => ':userIdPlaceholder'])),
            userUpdateBase: @json(route('admin.user.update', ['id' => ':userIdPlaceholder'])),
            userDestroyBase: @json(route('admin.user.destroy', ['id' => ':userIdPlaceholder'])),
        };
        window.csrfToken = "{{ csrf_token() }}";
    </script>
    <script src="{{ asset('assets/js/admin.js')}}"></script>
    <script src="{{ asset('plugins/jquery/jquery-ui.min.js')}}"></script>
    <script src="{{ asset('assets/js/permission.js') }}"></script>


</body>

</html>