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
        'status',      
        'priority',
        'column_id',
        'due_date',
        'position',  
    ];


    protected $casts = [
        'due_date' => 'date',
    ];

    public function column(): BelongsTo
    {
        return $this->belongsTo(Column::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->latest();
    }

    public function taskHistories(): HasMany
    {
        return $this->hasMany(TaskHistory::class)->latest();
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'assignees', 'task_id', 'user_id')->withTimestamps();
    }

    public function board()
    {
        return $this->column()->first()->board()->first();
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
        $oldColumn = $this->column;
        $newColumn = Column::find($newColumnId); 


        $this->update(['column_id' => $newColumnId]);

        // Ghi lại lịch sử nếu chuyển sang cột khác
        if ($oldColumnId != $newColumnId) {
            $this->taskHistories()->create([
                'user_id' => $userId,
                'action' => 'di chuyển',
                'note' => "Thẻ công việc di chuyển từ '{$oldColumn->name}' sang '{$newColumn->name}'",
            ]);
        }

        $tasksToUpdate = Task::whereIn('id', $orderedTaskIds)
                            ->where('column_id', $newColumnId)
                            ->get();

        foreach ($tasksToUpdate as $index => $task) {
            $task->update(['position' => $index]);
        }
    }

}
