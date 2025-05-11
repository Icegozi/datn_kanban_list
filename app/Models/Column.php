<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; 
use Illuminate\Database\Eloquent\Relations\HasMany;  

class Column extends Model
{
    use HasFactory;

    // Add fillable properties
    protected $fillable = ['name', 'position', 'board_id'];

    // Add board relationship
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    // Add tasks relationship
    public function tasks(): HasMany
    {
        // You might want to order tasks within a column later
        return $this->hasMany(Task::class);
    }

    public function createDefaultColumns(int $boardId): void
    {
        $defaultColumns = ['Việc cần làm', 'Đang làm', 'Hoàn thành'];

        foreach ($defaultColumns as $index => $columnName) {
            self::create([
                'name' => $columnName,
                'position' => $index,
                'board_id' => $boardId,
            ]);
        }
    }

}
