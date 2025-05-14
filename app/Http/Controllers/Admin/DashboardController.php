<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use LaravelDaily\LaravelCharts\Classes\LaravelChart;
use Auth;
use Illuminate\Support\Facades\DB;
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

        return view('admin.dashboard', ['boards' => $boardsWithRoles]);
    }


    public function getUserRegistrations(Request $request)
    {
        try {
            $dateRange = $request->query('date_range');

            if (!$dateRange) {
                return response()->json(['labels' => [], 'datasets' => []]);
            }

            $dates = explode(' to ', $dateRange);
            if (count($dates) === 2) {
                $startDate = Carbon::parse($dates[0])->startOfDay();
                $endDate = Carbon::parse($dates[1])->endOfDay();
            } else {
                $startDate = Carbon::parse($dates[0])->startOfDay();
                $endDate = $startDate->copy()->endOfDay();
            }

            $registrations = DB::table('users')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total'))
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $labels = [];
            $data = [];

            $current = $startDate->copy();
            while ($current->lte($endDate)) {
                $label = $current->toDateString();
                $labels[] = $label;

                $entry = $registrations->firstWhere('date', $label);
                $data[] = $entry ? $entry->total : 0;

                $current->addDay();
            }

            return response()->json([
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Số người đăng ký',
                        'data' => $data,
                        'borderColor' => 'rgb(75, 192, 192)',
                        'fill' => false,
                        'tension' => 0.1,
                    ]
                ]
            ]);
        } catch (\Throwable $e) {
            logger()->error("Chart data error: " . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}
