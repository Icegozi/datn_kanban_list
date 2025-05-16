<nav class="main-header navbar navbar-expand  border-bottom">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            @if (Auth::check() && Auth::user()->is_admin)
                <a class="nav-link" href="{{ route('admin.dashboard') }}"><i class="fas fa-bars"></i></a>
            @else
                <a class="nav-link" href="{{ route('user.dashboard') }}"><i class="fas fa-bars"></i></a>
            @endif
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ url('/') }}" class="nav-link">Trang chủ</a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ route('user.dashboard') }}" class="nav-link">Bảng của tôi</a>
        </li>
        <li class="nav-item dropdown"> {{-- Added dropdown class --}}
            <a class="nav-link dropdown-toggle {{ request()->routeIs('boards.settings') ? 'active' : 'text-while' }}"
                href="#" id="boardPermissionsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false">
                Mời thành viên
            </a>
            <div class="dropdown-menu" aria-labelledby="boardPermissionsDropdown">
                @php
                    $ownedBoardsForDropdown = Auth::user()
                        ? Auth::user()->boardsOwned()->orderBy('name')->get()
                        : collect();
                @endphp

                @if ($ownedBoardsForDropdown->isNotEmpty())
                    @foreach ($ownedBoardsForDropdown as $boardItem)
                        <a class="dropdown-item {{ request()->routeIs('boards.settings') && request()->route('board') && request()->route('board')->id == $boardItem->id ? 'active' : '' }}"
                            href="{{ route('boards.settings', $boardItem) }}">
                            <i class="fas fa-cog fa-fw mr-2"></i> {{ $boardItem->name }}
                        </a>
                    @endforeach
                @else
                    <a class="dropdown-item disabled" style="color: black" href="#">Vui lòng tạo thêm bảng!</a>
                @endif
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