<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Column;
use App\Models\Task;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ColumnController extends Controller
{
     // Authorization Helper
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

    private function authorizeBoardAccess(Board $board, array $requiredPermissions = [])
    {
        $user = Auth::user();
        foreach ($requiredPermissions as $permission) {
            if ($user->hasBoardPermission($board, $permission)) {
                return $board;
            }
        }

        abort(403, 'Bạn không có quyền truy cập!');
    }

 
     /**
      * Store a newly created column in storage.
      */
     public function store(Request $request, Board $board)
     {
         $this->authorizeBoardAccess($board,['board_viewer','board_editor','board_member_manager']);
 
         $validated = $request->validate([
             'name' => [
                 'required',
                 'string',
                 'max:255',
                  // Ensure name is unique *within this specific board*
                 Rule::unique('columns')->where(function ($query) use ($board) {
                     return $query->where('board_id', $board->id);
                 }),
             ]
         ]);
 
         try {
              // Determine the next position
             $maxPosition = $board->columns()->max('position');
             $nextPosition = ($maxPosition === null) ? 0 : $maxPosition + 1;
 
             $column = Column::create([
                 'name' => $validated['name'],
                 'board_id' => $board->id,
                 'position' => $nextPosition,
             ]);
 
              // Prepare data for frontend (you might want to return rendered HTML later)
             return response()->json([
                 'success' => true,
                 'message' => 'Cột đã được tạo thành công!',
                 'column' => [
                     'id' => $column->id,
                     'name' => $column->name,
                     'position' => $column->position,
                     'url_update' => route('columns.update', ['board' => $board->id, 'column' => $column->id]),
                     'url_destroy' => route('columns.destroy', ['board' => $board->id, 'column' => $column->id]),
                 ]
             ], 201);
 
         } catch (\Exception $e) {
             // Log the error
             \Log::error("Error creating column: " . $e->getMessage());
             return response()->json(['success' => false, 'message' => 'Không thể tạo cột. Đã xảy ra lỗi.'], 500);
         }
     }
 
     /**
      * Update the specified column (rename).
      */
     public function update(Request $request, Board $board, Column $column)
     {
        $this->authorizeBoardAccess($board,['board_editor','board_member_manager']);
 
         // Ensure the column actually belongs to the board in the URL
         if ($column->board_id !== $board->id) {
              abort(404); // Or 403, column not found on this board
         }
 
         $validated = $request->validate([
             'name' => [
                 'required',
                 'string',
                 'max:255',
                 // Ensure name is unique within this board, ignoring the current column
                 Rule::unique('columns')->where(function ($query) use ($board) {
                     return $query->where('board_id', $board->id);
                 })->ignore($column->id),
             ]
         ]);
 
         try {
             $column->update(['name' => $validated['name']]);
 
             return response()->json([
                 'success' => true,
                 'message' => 'Tên cột đã được cập nhật.',
                 'new_name' => $column->name
             ]);
         } catch (\Exception $e) {
              \Log::error("Error updating column {$column->id}: " . $e->getMessage());
             return response()->json(['success' => false, 'message' => 'Không thể cập nhật tên cột. Đã xảy ra lỗi.'], 500);
         }
     }
 
     /**
      * Remove the specified column from storage.
      */
     public function destroy(Board $board, Column $column)
     {
        $this->authorizeBoardAccess($board,['board_member_manager']);
 
         if ($column->board_id !== $board->id) {
              abort(404);
         }
 
          // --- Task Handling ---
          // Simplest approach (due to cascade delete in migration): Warn if tasks exist.
          // Advanced: Ask user how to handle tasks (move/delete).
          // For now, we rely on cascade and just proceed after confirmation (done on frontend).
         if ($column->tasks()->exists()) {
              // You could return a specific status or message if you want the frontend
              // to show a more specific warning before the final confirmation.
              // e.g., return response()->json(['success'=>false, 'message'=>'Cột này chứa công việc. Bạn có chắc muốn xoá? Mọi công việc sẽ bị mất.', 'has_tasks'=>true], 400);
         }
 
         try {
             DB::beginTransaction(); // Start transaction for consistency
 
             $deletedPosition = $column->position;
             $column->delete(); // Cascade delete should handle tasks based on migration
 
             // Update positions of subsequent columns on the same board
             Column::where('board_id', $board->id)
                   ->where('position', '>', $deletedPosition)
                   ->decrement('position');
 
             DB::commit(); // Commit transaction
 
             return response()->json(['success' => true, 'message' => 'Cột đã được xoá thành công.']);
 
         } catch (\Exception $e) {
             DB::rollBack(); // Rollback on error
              \Log::error("Error deleting column {$column->id}: " . $e->getMessage());
             return response()->json(['success' => false, 'message' => 'Không thể xoá cột. Đã xảy ra lỗi.'], 500);
         }
     }
 
     /**
      * Update the order of columns.
      */
     public function reorder(Request $request, Board $board)
     {
        $this->authorizeBoardAccess($board,['board_editor','board_member_manager']);
 
         $request->validate([
             'order' => 'required|array',
             'order.*' => 'integer|exists:columns,id',
         ]);
 
         $orderedIds = $request->input('order');
 
         try {
             DB::beginTransaction();
 
             foreach ($orderedIds as $index => $columnId) {
                 $column = Column::where('id', $columnId)->where('board_id', $board->id)->first();
                 if ($column) {
                     $column->position = $index;
                     $column->save();
                 } else {
                     throw new \Exception("Invalid column ID {$columnId} for board {$board->id}");
                 }
             }
 
             DB::commit();
             return response()->json(['success' => true, 'message' => 'Thứ tự cột đã được cập nhật.']);
 
         } catch (\Exception $e) {
             DB::rollBack();
              \Log::error("Error reordering columns for board {$board->id}: " . $e->getMessage());
             return response()->json(['success' => false, 'message' => 'Không thể cập nhật thứ tự cột. Đã xảy ra lỗi.'], 500);
         }
     }
}
