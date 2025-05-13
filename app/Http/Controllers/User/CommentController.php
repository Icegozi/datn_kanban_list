<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    private function authorizeTaskAccess(Task $task, array $requiredPermissions = [])
    {
        $user = Auth::user();
        $board = $task->column->board;
        foreach ($requiredPermissions as $permission) {
            if ($user->hasBoardPermission($board, $permission)) {
                return $board;
            }
        }
        abort(403, 'Bạn không có quyền truy cập!');
    }

    public function store(Request $request, Task $task) {
        $this->authorizeTaskAccess($task,['board_viewer','board_editor','board_member_manager']);
        $request->validate(['content' => 'required|string']);

        try {
            $comment = $task->comments()->create([
                'user_id' => Auth::id(),
                'content' => $request->content,
            ]);
            $comment->load('user'); 

            $taskHistory = new TaskHistory();
            $taskHistory->logTaskHistory($task,"thêm bình luận");
            $comment->user_avatar = $comment->user ? ('https://i.pravatar.cc/40?u=' . urlencode($comment->user->email)) : 'https://i.pravatar.cc/40?u=unknown';
            return response()->json([
                'success' => true, 'message' => 'Bình luận đã được thêm.',
                'comment' => $comment 
            ]);
        } catch (\Exception $e) {
            Log::error("Error adding comment to task {$task->id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể thêm bình luận.'], 500);
        }
    }

    public function destroy(Task $task, $commentId)
    {
        $this->authorizeTaskAccess($task,['board_member_manager']);
        try {
            $comment = $task->comments()->findOrFail($commentId);

            // Kiểm tra quyền xóa bình luận của người dùng
            if ($comment->user_id !== Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Bạn không có quyền xóa bình luận này.'], 403);
            }

            // Xóa bình luận
            $comment->delete();
            $taskHistory = new TaskHistory();
            $taskHistory->logTaskHistory($task,"xóa bình luận");
            return response()->json([
                'success' => true,
                'message' => 'Bình luận đã được xóa.'
            ]);
        } catch (\Exception $e) {
            Log::error("Error deleting comment for task {$task->id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể xóa bình luận.'], 500);
        }
    }

}
