@extends('layouts.admin')

@section('title', 'Thêm người dùng')

@section('content')
    <h3>Thêm tài khoản mới</h3>
    @if ($errors->any())
        <script>
            let errorMessages = {!! json_encode($errors->all()) !!};
            alert(errorMessages.join("\n"));
        </script>
    @endif

    <div id="user-create-container">
        <form id="createUserForm" method="POST" action="{{ route('admin.user.store') }}">
            @csrf

            <table class="table table-bordered mt-4">
                <tbody>
                    <tr>
                        <th scope="row">Họ và tên</th>
                        <td><input type="text" class="form-control" id="userName" name="name" value="{{ old('name') }}" required></td>
                    </tr>

                    <tr>
                        <th scope="row">Email</th>
                        <td><input type="email" class="form-control" id="userEmail" name="email" value="{{ old('email') }}" required></td>
                    </tr>

                    <tr>
                        <th scope="row" colspan="2">
                            <hr>
                            <p class="text-muted"><em>Bạn nên đặt mật khẩu có ít nhất 8 kí tự, ít nhất một chữ hoa, một chữ thường và 1 ki tự đặt biệt cho tài khoản mới.</em></p>
                        </th>
                    </tr>

                    <tr>
                        <th scope="row">Mật khẩu</th>
                        <td><input type="password" class="form-control" id="userPassword" name="password" placeholder="Nhập mật khẩu" required></td>
                    </tr>

                    <tr>
                        <th scope="row">Xác nhận mật khẩu</th>
                        <td><input type="password" class="form-control" id="userPasswordConfirmation" name="password_confirmation" placeholder="Xác nhận mật khẩu" required></td>
                    </tr>

                    <tr>
                        <th scope="row" colspan="2">
                            <hr>
                        </th>
                    </tr>

                    <tr>
                        <th scope="row">Trạng thái</th>
                        <td>
                            <select class="form-control" id="userStatus" name="status" required>
                                <option value="active">Kích hoạt</option>
                                <option value="inactive">Không kích hoạt</option>
                                <option value="banned">Bị khóa</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Là quản trị viên</th>
                        <td>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="isAdmin" name="is_admin" value="1">
                                <label class="form-check-label" for="isAdmin">Là quản trị viên (Is Admin)</label>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-outline-dark mr-2">Thêm tài khoản</button>
                <a href="{{ route('admin.user.index') }}" class="btn btn-danger">Hủy</a>
            </div>

        </form>
    </div>
@endsection
