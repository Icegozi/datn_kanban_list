<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::paginate(20);
        return view('admin.account.index', compact('users'));
    }

    public function create(Request $request)
    {
        return view('admin.account.create');
    }
    public function search(Request $request)
    {
        $users = User::searchUsers($request->query('q'))->paginate(20);
        return Response::json([
            'success' => true,
            'users' => $users,
        ]);
    }

    public function store(UserRequest $request)
    {
        User::addUser($request->all());
        return redirect()
            ->route('admin.user.index')
            ->with('success', 'Tài khoản đã được tạo thành công.');
    }

    public function show($id)
    {
        $user = User::find($id);

        return view('admin.account.show', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $ok = User::updateUserById($id, $request->all());

        if ($ok) {
            return Response::json([
                'success' => true,
                'message' => 'Cập nhật thành công',
            ]);
        }

        return Response::json([
            'success' => false,
            'message' => 'Không tìm thấy người dùng',
        ], 404);
    }

    public function destroy($id)
    {
        $ok = User::deleteUserById($id);

        if ($ok) {
            return Response::json([
                'success' => true,
                'message' => 'Đã xóa người dùng',
            ]);
        }

        return Response::json([
            'success' => false,
            'message' => 'Không thể tự xóa chính mình',
        ], 404);
    }

    public function getUserList()
    {
        $users = User::paginate(10);

        return response()->json([
            'success' => true,
            'users' => $users,
        ]);
    }

}
