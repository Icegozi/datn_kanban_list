<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignee extends Model
{
    use HasFactory;

    protected $table = 'assignees';

    protected $fillable = [
        'user_id',
        'task_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function addAsignee($userId, $taskId)
    {
        return self::create([
            'user_id' => $userId,
            'task_id' => $taskId,
        ]);
    }

    public function removeAsignee($userId, $taskId)
    {
        return self::where('user_id', $userId)
                   ->where('task_id', $taskId)
                   ->delete();
    }

    public function isExistsAsignee($userId, $taskId)
    {
        return self::where('user_id', $userId)
                   ->where('task_id', $taskId)
                   ->exists();
    }

    public function getUsersByTask($taskId)
    {
        return self::where('task_id', $taskId)
                ->with('user') 
                ->get()
                ->pluck('user');
    }

    public function updateUserForTask($taskId, $newUserId)
    {
        $assignee = self::where('task_id', $taskId)->first();

        if (!$assignee) {
            return false; 
        }

        $assignee->update([
            'user_id' => $newUserId,
        ]);

        return $assignee; 
    }

}
