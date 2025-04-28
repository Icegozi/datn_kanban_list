<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Xác thực')</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">

  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="{{ asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">

  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('assets/css/adminlte.min.css') }}">

  <!-- jquery -->
  <script src="{{ asset('plugins/jquery/jquery-3.7.1.min.js') }}"></script>

  {{-- js --}}
  <script>window.bgVideoUrl = "{{ asset('assets/vid/bg_auth.mp4') }}";</script>
  <script src="{{ asset('assets/js/auth.js') }}""></script>
</head>

<body class="hold-transition register-page">

   <!-- Hiển thị thông báo lỗi nếu có -->
  @if($errors->any())
    <script>
    $(document).ready(function () {
    let messages = @json($errors->all());
    let combined = messages.join('\n');
    alert(combined);
    });
    </script>
  @endif
  
  @yield('content')

  <!-- Scripts -->
  <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('dist/js/adminlte.min.js') }}"></script>
</body>

</html>