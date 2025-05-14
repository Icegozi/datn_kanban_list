<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\BoardRequest;
use App\Models\Board;
use Auth;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $accessibleBoards = $user->getAllAccessibleBoards();
        $boards = $accessibleBoards;
        $boardsWithRoles = $boards->map(function ($board) use ($user) {
            $board->currentUserRole = $user->getRoleForBoard($board); 
            return $board;
        });

        return view('user.dashboard', ['boards' => $boardsWithRoles]);
    }

    public function store(BoardRequest $request)
    {

    }

    public function update(BoardRequest $request, Board $board)
    {

    }

    public function destroy(Board $board)
    {

    }

}
