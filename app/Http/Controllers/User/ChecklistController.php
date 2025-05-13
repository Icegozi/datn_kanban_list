<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Checklist;
use App\Models\Task;
use Auth;
use Illuminate\Http\Request;
use Log;

class ChecklistController extends Controller
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
        foreach ($requiredPermissions as $permission) {
            if ($user->hasBoardPermission($board, $permission)) {
                return $board;
            }
        }

        abort(403, 'Bạn không có quyền thực hiện thao tác!');
    }

    public function index(Task $task)
    {
        $this->authorizeTaskAccess($task,['board_viewer','board_editor','board_member_manager']);
        $checklists = $task->checklists()->orderBy('position')->get();
        return response()->json(['success' => true, 'checklists' => $checklists]);
    }

    public function store(Request $request, Task $task)
    {
        $this->authorizeTaskAccess($task,['board_editor','board_member_manager']);

        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        try {
            $maxPosition = $task->checklists()->max('position');
            $position = is_null($maxPosition) ? 0 : $maxPosition + 1;

            $checklist = $task->checklists()->create([
                'title' => $request->title,
                'position' => $position,
                'is_done' => false, // Default
            ]);

            // Log history on task
            $task->taskHistories()->create([
                'user_id' => Auth::id(),
                'action' => 'added_checklist_item',
                'note' => "Đã thêm mục checklist: '{$checklist->title}'",
            ]);

            return response()->json(['success' => true, 'message' => 'Mục checklist đã được thêm.', 'checklist' => $checklist], 201);
        } catch (\Exception $e) {
            Log::error("Error creating checklist for task {$task->id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể thêm mục checklist.'], 500);
        }
    }

    public function update(Request $request, Checklist $checklist)
    {
        $task = $checklist->task;
        $this->authorizeTaskAccess($task,['board_editor','board_member_manager']);

        // VALIDATION:
        $validatedData = $request->validate([
            'title' => 'sometimes|required_without:is_done|string|max:255',
            'is_done' => 'sometimes|required_without:title|boolean', // Ensures 'is_done' is boolean
        ]);

        try {
            $originalTitle = $checklist->title;
            $originalIsDone = $checklist->is_done;
            $updatedFieldsMessages = [];

            // Check if 'title' is present in the request and different
            if ($request->has('title') && $checklist->title !== $request->title) {
                $checklist->title = $request->title; // Uses validated data if 'title' was part of it
                $updatedFieldsMessages[] = "tiêu đề từ '{$originalTitle}' thành '{$request->title}'";
            }

            // Check if 'is_done' is present in the request and different
            // IMPORTANT: Use $request->boolean('is_done') for proper boolean conversion from request
            if ($request->has('is_done') && $checklist->is_done !== $request->boolean('is_done')) {
                $checklist->is_done = $request->boolean('is_done'); // Assign the boolean value
                $status = $checklist->is_done ? 'hoàn thành' : 'chưa hoàn thành';
                $titleForMessage = $request->has('title') ? $request->title : $originalTitle;
                $updatedFieldsMessages[] = "trạng thái mục '{$titleForMessage}' thành {$status}";
            }

            if (!empty($updatedFieldsMessages)) {
                $checklist->save(); // <-- THIS IS WHERE THE DATABASE INTERACTION HAPPENS

                // Log history if the task relationship exists
                if ($checklist->task) { // Ensure task relationship is loaded or exists
                    $checklist->task->taskHistories()->create([
                    'user_id' => Auth::id(),
                    'action' => 'updated_checklist_item',
                    'note' => "Đã cập nhật mục checklist: " . implode(', ', $updatedFieldsMessages),
                ]);
                }
            }

            // Return the fresh checklist item (with potentially updated values)
            return response()->json(['success' => true, 'message' => 'Mục checklist đã được cập nhật.', 'checklist' => $checklist->fresh()]);

        } catch (\Exception $e) {
            // Log the detailed error
            Log::error("Error updating checklist {$checklist->id}: " . $e->getMessage() . " Stack trace: " . $e->getTraceAsString());
            return response()->json(['success' => false, 'message' => 'Không thể cập nhật mục checklist.'], 500); // Generic 500
        }
    }

    public function destroy(Checklist $checklist)
    {
        $this->authorizeTaskAccess($task,['board_member_manager']);
        $task = $checklist->task;

        try {
            $checklistTitle = $checklist->title;
            $checklist->delete();

            if ($task) {
                $task->taskHistories()->create([
                    'user_id' => Auth::id(),
                    'action' => 'deleted_checklist_item',
                    'note' => "Đã xóa mục checklist: '{$checklistTitle}'",
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Mục checklist đã được xóa.']);
        } catch (\Exception $e) {
            Log::error("Error deleting checklist {$checklist->id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể xóa mục checklist.'], 500);
        }
    }

    public function reorder(Request $request, Task $task)
    {
        $this->authorizeTaskAccess($task,['board_editor','board_member_manager']);

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:checklists,id',
        ]);

        try {
            foreach ($request->ids as $index => $checklistId) {
                Checklist::where('id', $checklistId)
                         ->where('task_id', $task->id)
                         ->update(['position' => $index]);
            }

            $task->taskHistories()->create([
                'user_id' => Auth::id(),
                'action' => 'reordered_checklist',
                'note' => "Đã sắp xếp lại các mục trong checklist.",
            ]);

            return response()->json(['success' => true, 'message' => 'Thứ tự checklist đã được cập nhật.']);
        } catch (\Exception $e) {
            Log::error("Error reordering checklists for task {$task->id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Không thể cập nhật thứ tự checklist.'], 500);
        }
    }
}
