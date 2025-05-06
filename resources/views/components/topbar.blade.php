<nav class="main-header navbar navbar-expand  border-bottom">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ url('/') }}" class="nav-link">Trang chủ</a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ route('user.dashboard') }}" class="nav-link">Board của tôi</a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
    <li class="nav-item">
        <a class="nav-link" href="#" onclick="confirmLogout(event)">
            <i class="fas fa-sign-out-alt"></i> 
        </a>
    </li>
    <!-- Form ẩn -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>
</ul>
</nav>

<script>
    function confirmLogout(event) {
        event.preventDefault();
        if (confirm("Bạn có chắc chắn muốn đăng xuất không?")) {
            document.getElementById('logout-form').submit();
        }
    }
</script>