<?php

namespace App\Models;

use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Board extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function columns(): HasMany
    {
        // Mặc định sắp xếp các cột theo vị trí 'position'
        return $this->hasMany(Column::class)->orderBy('position', 'asc');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(BoardInvitation::class);
    }

    // Lấy tất cả board theo user_id
    public static function getBoardsByUser(int $userId)
    {
        return self::where('user_id', $userId)->get();
    }

    // Thêm board mới
    public static function createBoard(array $data)
    {
        return self::create($data);
    }

    // Cập nhật board theo ID
    public static function updateBoard(int $id, array $data)
    {
        $board = self::findOrFail($id);
        $board->update($data);
        return $board;
    }

    // Xoá board theo ID
    public static function deleteBoard(int $id)
    {
        $board = self::findOrFail($id);
        return $board->delete();
    }

    // Tìm kiếm board theo khoảng thời gian (created_at)
    public static function searchByDate(string $from, string $to)
    {
        return self::whereBetween('created_at', [
            Carbon::parse($from)->startOfDay(),
            Carbon::parse($to)->endOfDay()
        ])->get();
    }

    //lấy column từ id
    public static function getBoardData(int $id)
    {
        return self::with(['user', 'columns.tasks'])->findOrFail($id);
    }


    public function boardPermissionUsers(): HasManyThrough
    {
        // Board -> board_permissions -> permission_users
        return $this->hasManyThrough(
            PermissionUser::class, 
            BoardPermission::class, 
            'board_id',        
            'id',                
            'id',                
            'permission_user_id'
        );
    }

    public function getMembersWithRoles()
    {
        $members = [];
        $permissionUserPivots = $this->boardPermissionUsers()->with('user', 'permission')->get();

        foreach ($permissionUserPivots as $pivot) {
            if ($pivot->user && $pivot->user->id !== $this->user_id) { 
                if (!isset($members[$pivot->user->id])) {
                    $members[$pivot->user->id] = [
                        'user' => $pivot->user,
                        'roles' => [], 
                    ];
                }
                if ($pivot->permission) {
                    $members[$pivot->user->id]['roles'][] = $pivot->permission->name;
                }
            }
        }

        return array_values($members);
    }

    // public function assignedUsers()
    // {
    //     return User::whereIn('id', function ($query) {
    //         $query->select('permission_users.user_id')
    //             ->from('permission_users')
    //             ->join('board_permissions', 'permission_users.id', '=', 'board_permissions.permission_user_id')
    //             ->where('board_permissions.board_id', $this->id);
    //     })->get();
    // }

    public function assignedUsers(Board $board)
    {
        $permissionUserIds = DB::table('permission_users')
            ->join('board_permissions', 'permission_users.id', '=', 'board_permissions.permission_user_id')
            ->where('board_permissions.board_id', $board->id)
            ->pluck('permission_users.user_id')
            ->toArray();
        $permissionUserIds[] = $board->user_id;
        $userIds = array_unique($permissionUserIds);
        return User::whereIn('id', $userIds)->get();
    }

}
