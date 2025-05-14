@extends('layouts.admin')

@section('title', 'Sửa người dùng')

@section('content')
    <h3>Cập nhật tài khoản</h3>
    <div id="user-edit-container">
        <form id="editUserForm" method="POST" action="{{ route('admin.user.update', $user->id) }}">
            @csrf
            @method('PUT')

            <!-- Hidden input for User ID -->
            <input type="hidden" id="userId" name="id" value="{{ $user->id }}">

            <table class="table table-bordered mt-4">
                <tbody>
                    <tr>
                        <th scope="row">Họ và tên</th>
                        <td><input type="text" class="form-control" id="userName" name="name"
                                value="{{ old('name', $user->name) }}" required></td>
                    </tr>

                    <tr>
                        <th scope="row">Email</th>
                        <td><input type="email" class="form-control" id="userEmail" name="email"
                                value="{{ old('email', $user->email) }}" required></td>
                    </tr>

                    <tr>
                        <th scope="row" colspan="2">
                            <hr>
                            <p class="text-muted"><em>Để trống các trường mật khẩu nếu bạn không muốn thay đổi.</em></p>
                        </th>
                    </tr>

                    <tr>
                        <th scope="row">Mật khẩu mới</th>
                        <td><input type="password" class="form-control" id="userPassword" name="password"
                                placeholder="Nhập mật khẩu mới"></td>
                    </tr>

                    <tr>
                        <th scope="row">Xác nhận mật khẩu mới</th>
                        <td><input type="password" class="form-control" id="userPasswordConfirmation"
                                name="password_confirmation" placeholder="Xác nhận mật khẩu mới"></td>
                    </tr>

                    <tr>
                        <th scope="row" colspan="2">
                            <hr>
                        </th>
                    </tr>

                    <tr>
                        <th scope="row">Trạng thái</th>
                        <td>
                            <select class="form-control" id="userStatus" name="status">
                                <option value="active" {{ $user->status == 'active' ? 'selected' : '' }}>Kích hoạt 
                                </option>
                                <option value="inactive" {{ $user->status == 'inactive' ? 'selected' : '' }}>Không kích hoạt</option>
                                <option value="banned" {{ $user->status == 'banned' ? 'selected' : '' }}>Bị khóa</option>
                                </option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Là quản trị viên</th>
                        <td>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="isAdmin" name="is_admin" value="1" {{ $user->is_admin ? 'checked' : '' }}>
                                <label class="form-check-label" for="isAdmin">Là quản trị viên (Is Admin)</label>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Ngày tạo</th>
                        <td>{{ $user->created_at }}</td>
                    </tr>

                    <tr>
                        <th scope="row">Cập nhật lần cuối</th>
                        <td>{{ $user->updated_at }}</td>
                    </tr>

                    <tr>
                        <th scope="row">Email đã xác thực lúc</th>
                        <td>{{ $user->email_verified_at ? $user->email_verified_at : 'Chưa xác thực' }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-outline-dark mr-2">Lưu thay đổi</button>
                <a href="{{ route('admin.user.index') }}" class="btn btn-danger">Hủy</a>
            </div>

        </form>
    </div>
@endsection