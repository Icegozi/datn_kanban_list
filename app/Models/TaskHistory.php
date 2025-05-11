<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TaskHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',   
        'user_id',
        'action',
        'note',
    ];

    /**
     * Get the task that owns the history.
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the user who performed the action.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Tạo phương thức tạo lịch sử hành động
    public function createHistory($taskId, $userId, $action, $note)
    {
        return self::create([
            'task_id' => $taskId,
            'user_id' => $userId,
            'action' => $action,
            'note' => $note,
        ]);
    }

    public function logTaskHistory($task, $action, $oldColumnName = null, $newColumnName = null)
    {
        if (is_array($task)) {
            $task = Task::findOrFail($task['id']); 
        }
        $userId = Auth::id();
        $userName = Auth::user()->name;

        if ($action === 'di chuyển' && $oldColumnName && $newColumnName) {
            $note = "<strong> {$userName} </strong> đã <strong>{$action}</strong> nhiệm vụ <strong>{$task->title}</strong> từ cột <strong>{$oldColumnName}</strong> sang cột <strong>{$newColumnName}</strong>.";
        } else {
            $note = "Nhiệm vụ <strong>{$task->title}</strong> đã được <strong>{$action}</strong> bởi <strong>{$userName}</strong>.";
        }

        
        if ($action === 'tạo' || $action === 'thêm bình luận') {
          
            $note = "Nhiệm vụ <strong>{$task->title}</strong> đã được <strong>{$action}</strong> bởi <strong>{$userName}</strong>.";
        }

        if ($action === 'xóa bình luận'){
            $note = "Nhiệm vụ <strong>{$task->title}</strong> đã bị <strong>{$action}</strong> bởi <strong>{$userName}</strong>.";
        }

        self::createHistory($task->id, $userId, $action, $note);
    }


}
