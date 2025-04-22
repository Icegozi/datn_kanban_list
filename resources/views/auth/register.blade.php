<form method="POST" action="{{ route('register') }}">
    @csrf
    <input type="text" name="name" placeholder="Họ tên" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Mật khẩu" required>
    <input type="password" name="password_confirmation" placeholder="Xác nhận mật khẩu" required>
    <button type="submit">Đăng ký</button>
</form>
