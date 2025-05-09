<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;

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

    public function createForColumn(Column $column, array $data): Task
    {
        $maxPosition = $column->tasks()->max('position');
        $position = is_null($maxPosition) ? 0 : $maxPosition + 1;

        $data['position'] = $position;
        $data['column_id'] = $column->id;
        $data['status'] = $data['status'] ?? 'todo';       
        $data['priority'] = $data['priority'] ?? 'normal'; 

        return self::create($data);
    }

    public function loadDetails(): self
{
    return $this->load([
        'column', 
        'assignees',
        'attachments',
        'comments.user',     
        'taskHistories.user', 
    ]);
}

    public function updateDetails(array $data): bool
    {
        $originalData = $this->only(array_keys($data)); 

        $updated = $this->update($data);

        if ($updated) {
            $changes = [];
            foreach ($data as $field => $newValue) {
                $oldValue = $originalData[$field] ?? null;
                if ($oldValue != $newValue) {
                    $changes[] = "{$field}: '{$oldValue}' → '{$newValue}'";
                }
            }

            if (!empty($changes)) {
                $this->taskHistories()->create([
                    'user_id' => Auth::id(),
                    'action' => 'updated',
                    'note' => implode('; ', $changes),
                ]);
            }
        }

        return $updated;
    }

    public function deleteWithHistory(): bool
    {
        $this->taskHistories()->create([
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'note' => "Task '{$this->title}' was deleted.",
        ]);

        return $this->delete();
    }


    public function moveToColumnWithOrder($newColumnId, $orderedTaskIds, $userId)
    {
        $oldColumnId = $this->column_id;

        $this->column_id = $newColumnId;
        $this->save();

        // Ghi lại lịch sử nếu chuyển sang cột khác
        if ($oldColumnId != $newColumnId) {
            $oldColumnName = optional(Column::find($oldColumnId))->name;
            $newColumnName = optional(Column::find($newColumnId))->name;

            $this->taskHistories()->create([
                'user_id' => $userId,
                'action' => 'moved',
                'note' => "Task moved from '{$oldColumnName}' to '{$newColumnName}'",
            ]);
        }

        // Cập nhật lại vị trí các task trong cột
        foreach ($orderedTaskIds as $index => $taskId) {
            static::where('id', $taskId)
                ->update(['position' => $index, 'column_id' => $newColumnId]);
        }
    }

    
}
