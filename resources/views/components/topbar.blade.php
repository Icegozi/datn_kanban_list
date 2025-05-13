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
            <a href="{{ route('user.dashboard') }}" class="nav-link">Bảng của tôi</a>
        </li>
        <li class="nav-item dropdown"> {{-- Added dropdown class --}}
            <a class="nav-link dropdown-toggle {{ request()->routeIs('boards.settings') ? 'active' : 'text-dark' }}"
                href="#" id="boardPermissionsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false">
                Mời thành viên
            </a>
            <div class="dropdown-menu" aria-labelledby="boardPermissionsDropdown">
                @php
                    $ownedBoardsForDropdown = Auth::user() ? Auth::user()->boardsOwned()->orderBy('name')->get() : collect();
                @endphp

                @if($ownedBoardsForDropdown->isNotEmpty())
                    @foreach($ownedBoardsForDropdown as $boardItem)
                        <a class="dropdown-item {{ request()->routeIs('boards.settings') && request()->route('board') && request()->route('board')->id == $boardItem->id ? 'active' : '' }}"
                            href="{{ route('boards.settings', $boardItem) }}" style="color: black !important;">
                            <i class="fas fa-cog fa-fw mr-2"></i> {{ $boardItem->name }}
                        </a>
                    @endforeach

                @else
                    <a class="dropdown-item disabled" style="color: black !important;" href="#">Vui lòng tạo thêm bảng!</a>
                @endif
            </div>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <li class="nav-item mt-2">
            {{ Auth::user()->name }}
        </li>
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