<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Board;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BoardController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                // Ensure name is unique for the *current user*
                Rule::unique('boards')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                }),
            ],
            // Add description validation if you plan to use it
            // 'description' => 'nullable|string|max:1000',
        ]);

        $board = Board::create([
            'name' => $validated['name'],
            'user_id' => Auth::id(),
            // 'description' => $validated['description'] ?? null,
        ]);

        // Return the created board data so frontend can display it
        return response()->json([
            'success' => true,
            'message' => 'Bảng đã được tạo thành công!',
            'board' => [
                'id' => $board->id,
                'name' => $board->name,
                'created_at_formatted' => $board->created_at->format('d/m/Y H:i'),
                'url_show' => route('boards.show', $board->id),
                'url_update' => route('boards.update', $board->id),
                'url_destroy' => route('boards.destroy', $board->id),
            ]
        ], 201);
    }

    /**
     * Display the specified resource (the actual board page).
     */

     public function show(Board $board)
     {
         if (Auth::id() !== $board->user_id) {
             abort(403, 'Unauthorized action.');
         }
     
         // Eager load các mối quan hệ cần thiết cho board view
         $board->load([
             'columns' => function ($query) {
                 $query->orderBy('position', 'asc'); // Sắp xếp cột theo vị trí
             },
             // Load tasks cho mỗi column, sắp xếp theo vị trí và load luôn assignees của task
             'columns.tasks' => function ($query) {
                 $query->with('assignees') // Eager load assignees
                       ->orderBy('position', 'asc'); // Sắp xếp task trong cột
             },
             // Không cần load columns.tasks.assignees riêng nữa vì đã có trong with('assignees')
         ]);
     
         return view('user.boards.show', compact('board'));
     }
     

    // ... các hàm khác ...


    /**
     * Show the form for editing the specified resource.
     * Not needed for AJAX rename.
     */
    // public function edit(Board $board) { }

    /**
     * Update the specified resource in storage (specifically renaming here).
     */
    public function update(Request $request, Board $board)
    {
        // Authorization Check
        if (Auth::id() !== $board->user_id) {
            return response()->json(['success' => false, 'message' => 'Không được phép cập nhật bảng này.'], 403);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                // Ensure name is unique for the current user, *ignoring the current board*
                Rule::unique('boards')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                })->ignore($board->id),
            ]
        ]);

        $board->update(['name' => $validated['name']]);

        return response()->json([
            'success' => true,
            'message' => 'Tên bảng đã được cập nhật.',
            'new_name' => $board->name, // Send back the updated name
            'updated_at_formatted' => $board->updated_at->format('d/m/Y H:i'), // Send back updated time
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Board $board)
    {
        // Authorization Check
        if (Auth::id() !== $board->user_id) {
            return response()->json(['success' => false, 'message' => 'Không được phép xoá bảng này.'], 403);
        }

        $board->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bảng đã được xoá thành công.'
        ]);
    }
}
