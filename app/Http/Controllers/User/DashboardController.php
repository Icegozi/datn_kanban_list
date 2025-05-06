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

        // Fetch boards belonging to this user, ordered by creation date (newest first)
        $boards = $user->boards()->orderBy('created_at', 'desc')->get();

        return view('user.dashboard', compact('boards')); 
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
