<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (User::login($credentials)) {
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'login' => 'Email hoặc mật khẩu không đúng hoặc tài khoản đã bị khóa.',
        ]);
    }
}
