<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function showLoginForm(){
        return view('auth.login');
    }
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (User::login($credentials)) {
            $user = Auth::user();

            if ($user->is_admin) {
                return redirect()->route('admin.dashboard');
            } else {
                return redirect()->route('user.dashboard');
            }
        } else {
            return back()->withErrors([
                'email' => 'Thông tin đăng nhập không hợp lệ hoặc tài khoản đã bị khóa.',
            ])->withInput();
        }
    }

    public function logout(Request $request)
    {
        User::logout();
        return redirect('/login');
    }

}
