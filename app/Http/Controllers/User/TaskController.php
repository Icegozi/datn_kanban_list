<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\Task;
use Auth;
use DB;
use Illuminate\Http\Request;
use Log;

class TaskController extends Controller
{
    private function authorizeTaskAccess(Task $task)
    {
        // Lấy board chứa task này
        $board = $task->column->board; // Giả sử column relation đã được load hoặc sẽ được load
        if ($board->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action on this task.');
        }
        return $board; // Trả về board để dùng lại nếu cần
    }

    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request, Column $column) // Nhận Column qua route model binding
    {
        // Kiểm tra quyền truy cập vào board chứa column này
        $board = $column->board;
        if ($board->user_id !== Auth::id()) {
            Log::warning("Unauthorized attempt to create task in column {$column->id} by user " . Auth::id());
            abort(403, 'Unauthorized action.');
        }

        Log::info("Task store request received for column {$column->id}: ", $request->all()); // <-- THÊM LOG NÀY
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            // Thêm validation cho các trường khác nếu cần (priority, due_date,...)
        ]);

        Log::info("Task data validated: ", $validated); // <-- THÊM LOG NÀY
        try {
            // Tìm vị trí cuối cùng trong cột
            $maxPosition = $column->tasks()->max('position');
            $position = ($maxPosition === null) ? 0 : $maxPosition + 1;

            $task = $column->tasks()->create([
                'title' => $validated['title'],
                'position' => $position,
                'priority' => $request->input('priority', 'normal'), // Lấy giá trị mặc định nếu không có
                'status' => 'todo', // Hoặc trạng thái phù hợp với column
                // Thêm các trường khác từ request nếu có
                // 'description' => $request->input('description'),
                // 'due_date' => $request->input('due_date'),
            ]);
            Log::info("Task created successfully with ID: {$task->id}"); // <-- THÊM LOG SAU KHI TẠO

            // Trả về dữ liệu task mới (hoặc HTML của card mới) để JS render
            return response()->json([
                'success' => true,
                'message' => 'Task created successfully.',
                // Có thể trả về HTML của card đã render sẵn để JS dễ dàng append
                // 'html' => view('partials.task_card', compact('task'))->render()
                // Hoặc chỉ trả về dữ liệu JSON
                 'task' => [
                     'id' => $task->id,
                     'title' => $task->title,
                     'position' => $task->position,
                     'column_id' => $task->column_id,
                     // Các thông tin khác cần để render card
                 ]
            ], 201);

        } catch (\Exception $e) {
        
            Log::error("Error creating task in column {$column->id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Could not create task.'], 500);
        }
    }

    /**
     * Display the specified task (for modal).
     */
    public function show(Task $task)
    {
         $this->authorizeTaskAccess($task);

        // Eager load các mối quan hệ cần hiển thị trong modal
        $task->load(['column', 'assignees', 'attachments', 'comments' => function ($query) {
            $query->with('user'); // Load user của comment
        }, 'taskHistories' => function($query){
             $query->with('user'); // Load user của history
        }]);

        return response()->json([
            'success' => true,
            'task' => $task,
            // Có thể thêm thông tin khác cần cho modal (danh sách user để assign,...)
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
            'priority' => ['sometimes', Rule::in(['low', 'normal', 'high', 'urgent'])], // Ví dụ các mức ưu tiên
            'due_date' => 'nullable|date',
            // Thêm validation cho các trường khác bạn muốn cập nhật
        ]);

         try {
             $task->update($validated);
             // Xử lý cập nhật assignees, attachments nếu cần (phức tạp hơn, cần logic riêng)

             // Ghi lại lịch sử thay đổi (Ví dụ)
             // $task->taskHistories()->create([
             //     'user_id' => Auth::id(),
             //     'action' => 'updated',
             //     'note' => 'Updated task details.' // Ghi rõ hơn các trường đã thay đổi
             // ]);

            // Lấy lại task với thông tin cập nhật để trả về (tùy chọn)
            // $task->refresh();

             return response()->json([
                 'success' => true,
                 'message' => 'Task updated successfully.',
                 'task' => $task // Trả về task đã cập nhật
             ]);
         } catch (\Exception $e) {
             Log::error("Error updating task {$task->id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Could not update task.'], 500);
         }
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task)
    {
         $this->authorizeTaskAccess($task);

         try {
             $task->delete(); // Cascade delete sẽ xử lý attachments, comments,... dựa vào migration
              return response()->json(['success' => true, 'message' => 'Task deleted successfully.']);
         } catch (\Exception $e) {
             Log::error("Error deleting task {$task->id}: " . $e->getMessage());
             return response()->json(['success' => false, 'message' => 'Could not delete task.'], 500);
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
             'order' => 'required|array', // Mảng các task_id trong cột mới, theo thứ tự mới
             'order.*' => 'integer|exists:tasks,id',
         ]);

         $taskId = $request->input('task_id');
         $newColumnId = $request->input('new_column_id');
         $orderedTaskIds = $request->input('order');

         // --- Authorization ---
         $taskToMove = Task::findOrFail($taskId);
         $board = $this->authorizeTaskAccess($taskToMove); // Kiểm tra quyền trên task sắp di chuyển
         // Kiểm tra quyền trên cột đích (cũng phải thuộc board của user)
         $newColumn = Column::findOrFail($newColumnId);
         if ($newColumn->board_id !== $board->id) {
             abort(403, 'Cannot move task to a column in another board.');
         }
         // --- End Authorization ---


         try {
             DB::beginTransaction();

             // 1. Cập nhật column_id cho task được di chuyển (nếu nó thay đổi)
             if ($taskToMove->column_id != $newColumnId) {
                 $taskToMove->column_id = $newColumnId;
                 // Có thể ghi history ở đây: moved from column X to column Y
             }
              // Lưu thay đổi column_id trước khi cập nhật position của các task khác
              // $taskToMove->save(); // Sẽ save ở bước 2

             // 2. Cập nhật position cho tất cả các task trong cột đích theo thứ tự mới
             foreach ($orderedTaskIds as $index => $tid) {
                 // Dùng DB::table để update nhanh hơn hoặc Task::where(...)->update(...)
                 // Cần đảm bảo chỉ update task thuộc board này
                Task::where('id', $tid)
                    ->whereHas('column', function ($q) use ($board) {
                        $q->where('board_id', $board->id);
                    })
                    ->update(['position' => $index, 'column_id' => $newColumnId]); // Cập nhật luôn column_id để chắc chắn
             }

             DB::commit();
             return response()->json(['success' => true, 'message' => 'Task position updated.']);

         } catch (\Exception $e) {
             DB::rollBack();
             Log::error("Error updating task position: " . $e->getMessage());
             return response()->json(['success' => false, 'message' => 'Could not update task position.'], 500);
         }
    }
}
