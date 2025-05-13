<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\BoardInvitation;
use App\Models\BoardPermission;
use App\Models\Permission;
use App\Models\PermissionUser;
use App\Models\User;
use App\Notifications\BoardInvitationNotification;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;
use Str;
use Illuminate\Support\Facades\URL;

class BoardMembershipController extends Controller
{
    // Helper function to grant a permission to a user for a board
    private function grantBoardPermission(Board $board, User $user, string $permissionName): bool
    {
        $permission = Permission::firstWhere('name', $permissionName);
        if (!$permission) {
            \Log::error("Permission {$permissionName} not found.");
            return false;
        }

        // Find or create the permission_users link
        $permissionUser = PermissionUser::firstOrCreate(
            ['user_id' => $user->id, 'permission_id' => $permission->id]
        );

        // Link this permission_user to the board
        BoardPermission::firstOrCreate(
            ['board_id' => $board->id, 'permission_user_id' => $permissionUser->id]
        );
        return true;
    }

    // Helper function to revoke a permission from a user for a board
    private function revokeBoardPermission(Board $board, User $user, string $permissionName): bool
    {
        $permission = Permission::firstWhere('name', $permissionName);
        if (!$permission)
            return false;

        $permissionUser = PermissionUser::where('user_id', $user->id)
            ->where('permission_id', $permission->id)
            ->first();
        if (!$permissionUser)
            return true; // Already doesn't have it

        // Delete the board_permissions link
        BoardPermission::where('board_id', $board->id)
            ->where('permission_user_id', $permissionUser->id)
            ->delete();

        // Optional: Clean up permission_users if this user no longer has this permission for ANY board
        // and no other system relies on this specific permission_user_id. This can be complex.
        // For now, we leave the permission_users entry.

        return true;
    }
    // Helper to revoke ALL board-specific permissions for a user on a board
    private function revokeAllBoardPermissionsForUser(Board $board, User $user)
    {
        $permissionUserIds = PermissionUser::where('user_id', $user->id)->pluck('id');
        BoardPermission::where('board_id', $board->id)
            ->whereIn('permission_user_id', $permissionUserIds)
            ->delete();
    }


    public function settings(Board $board) // Route model binding for $board
    {
        // Authorization: Only board owner or those with 'board_member_manager' permission
        if (Auth::id() !== $board->user_id && !Auth::user()->hasBoardPermission($board, 'board_member_manager')) {
            abort(403, 'Bạn không có quyền truy cập cài đặt của bảng này.');
        }

        // Eager load necessary relationships for the view
        // The 'owner' is already available if $board->user_id is the foreign key
        // $board->load(['owner']); // Already available as $board->owner if relation is set up

        $membersData = $board->getMembersWithRoles();
        $pendingInvitations = $board->invitations()->with('inviter')->whereNull('accepted_at')->get();

        $potentialRoles = [
            'board_viewer' => 'Người xem',
            'board_editor' => 'Người chỉnh sửa',
            'board_member_manager' => 'Người quản lý',
        ];

        // Ensure the view name matches what you have
        return view('user.boards.settings_adapted', compact('board', 'membersData', 'pendingInvitations', 'potentialRoles'));
    }


    public function inviteMember(Request $request, Board $board)
    {
        if (Auth::id() !== $board->user_id && !Auth::user()->hasBoardPermission($board, 'board_member_manager')) {
            abort(403, 'Bạn không có quyền mời thành viên.');
        }

        $request->validate([
            'email' => 'required|email',
            'role_permission_name' => 'required|string|exists:permissions,name', // Ensure role is a valid permission name
        ]);

        $emailToInvite = $request->input('email');
        $permissionNameToGrant = $request->input('role_permission_name');

        $invitedUser = User::firstWhere('email', $emailToInvite);

        if ($invitedUser && $invitedUser->id === $board->user_id) {
            return back()->with('error', 'Người dùng này đã là chủ sở hữu của bảng.');
        }
        if ($invitedUser && $invitedUser->hasBoardPermission($board, $permissionNameToGrant)) {
            // Or even if they have *any* role already.
            return back()->with('error', 'Người dùng này đã có vai trò tương tự hoặc cao hơn trong bảng.');
        }

        // --- Using BoardInvitations table (Recommended) ---
        $existingInvitation = BoardInvitation::where('board_id', $board->id)
            ->where('email', $emailToInvite)->whereNull('accepted_at')->first();
        if ($existingInvitation) {
            return back()->with('warning', 'Đã có lời mời đang chờ xử lý cho email này.');
        }
        do {
            $token = Str::random(40);
        } while (BoardInvitation::where('token', $token)->exists());
        $invitation = BoardInvitation::create([
            'board_id' => $board->id,
            'email' => $emailToInvite,
            'token' => $token,
            'role_permission_name' => $permissionNameToGrant,
            'invited_by' => Auth::id(),
            'expires_at' => now()->addDays(7),
        ]);
        try {
            \Illuminate\Support\Facades\Notification::route('mail', $emailToInvite)->notify(new BoardInvitationNotification($invitation));
        } catch (\Exception $e) {
            \Log::error("Failed to send invitation: " . $e->getMessage());
            $invitation->delete();
            return back()->with('error', 'Không thể gửi email mời. Vui lòng kiểm tra cấu hình mail.');
        }
        return back()->with('success', 'Lời mời đã được gửi tới ' . $emailToInvite);

    }

