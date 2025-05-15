<div class="d-flex flex-column flex-shrink-0 p-3 bg-light" style="width: 250px; height: auto;">
    <!-- Logo -->
    <a class="navbar-brand" href="#">My<span>App</span></a>
    <hr>

    <!-- Danh sách liên kết -->
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}" class="nav-link text-dark " aria-current="page">
                Thống kê
            </a>
        </li>
        <li class="nav-item dropdown">
    <a class="nav-link text-dark dropdown-toggle"
       href="#" id=userManagementDropdownSidebar"
       role="button" data-toggle="dropdown"
       aria-haspopup="true" aria-expanded="false">
        Quản lý tài khoản
    </a>
    <div class="dropdown-menu" aria-labelledby="userManagementDropdownSidebar">
        <!-- Các <a class="dropdown-item"> sẽ được thêm bằng jQuery -->
    </div>
</li>


    </ul>

</div>

<style>
    .dropdown-item:active,
    .dropdown-item:focus,
    .dropdown-item:hover {
        background-color: transparent !important;
        color: red !important;
    }

    .nav-link.dropdown-toggle:focus,
    .nav-link.dropdown-toggle:active,
    .nav-link.dropdown-toggle.show {
        background-color: black !important;
        color: white !important;
        box-shadow: none !important;
    }

    .nav-link:focus,
    .dropdown-item:focus {
        outline: none !important;
        box-shadow: none !important;
    }
</style>