<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Checklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'is_done',
        'position',
        'task_id', 
    ];

    protected $casts = [
        'is_done' => 'boolean',
    ];

    // Add this relationship
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