    public function acceptInvitation(Request $request, $token)
    {
        if (!$request->hasValidSignature()) {
            abort(401, 'Liên kết mời không hợp lệ hoặc đã hết hạn.');
        }

        $invitation = BoardInvitation::where('token', $token)->whereNull('accepted_at')->first();
        if (!$invitation) {
            abort(404, 'Không tìm thấy lời mời hoặc đã được chấp nhận.');
        }
        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            $invitation->delete();
            abort(401, 'Lời mời này đã hết hạn.');
        }

        if (!Auth::check()) {
            session(['url.intended' => URL::full()]);
            return redirect()->route('login.form')->with('info', 'Vui lòng đăng nhập hoặc đăng ký để chấp nhận lời mời.');
        }
        $user = Auth::user();
        if ($user->email !== $invitation->email) {
            Auth::logout();
            session(['url.intended' => URL::full()]);
            return redirect()->route('login.form')->with('error', 'Lời mời này dành cho một tài khoản email khác.');
        }

        DB::transaction(function () use ($invitation, $user) {
            if (!$this->grantBoardPermission($invitation->board, $user, $invitation->role_permission_name)) {
                // This scenario should be rare if permission exists, but good to handle
                throw new \Exception("Không thể cấp quyền '{$invitation->role_permission_name}'.");
            }
            $invitation->update(['accepted_at' => now()]);
        });

        return redirect()->route('boards.show', $invitation->board_id)->with('success', 'Bạn đã tham gia thành công vào bảng ' . $invitation->board->name);
    }

    public function updateMemberRole(Request $request, Board $board, User $member)
    {
        if (Auth::id() !== $board->user_id && !Auth::user()->hasBoardPermission($board, 'board_member_manager')) {
            return response()->json(['success' => false, 'message' => 'Không có quyền.'], 403);
        }
        if ($member->id === $board->user_id) {
            return response()->json(['success' => false, 'message' => 'Không thể thay đổi vai trò của chủ sở hữu.'], 400);
        }

        $request->validate([
            'new_role_permission_name' => 'required|string|exists:permissions,name',
        ]);
        $newPermissionName = $request->input('new_role_permission_name');

        DB::transaction(function () use ($board, $member, $newPermissionName) {
            // 1. Revoke all existing board-specific permissions for this user on this board
            $this->revokeAllBoardPermissionsForUser($board, $member);

            // 2. Grant the new permission
            if (!$this->grantBoardPermission($board, $member, $newPermissionName)) {
                throw new \Exception("Failed to grant new permission {$newPermissionName}.");
            }
        });

        return response()->json(['success' => true, 'message' => 'Vai trò của thành viên đã được cập nhật.']);
    }

    public function removeMember(Board $board, User $member)
    {
        if (Auth::id() !== $board->user_id && !Auth::user()->hasBoardPermission($board, 'board_member_manager')) {
            return response()->json(['success' => false, 'message' => 'Không có quyền.'], 403);
        }
        if ($member->id === $board->user_id) {
            return response()->json(['success' => false, 'message' => 'Không thể xóa chủ sở hữu.'], 400);
        }

        DB::transaction(function () use ($board, $member) {
            $this->revokeAllBoardPermissionsForUser($board, $member);
            // Also cancel any pending invitations for this user on this board
            BoardInvitation::where('board_id', $board->id)->where('email', $member->email)->delete(); // If using invitations
        });

        return response()->json(['success' => true, 'message' => 'Thành viên đã được xóa khỏi bảng.']);
    }

    public function cancelInvitation(Board $board, BoardInvitation $invitation) // If using BoardInvitations
    {
        if ((Auth::id() !== $board->user_id && !Auth::user()->hasBoardPermission($board, 'board_member_manager')) || $invitation->board_id !== $board->id) {
            abort(403);
        }
        $invitation->delete();
        return back()->with('success', 'Lời mời đã được hủy.');
    }
}
