<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\BoardRequest;
use App\Models\Board;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
            return view('user.dashboard');
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
