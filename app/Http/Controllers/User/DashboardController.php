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

        // Use the new method to get all accessible boards
        $accessibleBoards = $user->getAllAccessibleBoards();

        // You might want to sort them, e.g., by last updated or by name
        // The getAllAccessibleBoards method already sorts by name.
        // If you want a different sort here:
        // $boards = $accessibleBoards->sortByDesc('updated_at');
        $boards = $accessibleBoards;


        // Add the user's role for each board to pass to the view
        // This can be helpful for displaying role-specific UI elements on the dashboard cards
        $boardsWithRoles = $boards->map(function ($board) use ($user) {
            $board->currentUserRole = $user->getRoleForBoard($board); // Add a temporary attribute
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
