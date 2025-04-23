<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        try {
            User::register($request->only('name', 'email', 'password'));
            session()->flash('success', 'Đăng ký thành công, bạn có thể đăng nhập ngay!');
            return redirect()->route('login'); 
        } catch (\Exception $e) {
            session()->flash('error', 'Đã có lỗi xảy ra, vui lòng thử lại!');
            return redirect()->back();
        }
    }

}
