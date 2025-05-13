<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\BoardRequest;
use App\Models\Board;
use App\Models\Column;
use Auth;
use Illuminate\Http\Request;

class BoardController extends Controller
{
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

    public function store(BoardRequest $request)
    {
        $validated = $request->validated();
        $column = new Column();
        $boards = new Board();

        $data = [
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ];
        $board = $boards->createBoard($data);
        $column->createDefaultColumns($board->id);

        return response()->json([
            'success' => true,
            'message' => 'Bảng đã được tạo thành công!',
            'board' => [
                'id' => $board->id,
                'name' => $board->name,
                'created_at_formatted' => $board->created_at->format('d/m/Y H:i:s'),
                'url_show' => route('boards.show', $board->id),
                'url_update' => route('boards.update', $board->id),
                'url_destroy' => route('boards.destroy', $board->id),
            ]
        ], 201);
    }


    public function show(Board $board)
    {
        $this->authorizeBoardAccess($board, ['board_viewer','board_editor', 'board_member_manager']);
        $board->load([
            'columns' => function ($query) {
                $query->orderBy('position', 'asc');
            },

            'columns.tasks' => function ($query) {
                $query->with('assignees')
                    ->orderBy('position', 'asc');
            },

        ]);

        return view('user.boards.show', compact('board'));
    }


    public function update(BoardRequest $request, Board $board)
    {
        $this->authorizeBoardAccess($board, ['board_editor', 'board_member_manager']);

        $validated = $request->validated();

        $board->update(['name' => $validated['name']]);

        return response()->json([
            'success' => true,
            'message' => 'Tên bảng đã được cập nhật.',
            'new_name' => $board->name,
            'updated_at_formatted' => $board->updated_at->format('d/m/Y H:i:s'),
        ]);
    }


    public function destroy(Board $board)
    {
        $this->authorizeBoardAccess($board, ['board_member_manager']);

        $board->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bảng đã được xoá thành công.'
        ]);
    }
}
