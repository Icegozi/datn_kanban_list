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
        // Lấy board chứa task này
        $board = $task->column->board; // Giả sử column relation đã được load hoặc sẽ được load
        if ($board->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập!');
        }
        return $board;
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request, Column $column)
    {
        $board = $column->board;
        if ($board->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập!');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            // Các trường khác nếu cần
        ]);

        try {
            $task = new Task();
            // Thêm các trường khác bạn có thể gửi từ form
            $dataToCreate = array_merge(
                $request->validate(['title' => 'required|string|max:255']),
                $request->only(['description', 'priority', 'due_date']) // Lấy thêm các trường nếu có
            );
            $task = $task->createForColumn($column, $dataToCreate);

            // Load relations cần thiết để hiển thị trên card mới (ví dụ assignees)
            // $task->load('assignees'); // Nếu bạn có assignees và muốn hiển thị ngay

            Log::info("Task created successfully with ID: {$task->id}");

            return response()->json([
                'success' => true,
                'message' => 'Task created successfully.',
                'task' => [ // Trả về đầy đủ thông tin cần cho createTaskCardHtml
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'priority' => $task->priority,
                    'due_date' => $task->due_date ? $task->due_date->toDateString() : null, // Format nếu cần
                    'formatted_due_date' => $task->due_date ? $task->due_date->format('M d') : null,
                    'position' => $task->position,
                    'column_id' => $task->column_id,
                    // 'assignees' => $task->assignees->map(function($assignee) { // Nếu có assignees
                    //     return ['id' => $assignee->id, 'name' => $assignee->name, 'email' => $assignee->email];
                    // }),
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error("Error creating task in column {$column->id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể thêm  nhiệm vụ mới.'], 500);
        }
    }


    /**
     * Display the specified task (for modal).
     */
    public function show(Task $task)
    {
        $this->authorizeTaskAccess($task);

        $task->loadDetails();

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
        // Sử dụng Route Model Binding, Laravel sẽ tự động tìm Task theo ID
        // Load các relationship cần thiết
        $task->load(['column.board', 'assignees', 'creator']); // Giả sử có 'creator' relationship

        // Định dạng ngày tháng nếu cần (hoặc dùng Accessor trong Model)
        if ($task->due_date) {
            $task->formatted_due_date = Carbon::parse($task->due_date)->format('d M, Y');
        }
        if ($task->created_at) {
            $task->formatted_created_at = $task->created_at->format('d M, Y \lúc H:i');
        }


        // Truyền dữ liệu task vào view mới
        return view('tasks.show_details', compact('task'));
    }
}
