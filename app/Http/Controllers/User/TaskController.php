<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRequest;
use App\Http\Requests\UpdateTaskPositionRequest;
use App\Models\Board;
use App\Models\Column;
use App\Models\Task;
use App\Models\TaskHistory;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Log;

class TaskController extends Controller
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
        abort(403, 'Bạn không có quyền thực hiện thao tác!');
    }

    private function authorizeBoardAccess(Board $board, array $requiredPermissions = [])
    {
        $user = Auth::user();
        // Kiểm tra nếu người dùng có một trong các quyền yêu cầu
        foreach ($requiredPermissions as $permission) {
            if ($user->hasBoardPermission($board, $permission)) {
                return $board;
            }
        }

        abort(403, 'Bạn không có quyền thực hiện thao tác!');
    }
    public function store(TaskRequest $request, Column $column)
    {
        $board = $column->board;
        $this->authorizeBoardAccess($board,['board_editor','board_member_manager']);

        try {
            $taskInstance = new Task();
            $taskHistory = new TaskHistory();
            $data = $request->validated();
            $data['priority'] = $request->input('priority', 'normal');
            $createdTask = $taskInstance->createForColumn($column, $data);
            $action = "tạo";
            $taskHistory->logTaskHistory($createdTask, $action, $oldColumn->name ?? null, $newColumn->name ?? null);
            $createdTask->load('assignees');
            return response()->json([
                'success' => true,
                'message' => 'Công việc đã được tạo thành công!',
                'task' => [
                    'id' => $createdTask->id,
                    'title' => $createdTask->title,
                    'description' => $createdTask->description,
                    'priority' => $createdTask->priority,
                    'due_date' => $createdTask->due_date ? $createdTask->due_date->toDateString() : null,
                    'formatted_due_date' => $createdTask->due_date ? $createdTask->due_date->format('d M') : null, // Ví dụ: 25 Th04
                    'position' => $createdTask->position,
                    'column_id' => $createdTask->column_id,
                    'assignees' => $createdTask->assignees->map(function ($assignee) {
                        return ['id' => $assignee->id, 'name' => $assignee->name, 'email' => $assignee->email];
                    }),
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error("Error creating task in column {$column->id}: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
            return response()->json(['success' => false, 'message' => 'Không thể thêm nhiệm vụ mới. Vui lòng thử lại.'], 500);
        }
    }


    public function show(Task $task)
    {
        $this->authorizeTaskAccess($task,['board_viewer','board_editor','board_member_manager']);
        $task->loadDetails();

        if ($task->column) {
            $task->column_name = $task->column->name;
        } else {
            $task->column_name = 'N/A';
            Log::warning("Task ID {$task->id} is missing column relation when trying to show details.");
        }

        // Định dạng ngày nếu cần
        $task->formatted_due_date = $task->due_date ? $task->due_date->format('d/m/Y') : null;

        if ($task->task_histories instanceof \Illuminate\Database\Eloquent\Collection) {
           $task->task_histories->transform(function ($history) {
            $user = $history->user;

            $timestamp = $history->updated_at ?? $history->created_at;
            $formatted_time = $timestamp ? \Carbon\Carbon::parse($timestamp)->format('Y/m/d H:i:s') : 'Không rõ thời gian';

            return [
                'id' => $history->id,
                'user_id' => $user?->id,
                'user_name' => $user?->name ?? 'Người dùng không xác định',
                'user_avatar' => $user ? 'https://i.pravatar.cc/40?u=' . urlencode($user->email) : 'https://i.pravatar.cc/40?u=unknown',
                'action' => $history->action,
                'note' => $history->note,
                'created_at' => $history->created_at->format('Y/m/d H:i:s'),
                'updated_at' => $formatted_time,
            ];
        });
        } else {
            $task->task_histories = collect([]);
            Log::warning("Task ID {$task->id}: task_histories was not a collection, possibly null or load issue.");
        }

        if ($task->comments instanceof \Illuminate\Database\Eloquent\Collection) {
            $task->comments->transform(function ($comment) {
                $comment->user_name = $comment->user ? $comment->user->name : 'Người dùng không xác định';
                $comment->user_avatar = $comment->user ? ('https://i.pravatar.cc/40?u=' . urlencode($comment->user->email)) : 'https://i.pravatar.cc/40?u=unknown';
                $comment->time_ago = $comment->created_at ? $comment->created_at->diffForHumans() : 'Không rõ thời gian';
                return $comment;
            });
        } else {
            $task->comments = collect([]);
            Log::warning("Task ID {$task->id}: comments was not a collection, possibly null or load issue.");
        }

         // Format checklists if they exist
         if ($task->checklists instanceof \Illuminate\Database\Eloquent\Collection) {
            // No need to transform here if the Checklist model's $casts and $appends handle it well
            // Or if you want specific formatting:
            $task->checklists->transform(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'is_done' => $item->is_done,
                    'position' => $item->position,
                    'task_id' => $item->task_id,
                ];
            });
        } else {
            $task->checklists = collect([]);
            Log::warning("Task ID {$task->id}: checklists was not a collection, possibly null or load issue.");
        }


        return response()->json([
            'success' => true,
            'task' => $task,
        ]);
    }


    /**
     * Update the specified task in storage.
     */
    public function update(TaskRequest $request, Task $task)
    {
        $this->authorizeTaskAccess($task,['board_member_manager','board_editor']);

        try {
            $task->updateDetails($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thể nhiệm vụ thành công.',
                'task' => [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'priority' => $task->priority,
                    'due_date' => $task->due_date ? $task->due_date->toDateString() : null,
                    'formatted_due_date' => $task->due_date ? $task->due_date->format('M d') : null,
                    'position' => $task->position,
                    'column_id' => $task->column_id,
                    'assignees' => $task->assignees->map(function ($assignee) {
                        return ['id' => $assignee->id, 'name' => $assignee->name, 'email' => $assignee->email];
                    }),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("Error updating task {$task->id}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Không thể cập nhật thể nhiệm vụ.'
            ], 500);
        }
    }


    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task)
    {
        $this->authorizeTaskAccess($task,['board_member_manager']);

        try {
            $task->deleteWithHistory();

            return response()->json(['success' => true, 'message' => 'Xóa nhiệm vụ thành công']);
        } catch (\Exception $e) {
            Log::error("Error deleting task {$task->id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'không thể xóa nhiệm vụ'], 500);
        }
    }


    /**
     * Update task position (within or between columns).
     */
    public function updatePosition(UpdateTaskPositionRequest $request)
    {
        $task = Task::findOrFail($request->task_id);
        $board = $this->authorizeTaskAccess($task,['board_editor','board_member_manager']);
        $taskHistory = new TaskHistory();
        $oldColumn = Column::find($task->column_id);
        $newColumn = Column::findOrFail($request->new_column_id);
        if ($newColumn->board_id !== $board->id) {
            abort(403, 'Không thể di chuyển nhiệm vụ sang một cột trong bảng khác.');
        }

        try {
            DB::beginTransaction();

            // Cập nhật column_id (chỉ khi khác)
            if ($task->column_id !== $request->new_column_id) {
                $task->column_id = $request->new_column_id;
                $task->save();
            }
            $action = "di chuyển";
            $taskHistory->logTaskHistory($task, $action, $oldColumn->name ?? null, $newColumn->name ?? null);

            foreach ($request->order as $index => $taskId) {
                Task::where('id', $taskId)->update([
                    'position' => $index, // hoặc $index + 1 tùy bạn
                ]);
            }

            $action = "Di chuyển";
            

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Vị trí nhiệm vụ đã cập nhật.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Lỗi khi cập nhật vị trí: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể cập nhật vị trí nhiệm vụ.'], 500);
        }
    }
}
