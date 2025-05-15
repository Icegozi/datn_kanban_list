<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoardInvitation extends Model
{
    use HasFactory;
    protected $fillable = [
        'board_id',
        'email',
        'token',
        'role_permission_name',
        'invited_by',
        'expires_at',
        'accepted_at',
    ];
    protected $casts = ['expires_at' => 'datetime', 'accepted_at' => 'datetime'];

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }
    
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}
