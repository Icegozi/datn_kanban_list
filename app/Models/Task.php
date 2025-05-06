<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'status',       // Có thể không cần nếu dựa vào column? Hoặc dùng cho mục đích khác.
        'priority',
        'column_id',
        'due_date',
        'position',     // <-- **QUAN TRỌNG: Thêm cột position**
    ];

    // Casting cho due_date và các kiểu dữ liệu khác nếu cần
    protected $casts = [
        'due_date' => 'date',
    ];

    public function column(): BelongsTo
    {
        return $this->belongsTo(Column::class);
    }

    // Nối với User (Người tạo? - Cần thêm user_id vào bảng tasks nếu muốn)
    // public function creator(): BelongsTo
    // {
    //     return $this->belongsTo(User::class, 'user_id');
    // }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function comments(): HasMany
    {
        // Sắp xếp comment mới nhất lên đầu
        return $this->hasMany(Comment::class)->latest();
    }

    public function taskHistories(): HasMany
    {
        return $this->hasMany(TaskHistory::class)->latest();
    }

    // Mối quan hệ Nhiều-Nhiều với User thông qua bảng assignees
    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'assignees', 'task_id', 'user_id')
            ->withTimestamps(); // Lấy cả created_at, updated_at của bảng trung gian nếu cần
    }

    // Helper để lấy board chứa task này (thông qua column)
    public function board()
    {
        // return $this->column->board; // Cách này gây N+1 nếu không eager load column.board
        // Cách tốt hơn là định nghĩa HasOneThrough hoặc truy vấn khi cần
        return $this->column()->first()->board()->first(); // Ví dụ đơn giản
    }
}
