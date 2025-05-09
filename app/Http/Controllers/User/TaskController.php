<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\Task;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Log;

class TaskController extends Controller
{
    private function authorizeTaskAccess(Task $task)
    {
        $board = $task->column->board;
        if ($board->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập!');
        }
        return $board;
    }

    public function store(Request $request, Column $column)
    {
        $board = $column->board;
        if ($board->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền truy cập bảng này!'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => ['nullable', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'due_date' => 'nullable|date',
        ]);

        try {
            $taskInstance = new Task(); 
            $dataToCreate = [
                'title' => $validated['title'],
                'description' => $request->input('description'), 
                'priority' => $request->input('priority', 'normal'), 
                'due_date' => $request->input('due_date'), 
            ];

            $createdTask = $taskInstance->createForColumn($column, $dataToCreate);

            $createdTask->load('assignees');

            Log::info("Task created successfully with ID: {$createdTask->id} for column {$column->id}");

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
        $this->authorizeTaskAccess($task); 
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
                $history->user_name = $history->user ? $history->user->name : 'Người dùng không xác định';
                $history->user_avatar = $history->user ? ('https://i.pravatar.cc/40?u=' . urlencode($history->user->email)) : 'https://i.pravatar.cc/40?u=unknown';
                $history->time_ago = $history->created_at ? $history->created_at->diffForHumans() : 'Không rõ thời gian';
                return $history;
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

        return response()->json([
            'success' => true,
            'task' => $task,
        ]);
    }


    /**
     * Update the specified task in storage.
     */
    public function update(Request $request, Task $task)
    {
        $this->authorizeTaskAccess($task);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'priority' => ['sometimes', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'due_date' => 'nullable|date',
        ]);

        try {
            $task->updateDetails($validated);

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
                    // 'assignees' => $task->assignees->map(function($assignee) { /* ... */ }),
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
        $this->authorizeTaskAccess($task);

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
    public function updatePosition(Request $request)
    {
        $request->validate([
            'task_id' => 'required|integer|exists:tasks,id',
            'new_column_id' => 'required|integer|exists:columns,id',
            'order' => 'required|array',
            'order.*' => 'integer|exists:tasks,id',
        ]);

        $task = Task::findOrFail($request->task_id);
        $board = $this->authorizeTaskAccess($task);

        $newColumn = Column::findOrFail($request->new_column_id);
        if ($newColumn->board_id !== $board->id) {
            abort(403, 'Không thể di chuyển nhiệm vụ sang một cột trong bảng khác.');
        }

        try {
            DB::beginTransaction();

            $task->moveToColumnWithOrder($request->new_column_id, $request->order, Auth::id());

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Vị trí nhiệm vụ đã thay đổi']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating task position: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể cập nhật vị trí nhiệm vụ'], 500);
        }
    }


    public function showDetailsPage(Task $task)
    {
        $this->authorizeTaskAccess($task);

        $task->load([
            'column.board',
            'assignees',
            'comments.user',      
            'taskHistories.user', 
        ]);

        $boardForLayout = $task->column->board;

        if ($task->due_date) {
            $task->formatted_due_date = $task->due_date->format('d M, Y');
        }
        if ($task->created_at) {
            $task->formatted_created_at = $task->created_at->format('d M, Y \lúc H:i');
        }

        if ($task->task_histories instanceof \Illuminate\Database\Eloquent\Collection) {
            $task->task_histories->each(function ($history) { 
                $history->user_name = $history->user ? $history->user->name : 'N/A';
                $history->time_ago = $history->created_at ? $history->created_at->diffForHumans() : 'N/A';
            });
        } else {
            $task->task_histories = collect([]);
        }

        if ($task->comments instanceof \Illuminate\Database\Eloquent\Collection) {
            $task->comments->each(function ($comment) {
                $comment->user_name = $comment->user ? $comment->user->name : 'N/A';
                $comment->time_ago = $comment->created_at ? $comment->created_at->diffForHumans() : 'N/A';
            });
        } else {
            $task->comments = collect([]);
        }

        return view('user.tasks.show_details', ['task' => $task, 'board' => $boardForLayout]);
    }
}

