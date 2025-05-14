<nav class="main-header navbar navbar-expand  border-bottom">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
                <a class="nav-link" href="{{ route('user.dashboard') }}"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ route('admin.dashboard') }}" class="nav-link">Thống kê</a>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="userManagementDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Quản lý tài khoản
            </a>
            <div class="dropdown-menu" aria-labelledby="userManagementDropdown">
                <a href="{{ route('admin.user.index') }}" class="dropdown-item">Danh sách</a>
                <a href="{{ route('admin.user.create') }}" class="dropdown-item">Thêm tài khoản</a>
            </div>
        </li>

    </ul>

   <!-- Right navbar links -->
<ul class="navbar-nav ml-auto">
    <li class="nav-item d-flex align-items-center">
        <span class="nav-link">{{ Auth::user()->name }}</span>
    </li>
    <li class="nav-item">
        <a class="nav-link d-flex align-items-center" href="#" onclick="confirmLogout(event)">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </li>
</ul>

<!-- Form ẩn - Đặt ngoài UL -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
    @csrf
</form>


</nav>

<script>
    function confirmLogout(event) {
        event.preventDefault();
        if (confirm("Bạn có chắc chắn muốn đăng xuất không?")) {
            document.getElementById('logout-form').submit();
        }
    }
</script>

<style>
    .dropdown-item {
        color: black !important;
    }

    .dropdown-item:focus,
    .dropdown-item:active {
        background-color: rgba(201, 198, 198, 0.77) !important;

    }

    .active {
        background-color: #f1f1f1 !important;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: background-color 0.2s ease;
    }
</style>
