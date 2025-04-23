@extends('layouts.auth')

@section('title', 'Đăng nhập')

@section('content')
  <div class="login-box">
    <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <a href="#" class="h1"><b>My</b>App</a>
    </div>

    <div class="card">
      <div class="card-body login-card-body">
      <p class="login-box-msg">Đăng nhập để bắt đầu phiên làm việc</p>

      <form action="{{ route('login') }}" method="POST">
        @csrf
        <div class="input-group mb-3">
        <input type="email" name="email" class="form-control" placeholder="Email" value="{{ old('email') }}"
          required autofocus>
        <div class="input-group-append">
          <div class="input-group-text h-100">
          <span class="fas fa-envelope"></span>
          </div>
        </div>
        </div>

        <div class="input-group mb-3">
        <input type="password" name="password" class="form-control" placeholder="Mật khẩu" required>
        <div class="input-group-append">
          <div class="input-group-text h-100">
          <span class="fas fa-lock"></span>
          </div>
        </div>
        </div>

        <div class="row">
        <div class="col-7">
          <div class="icheck-primary">
          <input type="checkbox" id="remember" name="remember">
          <label for="remember">
            Ghi nhớ đăng nhập
          </label>
          </div>
        </div>

        <div class="col-5 text-end">
          <button type="submit" class="btn btn-primary">Đăng nhập</button>
        </div>
        </div>
      </form>

      <p class="mb-1 mt-3">
        <a href="#">Quên mật khẩu?</a>
      </p>
      <p class="mb-0">
        <a href="{{ route('register') }}" class="text-center">Chưa có tài khoản? Đăng ký</a>
      </p>
      </div>
    </div>
    </div>
  @endsection