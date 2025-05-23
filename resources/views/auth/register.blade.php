@extends('layouts.auth')

@section('title', 'Đăng ký tài khoản')

@section('content')
  <div class="register-box">
    <div class="card card-outline card-secondary">
    <div class="card-header text-center">
      <a href="#" class="h1"><b>My</b>App</a>
    </div>
    <div class="card-body">
      <p class="login-box-msg">Tạo tài khoản mới</p>

      <form action="{{ route('register') }}" method="POST">
      @csrf
      <div class="input-group mb-3">
        <input type="text" class="form-control" name="name" placeholder="Họ tên" value="{{ old('name') }}" required>
        <div class="input-group-append">
        <div class="input-group-text h-100"><span class="fas fa-user"></span></div>
        </div>
      </div>

      <div class="input-group mb-3">
        <input type="email" class="form-control" name="email" placeholder="Email" value="{{ old('email', request('email')) }}" required>
        <div class="input-group-append">
        <div class="input-group-text h-100"><span class="fas fa-envelope"></span></div>
        </div>
      </div>

      <div class="input-group mb-3">
        <input type="password" class="form-control" name="password" placeholder="Mật khẩu" required>
        <div class="input-group-append">
        <div class="input-group-text h-100"><span class="fas fa-lock"></span></div>
        </div>
      </div>

      <div class="input-group mb-3">
        <input type="password" class="form-control" name="password_confirmation" placeholder="Nhập lại mật khẩu"
        required>
        <div class="input-group-append">
        <div class="input-group-text h-100"><span class="fas fa-lock"></span></div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6 mb-2">
          <button type="submit" class="btn btn-dark btn-block font-weight-bold">
            Đăng ký
          </button>
        </div>
        <div class="col-md-6 d-flex align-items-center justify-content-md-end justify-content-center">
          <a href="{{ route('login.form') }}" class="text-dark" style="text-decoration: none;">
            Tôi đã có tài khoản
          </a>
        </div>
      </div>  
      </form>

    </div>
    </div>
  </div>
@endsection