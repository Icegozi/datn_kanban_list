@extends('layouts.admin')

@section('content')
    <div class="container ">

        <!-- Overlay sẽ được tạo bằng jQuery -->
        <div id="cardOverlay"></div>

        
        {{-- 🔍 Thanh tìm kiếm --}} <div class="row mb-3">
        <div class="col-md-3">
            <form method="GET" action="#">
                <div class="input-group ">
                    <input type="text" name="search" class="form-control" placeholder="Tìm kiếm bảng làm việc">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit">Tìm</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">

        {{-- Giao diện các board theo dạng folder/file --}}
        @for ($i = 0; $i < 12; $i++)
        <div class="col-md-4 col-lg-3 mb-4 card-drop-target">
                <!-- Thay đổi col-* nếu cần bố cục khác -->
                <div class="card shadow-sm h-100 card-hover">
                    {{-- Không dùng card-header ở đây nữa để linh hoạt hơn với dropdown --}}
                    {{-- <div class="card-header bg-transparent pt-3 pb-2 px-3 border-bottom"> --}}
                    <div class="card-body p-3 d-flex flex-column">
                        {{-- Phần Header tùy chỉnh bên trong card-body --}}
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <h6 class="mb-0 text-truncate font-weight-bold">Bảng {{ $i + 1 }}</h6>

                            <!-- Dropdown -->
                            <div class="dropdown">
                                <a href="#" class="text-muted dropdown-toggle-no-caret"
                                    id="itemMenu{{ $i }}" data-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="false" aria-label="Tùy chọn bảng">
                                    <i class="fas fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right"
                                    aria-labelledby="itemMenu{{ $i }}">
                                    <a class="dropdown-item" href="#">
                                        <i class="fas fa-folder-open fa-fw mr-2 text-muted"></i>Mở
                                    </a>
                                    <a class="dropdown-item" href="#">
                                        <i class="fas fa-pencil-alt fa-fw mr-2 text-muted"></i>Sửa tên
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="#">
                                        <i class="fas fa-trash-alt fa-fw mr-2"></i> Xoá
                                    </a>
                                </div>
                            </div>
                        </div>

                        <p class="text-muted small mb-3">
                            Mô tả ngắn của bảng {{ $i + 1 }}. Thay đổi được.
                            {{-- Hoặc dùng placeholder nếu chưa có mô tả --}}
                            {{-- <span class="font-italic">Chưa có mô tả.</span> --}}
                        </p>

                        {{-- Đẩy timestamp xuống cuối card --}}
                        <div class="d-flex align-items-center text-danger small mt-auto">
                            <i class="far fa-clock fa-fw mr-2"></i>
                            {{ \Carbon\Carbon::now()->subDays(rand(1, 30))->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>
        @endfor

    </div>
    </div>
@endsection
