@extends('layouts.admin')

@section('title', 'Danh sách tài khoản')

@section('content')
    <div class="container">
        <h3 class="mb-4">Danh sách người dùng</h3>
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr class="text-center">
                    <th>ID</th>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>Quyền</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Cập nhật</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td class="text-center">{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td class="text-center">
                            @if ($user->is_admin)
                                <span class="badge badge-success">Admin</span>
                            @else
                                <span class="badge badge-secondary">User</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if ($user->status === 'active')
                                <span class="badge badge-primary">Kích hoạt</span>
                            @elseif ($user->status === 'inactive')
                                <span class="badge badge-warning">Không kích hoạt</span>
                            @elseif ($user->status === 'banned')
                                <span class="badge badge-danger">Bị khóa</span>
                            @else
                                <span class="badge badge-light">{{ $user->status }}</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $user->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-center">{{ $user->updated_at->format('d/m/Y H:i') }}</td>
                        <td class="text-center">
                            <a href="javascript:void(0)" class="btn btn-sm btn-danger text-white delete-user"
                                data-id="{{ $user->id }}">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">Không có người dùng nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Hiển thị phân trang --}}
        <div class="d-flex justify-content-end mt-4">
            {{ $users->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endsection


@if (session('success'))
    <script>
        alert("{{ session('success') }}");
    </script>
@endif
