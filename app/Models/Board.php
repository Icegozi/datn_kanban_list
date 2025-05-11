<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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



}
